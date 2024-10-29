<?php
	namespace Shipping;

	class PackageList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Shipping\Package';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Dereference Working Class
			$workingClass = new $this->_modelName;

			// Build Query
			$find_objects_query = "
				SELECT	`".$workingClass->_tableIdColumn()."`
				FROM	`".$workingClass->_tableName()."`
				WHERE	`".$workingClass->_tableIdColumn()."` = `".$workingClass->_tableIdColumn()."`";

			// Add Parameters
			if (!empty($parameters['shipment_id']) && is_numeric($parameters['shipment_id'])) {
				$find_objects_query .= "
				AND		shipment_id = ?";
				$database->AddParam($parameters['shipment_id']);
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
				$object = new $this->_modelName($id);
				array_push($objects,$object);
				$this->_count ++;
			}
			return $objects;
		}
	}
