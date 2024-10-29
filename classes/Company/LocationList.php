<?php
	namespace Company;

	class LocationList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = 'Company\Location';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build the query
			$find_objects_query = "
				SELECT	id
				FROM	company_locations
				WHERE	id = id
			";

			// Add Parameters
			if (!empty($parameters['company_id'])) {
				if (preg_match('/^\d+$/',$parameters['company_id'])) {
					$find_objects_query .= "
						AND	company_id = ".$parameters['company_id'];
				}
				else {
					$this->error("Invalid company_id");
					return null;
				}
			}
			if (!empty($parameters['domain_id'])) {
				if (preg_match('/^\d+$/',$parameters['domain_id'])) {
					$find_objects_query .= "
						AND	domain_id = ".$parameters['domain_id'];
				}
				else {
					$this->error("Invalid domain_id");
					return [];
				}
			}

			// Execute the query
			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$location = new \Company\Location($id);
				array_push($objects,$location);
				$this->incrementCount();
			}
			return $objects;
		}
	}
