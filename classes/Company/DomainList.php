<?php
	namespace Company;

	class DomainList {
		public $error;
		public $count = 0;

		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	company_domains
				WHERE	id = id";

			if ($parameters['name']) {
				if (preg_match('/^[\w\-\.]+$/',$parameters['name'])) {
					$find_objects_query .= "
					AND		domain_name = ".$GLOBALS['_database']->qstr($parameters['name'],get_magic_quotes_gpc());
				}
				else {
					$this->error = "Invalid domain name";
					return false;
				}
			}

			if ($parameters['location_id']) {
				$location = new \Site\Location($parameters['location_id']);
				if (! $location->id) {
					$this->error = "Location ID not found";
					return false;
				}
				$find_objects_query .= "
				AND		location_id = ".$GLOBALS['_database']->qstr($parameters['location_id'],get_magic_quotes_gpc());
			}


			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if (! $rs) {
				$this->error = "SQL Error in Site::Domain::find: ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$domain = new \Site\Domain($id);
				array_push($objects,$domain);
			}
			return $objects;
		}
	}
