<?php
	namespace Register\Organization;

	class OwnedProductList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Register\Organization\OwnedProduct';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$get_objects_query = "
				SELECT  organization_id,product_id
				FROM    register_organization_products
				WHERE   product_id = product_id
			";

			// Add Parameters
			$validationClass = new $this->_modelName;
			if (!empty($parameters['product_id']) && is_numeric($parameters['product_id'])) {
				$product = new \Product\Item($parameters['product_id']);
				if ($product->exists()) {
					$get_objects_query .= "
						AND     product_id = ?
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
					AND     organization_id = ?";
				$database->AddParam($parameters['organization_id']);
			}
			elseif (is_numeric($GLOBALS['_customer']->organization->id)) {
				$organization = new \Register\Organization($GLOBALS['_customer']->organization->id);
				if ($organization->exists()) {
					$get_objects_query .= "
						AND     organization_id = ?
					";
					$database->AddParam($GLOBALS['_customer']->organization->id);
				}
				else {
					$this->setError('Invalid organization_id');
					return [];
				}
			}

			// Limit Clause
			$get_objects_query .= $this->limitClause($controls);

			// Execute Query
			$rs = $database->Execute($get_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return null;
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
