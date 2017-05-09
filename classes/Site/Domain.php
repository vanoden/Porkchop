<?
	namespace Site;

	class Domain {
		private $schema_version = 1;
		public $error;
		public $id;
		public $status;
		public $comments;
		public $location;
		public $name;
		public $date_registered;
		public $date_created;
		public $date_expires;
		public $registration_period;
		public $register;
		public $company;

		public function __construct($id = 0) {
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function get($name) {
			$get_object_query = "
				SELECT	id
				FROM	company_domains
				WHERE	domain_name = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($name)
			);
			if (! $rs) {
				$this->error = "SQL Error in Site::Domain::get(): ".$GLOBALS['_database']->ErrorMsg();
				return undef;
			}
			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
		}

		public function details() {
			$get_details_query = "
				SELECT	*
				FROM	company_domains
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_details_query,
				array($this->id)
			);
			if (! $rs) {
				$this->error = "SQL Error in Site::Domain::details: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$object = $rs->FetchNextObject(false);
			$this->status = $object->status;
			$this->comments = $object->comments;
			$this->name = $object->domain_name;
			$this->date_registered = $object->date_registered;
			$this->date_created = $object->date_created;
			$this->date_expires = $object->date_expires;
			$this->registration_period = $object->registration_period;
			$this->register = $object->register;
			$this->company = new \Site\Company($object->company_id);
			return $object;
		}

		public function add($parameters = array()) {
			if (! isset($parameters['company_id'])) {
				if (preg_match('/^\d+$/',$GLOBALS['_SESSION_']->company->id)) {
					$parameters['company_id'] = $GLOBALS['_SESSION_']->company->id;
				}
				else {
					$this->error = "company must be set for Site::Domain::add";
					return undef;
				}
			}
			if (! preg_match('/\w/',$parameters['name'])) {
				$this->error = "name parameter required in Site::Domain::add";
				return undef;
			}
			if (! preg_match('/^(0|1)$/',$parameters['status'])) {
				$parameters['status'] = 0;
			}
			
			$add_object_query = "
				INSERT
				INTO	company_domains
				(		company_id,
						domain_name,
						status
				)
				VALUES
				(		".$parameters['company_id'].",
						".$GLOBALS['_database']->qstr($parameters['name'],get_magic_quotes_gpc()).",
						".$parameters['status']."
				)
			";

			$GLOBALS['_database']->Execute($add_object_query);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Site::Domain::add: ".$GLOBALS['_database']->ErrorMsg();
				return undef;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			
			return $this->update($this->id,$parameters);
		}

		public function update($parameters = array()) {
			if (! preg_match('/^\d+$/',$this->id)) {
				$this->error = "Valid id required for details in Site::Domain::update";
				return undef;
			}

			# Update Object
			$update_object_query = "
				UPDATE	company_domains
				SET		id = id";

			if ($parameters['name'])
				$update_object_query .= ",
						domain_name = ".$GLOBALS['_database']->qstr($parameters['name'],get_magic_quotes_gpc());

			if (preg_match('/^(0|1)$/',$parameters['active']))
				$update_object_query .= ",
						active = ".$parameters['active'];

			if (preg_match('/^\d+$/',$parameters['status']))
				$update_object_query .= ",
						status = ".$parameters['status'];

			if (isset($parameters['location_id']) && strlen($parameters['location_id'])) {
				$location = new \Site\Location($parameters['location_id']);
				if (! $location->id) {
					$this->error = "Location ID not found";
					return false;
				}
				$update_object_query .= ",
					location_id = ".$location->id;
			}

			$update_object_query .= "
				WHERE	id = ?
			";

			$GLOBALS['_database']->Execute(
				$update_object_query,
				array($this->id)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Site::Domain::update: ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			
			return $this->details($id);
		}
	}
?>