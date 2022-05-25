<?php
	namespace Register\Organization;
	
	class LocationList {
	
		public $error;
		public $count;

		public function find($parameters = array()) {
		
			$find_objects_query = "
				SELECT	id
				FROM	register_locations
				WHERE	id = id
			";
			$bind_params = array();
			if (isset($parameters['company_id']) && strlen($parameters['company_id'])) {
				if (preg_match('/^\d+$/',$parameters['company_id'])) {
					$find_objects_query .= "
						AND	company_id = ?";
					array_push($bind_params,$parameters['company_id']);
				}
				else {
					$this->error = "Invalid company_id";
					return false;
				}
			}
			
			if (isset($parameters['domain_id']) && strlen($parameters['domain_id'])) {
				if (preg_match('/^\d+$/',$parameters['domain_id'])) {
					$find_objects_query .= "
						AND	domain_id = ?";
					array_push($bind_params,$parameters['domain_id']);
				}
				else {
					$this->error = "Invalid domain_id";
					return false;
				}
			}

			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if (! $rs) {
				$this->error = "SQL Error in Register::Organization::LocationList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$location = new \Register\Organization\Location($id);
				array_push($objects,$location);
				$this->count ++;
			}
			return $objects;
		}
	}
