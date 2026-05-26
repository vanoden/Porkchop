<?php
	namespace Sales\Document;

	class ItemList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Sales\Document\Item';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$find_objects_query = "
				SELECT	id
				FROM	`sales_document_items`
				WHERE	id = id
			";

			// Add Parameters
			$validationClass = new $this->_modelName;
			$document_id = $parameters['document_id'] ?? $parameters['order_id'] ?? null;
			if (!empty($document_id) && is_numeric($document_id)) {
				$order = new \Sales\SalesOrder((int) $document_id);
				if ($order->exists()) {
					$find_objects_query .= "
						AND		document_id = ?
					";
					$database->AddParam((int) $document_id);
				}
				else {
					$this->error('Document not found');
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

			// Document Clause
			$find_objects_query .= "
				ORDER BY document_id, line_number
			";

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
			while (list($id) = $rs->FetchRow()) {
				if (!is_numeric($id) || (int)$id < 1) continue;
				$item = new \Sales\Document\Item((int)$id);
				if ($item->error()) {
					$this->error("Error getting details for ".$this->_modelName.": ".$item->error());
					return [];
				}
				array_push($objects, $item);
				$this->_count++;
			}

			return $objects;
		}
	}
