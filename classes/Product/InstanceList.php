<?php
	namespace Product;

	class InstanceList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Product\Instance';
		}

		# Return a list of hubs
		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$find_objects_query = "
				SELECT	ma.asset_id
				FROM	monitor_assets AS ma
				JOIN	product_products AS pi
				ON		ma.product_id = pi.id
				LEFT OUTER JOIN
						register_organizations AS ro
				ON		ma.organization_id = ro.id
				WHERE	pi.id = pi.id
			";

			// Add Parameters
			$validationClass = new $this->_modelName();

			if ($GLOBALS['_SESSION_']->customer->can('manage product instances')) {
				if (isset($parameters['organization_id']) && is_numeric($parameters['organization_id'])) {
					$organization = new \Register\Organization($parameters['organization_id']);
					if ($organization->error()) {
						$this->error("Error loading organization: ".$organization->error());
						return false;
					}
					if (! $organization->exists()) {
						$this->error("Organization not found");
						return false;
					}
					$find_objects_query .= "
					AND	ma.organization_id = ?";
					$database->AddParam($parameters['organization_id']);
				}
			}
			elseif (isset($GLOBALS['_SESSION_']->customer->organization()->id)) {
				$find_objects_query .= "
					AND	ma.organization_id = ?";
				$database->AddParam($GLOBALS['_SESSION_']->customer->organization()->id);
			}
			else {
				$this->error("Customer must belong to an organization");
				return null;
			}

			if (isset($parameters['id']) && is_numeric($parameters['id'])) {
				$product = new \Product\Item($parameters['id']);
				if ($product->exists()) {
					$find_objects_query .= "
					AND	asset_id = ?";
					$database->AddParam($parameters['id']);
				}
				else {
					$this->error("Product not found");
					return false;
				}
			}
			if (isset($parameters['code']) && $validationClass->validCode($parameters['code'])) {
				$find_objects_query .= "
				AND		asset_code = ?";
				$database->AddParam($parameters['code']);
			}
			if (isset($parameters['product_id']) && is_numeric($parameters['product_id'])) {
				$find_objects_query .= "
				AND		pi.id = ?";
				$database->AddParam($parameters['product_id']);
			}
			if (isset($parameters['product_code'])) {
				$product = new \Product\Item();
				if ($product->validCode($parameters['product_code'])) {
					$find_objects_query .= "
					AND		pi.code = ?";
					$database->AddParam($parameters['product_code']);
				}
				else {
					$this->error("Invalid product code");
					return false;
				}
			}

			// Order Clause
			if (array_key_exists("sort",$controls)) {
				if ($controls['sort'] == 'organization') {
					$find_objects_query .= "
					ORDER BY ro.name ".$controls['order']."
					";
				}
				elseif($controls['sort'] == 'product') {
					$find_objects_query .= "
					ORDER BY pi.code ".$controls['order']."
					";
				}
				else
					$find_objects_query .= "
					ORDER BY asset_code ".$controls['order']."
					";
			}
			else {
				$find_objects_query .= "
				ORDER BY asset_code ASC";
			}

			// Limit Clause
			$find_objects_query .= $this->limitClause($controls);

			// Execute Query
			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				if ($controls['recursive']) {
					app_log("Adding instance $id to InstanceList",'trace',__FILE__,__LINE__);
					if (isset($controls['flat']) && $controls['flat']) {
						$object = new Instance($id,$controls['flat']);
					}
					else {
						$object = new Instance($id);
					}
					if ($object->error()) {
						$this->error("Error loading asset: ".$object->error());
						return null;
					}
					array_push($objects,$object);
				}
				else {
					array_push($objects,$id);
				}
				$this->incrementCount();
			}
			return $objects;
		}
	}
