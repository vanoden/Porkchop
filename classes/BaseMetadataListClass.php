<?php
	class BaseMetadataListClass Extends \BaseListClass {
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
				WHERE	`".$workingClass->_tableIdColumn()."` = `".$workingClass->_tableIdColumn()."`
			";

			// Add Parameters
			if (!empty($parameters['key'])) {
				$find_objects_query .= "
				AND		`key` = ?";
				$database->AddParam($parameters['key']);
			}

			if (!empty($parameters['object_id'])) {
				$find_objects_query .= "
				AND		`object_id` = ?";
				$database->AddParam($parameters['object_id']);
			}

			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}
			$objects = [];
			while(list($id) = $rs->FetchRow()) {
				$object = new \Site\Page\Metadata($id);
				array_push($objects,$object);
				$this->incrementCount();
			}
			return $objects;
		}
	}
