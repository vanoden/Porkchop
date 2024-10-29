<?php
	namespace Sales\Order;

	class ItemList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Sales\Order\Item';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$find_objects_query = "
				SELECT	id
				FROM	sales_order_items
				WHERE	id = id
			";

			// Add Parameters
			$validationClass = new $this->_modelName;
			if (!empty($parameters['order_id']) && is_numeric($parameters['order_id'])) {
				$order = new $this->_modelName($parameters['order_id']);
				if ($order->exists()) {
					$find_objects_query .= "
						AND		order_id = ?
					";
					$database->AddParam($parameters['order_id']);
				}
				else {
					$this->error('Order not found');
					return [];
				}
			}
			if (!empty($parameters['status'])) {
				if (is_array($parameters['status'])) {
					$statii = [];
					foreach ($parameters['status'] as $status) {
						if ($validationClass->validStatus($status)) {
							array_push($statii, $status);
						}
					}
					$find_objects_query .= "
					AND	status in (".implode(',',$statii).")";
				}
				elseif ($validationClass->validStatus($parameters['status'])) {
					$find_objects_query .= "
					AND		status = ?";
					$database->AddParam($parameters['status']);
				}
				else {
					$this->error('Invalid status');
					return [];
				}
			}

			// Order Clause
			$find_objects_query .= "
				ORDER BY order_id, line_number
			";

			// Limit Clause
			$find_objects_query .= $this->limitClause($controls);

			// Execute Query
			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
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
