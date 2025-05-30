<?php
	namespace Company;

	class DomainList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = 'Company\Domain';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Service
			$database = new \Database\Service();

			// Initialize Working Class
			$workingClass = new $this->_modelName();

			// Build Query
			$find_objects_query = "
				SELECT	id
				FROM	company_domains
				WHERE	id = id";

			if (!empty($parameters['name'])) {
				// Handle Wildcards
				if (preg_match('/[\*\?]/',$parameters['name']) && preg_match('/^[\*\?\w\-\.\s]+$/',$parameters['name'])) {
					$parameters['name'] = str_replace('*','%',$parameters['name']);
					$parameters['name'] = str_replace('?','_',$parameters['name']);
					$find_objects_query .= "
					AND	domain_name LIKE ?";
					$database->AddParam($parameters['name']);
				}
				// Handle Exact Match
				elseif ($workingClass->validDomainName($parameters['name'])) {
					$find_objects_query .= "
					AND	domain_name = ?";
					$database->AddParam($parameters['name']);
				}
				else {
					$this->error("Invalid domain name");
					return [];
				}
			}

			if (!empty($parameters['location_id'])) {
				$location = new \Company\Location($parameters['location_id']);
				if (! $location->id) {
					$this->error("Location ID not found");
					return [];
				}
				$find_objects_query .= "
				AND		location_id = ?";
				$database->AddParam($parameters['location_id']);
			}

			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}
			
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$domain = new \Company\Domain($id);
				array_push($objects,$domain);
				$this->incrementCount();
			}
			
			return $objects;
		}
	}
