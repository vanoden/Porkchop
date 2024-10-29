<?php
	namespace Register\Organization;
	
	class LocationList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Register\Organization\Location';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$find_objects_query = "
				SELECT	id
				FROM	register_locations
				WHERE	id = id
			";

			// Add Parameters
			$validationClass = new $this->_modelName;
			if (!empty($parameters['organization_id']) && is_numeric($parameters['organization_id'])) {
				$organization = new \Register\Organization($parameters['organization_id']);
				if ($organization->exists()) {
					$find_objects_query .= "
						AND	organization_id = ?
					";
					$database->AddParam($parameters['organization_id']);
				}
				else {
					$this->setError('Invalid organization_id');
					return [];
				}
			}
			if (!empty($parameters['company_id']) && is_numeric($parameters['company_id'])) {
				$company = new \Register\Organization($parameters['company_id']);
				if ($company->exists()) {
					$find_objects_query .= "
						AND	company_id = ?
					";
					$database->AddParam($parameters['company_id']);
				}
				else {
					$this->setError('Invalid company_id');
					return [];
				}
			}
			
			if (!empty($parameters['domain_id']) && is_numeric($parameters['domain_id'])) {
				$domain = new \Company\Domain($parameters['domain_id']);
				if ($domain->exists()) {
					$find_objects_query .= "
						AND	domain_id = ?
					";
					$database->AddParam($parameters['domain_id']);
				}
				else {
					$this->setError('Invalid domain_id');
					return [];
				}
			}

			// Limit Clause
			$find_objects_query .= $this->limitClause($controls);

			// Execute Query
			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$location = new \Register\Organization\Location($id);
				array_push($objects,$location);
				$this->incrementCount();
			}
			return $objects;
		}
	}
