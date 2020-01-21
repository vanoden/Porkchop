<?php
	namespace Register\Organization;

	class Locations {
	
		private $schema_version = 15;
		public $error;
		public $organization;
		public $location;
		public $name;

		public function __construct($organization_id = 0, $location_id = 0) {
            $this->get(array('organization_id' => $organization_id, 'location_id' => $location_id));
		}
		
		public function get($parameters = array()) {
			$this->organization = new \Register\Organization($parameters['organization_id']);
			if ($this->organization->error) {
				$this->error = "Error loading organization: " . $this->organization->error;
				return null;
			}

            $this->location = new \Register\Organization\Location($parameters['location_id']);
			if ($this->location->error) {
				$this->error = "Error loading location: " . $this->location->error;
				return null;
			}
			$this->details();
		}

        private function details() {

            $get_details_query = "
                SELECT
                        organization_id,
						location_id,
						name
                FROM    register_organization_locations
                WHERE   organization_id = ?
				AND		location_id = ?
            ";

            $rs = $GLOBALS['_database']->Execute (
				$get_details_query,
				array($this->organization->id, $this->location->id)
			);
            if (! $rs) {
                $this->error = "SQL Error in Register::Organization::Locations::::details(): ".$GLOBALS['_database']->ErrorMsg();
                return null;
            }

			$object = $rs->FetchNextObject(false);			
			$this->organization = new \Register\Organization($object->organization_id);
            $this->location = new \Register\Organization\Location($object->location_id);
			$this->name = $object->name;

			app_log("Organization Location " . $this->organization->name . " at this location is called: " . $this->name, 'trace');
        }
        
		public function add($parameters = array()) {

			if (! preg_match('/^\d+$/',$parameters['organization_id'])) {
				$this->error = "organization_id parameter required for Register::Organization::Locations::add";
				return undef;
			}

			if (! preg_match('/^\d+$/',$parameters['location_id'])) {
				$this->error = "location_id required in Register::Organization::Locations::add";
				return undef;
			}
	
			$add_object_query = "
				INSERT
				INTO	register_organization_locations (
						organization_id,
						location_id,
						name
				) VALUES (?, ?, ?)
			";

			$GLOBALS['_database']->Execute(
				$add_object_query,
				array($parameters["organization_id"], $parameters["location_id"], $parameters["name"])
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Register::Organization::Locations::add: ".$GLOBALS['_database']->ErrorMsg();
				return undef;
			}
			return true;
		}

		public function update($parameters = array()) {

            $this->get($parameters);

			// Update Object
			$update_object_query = "
				UPDATE	register_organization_locations
				SET		organization_id = organization_id";

			if ($parameters['name'])
				$update_object_query .= ",
						name = ".$GLOBALS['_database']->qstr($parameters['name'],get_magic_quotes_gpc);

			if (preg_match('/^\d+$/',$parameters['organization_id']))
				$update_object_query .= ",
					organization_id = ".$GLOBALS['_database']->qstr($parameters['organization_id'],get_magic_quotes_gpc);

			if (preg_match('/^\d+$/',$parameters['location_id']))
				$update_object_query .= ",
					location_id = ".$GLOBALS['_database']->qstr($parameters['location_id'],get_magic_quotes_gpc);

			$update_object_query .= "
				WHERE organization_id = ? AND location_id = ?
			";

			$GLOBALS['_database']->Execute(
				$update_object_query,
				array($parameters['organization_id', $parameters['location_id')
			);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Register::Organization::Locations::update: ".$GLOBALS['_database']->ErrorMsg();
				return undef;
			}
            return true;
		}

	}
}
