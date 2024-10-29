<?php
	namespace Company;

	class DepartmentList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = 'Company\Department';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build the query
			$find_objects_query = "
				SELECT	id
				FROM	company_departments
				WHERE	id = id
			";

			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			$objects = array();
			while(list($id) = $rs->FetchRow()) {
				$object = new Department($id);
				array_push($objects,$object);
				$this->incrementCount();
			}
			return $objects;
		}
	}
