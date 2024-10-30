<?php
	namespace Sales;

	class OrderList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Sales\Order';
			$this->_tableDefaultSortBy = 'date_event';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Initialize Working Class
			$workingClass = new $this->_modelName;

			// Build Query
			$find_objects_query = "
				SELECT	`".$workingClass->_tableIDColumn()."`
				FROM	`".$workingClass->_tableName()."`
				WHERE	`".$workingClass->_tableIDColumn()."` = `".$workingClass->_tableIDColumn()."`
			";

			// Add Parameters
			if (!empty($parameters['id']) && is_numeric($parameters['id'])) {
				$order = new $this->_modelName($parameters['id']);
				if ($order->exists()) {
					$find_objects_query .= "
						AND		id = ?
					";
					$database->AddParam($parameters['id']);
				}
				else {
					$this->error('Order not found');
					return [];
				}
			}
			
			if (!empty($parameters['customer_id']) && is_numeric($parameters['customer_id'])) {
				$customer = new \Register\Customer($parameters['customer_id']);
				if ($customer->exists()) {
					$find_objects_query .= "
						AND		customer_id = ?
					";
					$database->AddParam($parameters['customer_id']);
				}
				else {
					$this->error('Customer not found');
					return [];
				}
			}
			
			if (!empty($parameters['status'])) {
				if (is_array($parameters['status'])) {
					if (count($parameters['status']) > 0) {
						$statii = [];
						foreach ($parameters['status'] as $status) {
							if ($workingClass->validStatus($status)) {
								array_push($statii, $status);
							}
							else {
								$this->error('Invalid status');
								return [];
							}
						}
						$find_objects_query .= "
							AND status in ('".implode("','",$statii)."')";
					}
					else {
						$find_objects_query .= "
							AND id != id";
					}
				}
				elseif (!empty($parameters['status'])) {
					if ($workingClass->validStatus($parameters['status'])) {
						$find_objects_query .= "
							AND status = ?";
						$database->AddParam($parameters['status']);
					}
					else {
						$this->error('Invalid status');
						return [];
					}
				}
			}

			// apply the order and sort direction
			if (!empty($controls['sort']) && !empty($controls['order'])) {
				$order_by_clause = " ORDER BY ";
				$sort_direction_clause = " `" . $controls['sort'] . "` " . strtoupper($controls['order']);
				$find_objects_query .= $order_by_clause . $sort_direction_clause;
			}

			// Limit Clause
			$find_objects_query .= $this->limitClause($controls);

			// Execute Query
			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
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
