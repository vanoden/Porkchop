<?php
	namespace Register\Organization;

	class OwnedProductList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Register\Organization\OwnedProduct';
		}

		public function findAdvanced($parameters = [], $advanced = [], $controls = []): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$get_objects_query = "
				SELECT  rop.organization_id,p.id
				FROM    register_organization_products rop
				JOIN    product_products p
				ON		rop.product_id = p.id
				WHERE   rop.quantity > 0
				AND		rop.date_expires > NOW()
				AND		p.status = 'ACTIVE'
			";

			// Add Parameters
			if (!empty($parameters['product_id']) && is_numeric($parameters['product_id'])) {
				$product = new \Product\Item($parameters['product_id']);
				if ($product->exists()) {
					$get_objects_query .= "
						AND     p.id = ?
					";
					$database->AddParam($parameters['product_id']);
				}
				else {
					$this->setError('Invalid product_id');
					return [];
				}
			}

			if (! $GLOBALS['_SESSION_']->customer->can('manage customers')) {
				$parameters['organization_id'] = $GLOBALS['_SESSION_']->customer->organization->id;
				$get_objects_query .= "
					AND     rop.organization_id = ?";
				$database->AddParam($parameters['organization_id']);
			}
			elseif (is_numeric($GLOBALS['_SESSION_']->customer->organization_id)) {
				$organization = new \Register\Organization($GLOBALS['_SESSION_']->customer->organization_id);
				if ($organization->exists()) {
					$get_objects_query .= "
						AND     rop.organization_id = ?
					";
					$database->AddParam($GLOBALS['_SESSION_']->customer->organization_id);
				}
				else {
					$this->setError('Invalid organization_id');
					return [];
				}
			}
			if (!empty($parameters['type'])) {
				$productValidation = new \Product\Item();
				if (! $productValidation->validType($parameters['type'])) {
					$this->error('Invalid product type');
					return [];
				}
				$get_objects_query .= "
					AND     p.type = ?
				";
				$database->AddParam($parameters['type']);
			}

			// Limit Clause
			$get_objects_query .= $this->limitClause($controls);

			// Execute Query
			$rs = $database->Execute($get_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			// Build Results
			$objects = array();
			while (list($organization_id,$product_id) = $rs->FetchRow()) {
				$orgProduct = new \Register\Organization\OwnedProduct($organization_id,$product_id);
				$object = $orgProduct;
				if ($this->error()) {
					$this->error("Error getting details for ".$this->_modelName.": ".$this->error());
					return [];
				}
				array_push($objects,$object);
			}

			return $objects;
		}
	}
