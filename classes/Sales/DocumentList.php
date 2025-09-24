<?php
	namespace Sales;

	abstract class DocumentList Extends \BaseListClass {
		protected $document_type = 'SALES_ORDER'; // Default order type

		public function __construct() {
			$this->_tableDefaultSortBy = 'date_event';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Initialize Working Class
			$modelName = $this->_modelName ?? '\Sales\SalesOrder';
			$workingClass = new $modelName();

			// Build Query
			$find_objects_query = "
				SELECT	`".$workingClass->_tableIDColumn()."`
				FROM	`".$workingClass->_tableName()."`
				WHERE	`".$workingClass->_tableIDColumn()."` = `".$workingClass->_tableIDColumn()."`
				AND		`order_type` = ?
			";

			// Add Parameters
			$database->AddParam($this->document_type);

			if (!empty($parameters['id']) && is_numeric($parameters['id'])) {
				$order = new $this->_modelName($parameters['id']);
				if ($order->exists()) {
					$find_objects_query .= "
						AND		id = ?
					";
					$database->AddParam($parameters['id']);
				}
				else {
					$this->error('Document not found');
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

            // apply the order and sort direction with sane defaults
            $sort  = $controls['sort'] ?? ($this->_tableDefaultSortBy ?? $workingClass->_tableIDColumn());
            $order = strtoupper($controls['order'] ?? ($this->_tableDefaultSortOrder ?? 'ASC'));
            $order = in_array($order, ['ASC','DESC']) ? $order : 'ASC';
            if ($workingClass->hasField($sort)) {
                $find_objects_query .= " ORDER BY `{$sort}` {$order}";
            }

			// Limit Clause
			$find_objects_query .= $this->limitClause($controls);

			$database->debug = 'screen';
			$database->trace(9);
			// Execute Query
			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

            // Build Results
            $objects = array();
            while (list($id) = $rs->FetchRow()) {
				$orderObj = new $this->_modelName($id);
                if ($orderObj->error()) {
                    $this->error($orderObj->error());
                    return [];
                }
                array_push($objects,$orderObj);
            }

			return $objects;
		}

	}
