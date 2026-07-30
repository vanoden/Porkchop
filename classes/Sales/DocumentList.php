<?php
	namespace Sales;

	abstract class DocumentList Extends \BaseListClass {
		protected $document_type = 'SALES_ORDER'; // Default order type

		/** @var string[] Columns allowed for ORDER BY on sales_documents */
		protected $_sortableColumns = ['id', 'code', 'status', 'customer_id', 'salesperson_id', 'organization_id'];

		public function __construct() {
			// date_event lives on sales_document_events, not sales_documents
			$this->_tableDefaultSortBy = 'id';
			$this->_tableDefaultSortOrder = 'DESC';
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
				WHERE	`type` = ?
			";

			// Add Parameters
			$database->AddParam($this->document_type);

			if (!empty($parameters['id']) && is_numeric($parameters['id'])) {
				$order = new $this->_modelName($parameters['id']);
				if ($order->exists()) {
					$find_objects_query .= "
						AND		`id` = ?
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
						AND		`customer_id` = ?
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
						$placeholders = implode(',', array_fill(0, count($statii), '?'));
						$find_objects_query .= "
							AND `status` IN ({$placeholders})";
						foreach ($statii as $s) {
							$database->AddParam($s);
						}
					}
					else {
						$find_objects_query .= "
							AND `id` != `id`";
					}
				}
				elseif (!empty($parameters['status'])) {
					if ($workingClass->validStatus($parameters['status'])) {
						$find_objects_query .= "
							AND `status` = ?";
						$database->AddParam($parameters['status']);
					}
					else {
						$this->error('Invalid status');
						return [];
					}
				}
			}

			// Apply sort with allowlist (hasField is unreliable before details() loads _fields)
			$sort = $controls['sort'] ?? ($this->_tableDefaultSortBy ?? $workingClass->_tableIDColumn());
			$order = strtoupper($controls['order'] ?? ($this->_tableDefaultSortOrder ?? 'ASC'));
			$order = in_array($order, ['ASC', 'DESC'], true) ? $order : 'ASC';
			if (in_array($sort, $this->_sortableColumns, true)) {
				$find_objects_query .= " ORDER BY `{$sort}` {$order}";
			}

			// Limit Clause
			$find_objects_query .= $this->limitClause($controls);

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
				array_push($objects, $orderObj);
				$this->incrementCount();
			}

			return $objects;
		}

	}
