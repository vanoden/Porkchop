<?php
	namespace Company;

	class DomainList Extends \BaseListClass {
		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	company_domains
				WHERE	id = id";

			$bind_params = array();
			if (!empty($parameters['name'])) {
				if (preg_match('/^[\w\-\.]+$/',$parameters['name'])) {
					$find_objects_query .= "
					AND		domain_name = ?";
					array_push($bind_params,$parameters['name']);
				}
				else {
					$this->error("Invalid domain name");
					return false;
				}
			}

			if (!empty($parameters['location_id'])) {
				$location = new \Company\Location($parameters['location_id']);
				if (! $location->id) {
					$this->error("Location ID not found");
					return false;
				}
				$find_objects_query .= "
				AND		location_id = ?";
				array_push($bind_params,$parameters['location_id']);
			}

			$rs = $GLOBALS['_database']->Execute($find_objects_query,$bind_params);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
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
