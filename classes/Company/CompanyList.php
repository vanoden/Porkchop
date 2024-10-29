<?php
	namespace Company;

	class CompanyList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = 'Company\Company';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build the query
			$find_objects_query = "
				SELECT	id
				FROM	company_companies
				WHERE	id = id";

			// Execute the query
			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}
			
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$company = new Company($id);
				array_push($objects,$company);
				$this->incrementCount();
			}
			return $objects;
		}
	}
