<?
	namespace Site;

	class Domain {
		private $schema_version = 1;
		public $error;
		public $id;
		public $status;
		public $comments;
		public $location_id;
		public $name;
		public $date_registered;
		public $date_created;
		public $date_expires;
		public $registration_period;
		public $register;
		public $company_id;

		public function __construct() {
		}

		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	company_domains
				WHERE	id = id";

			if ($parameters['name']) {
				$find_objects_query .= "
				AND		domain_name = ".$GLOBALS['_database']->qstr($parameters['name'],get_magic_quotes_gpc());
			}

			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if (! $rs) {
				$this->error = "SQL Error in Site::Domain::find: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			
			$objects = array();
			while (list($id) = $rs->FetchRow())
			{
				array_push($objects,$this->details($id));
			}
			return $objects;
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
			$this->location_id = $object->location_id;
			$this->name = $object->domain_name;
			$this->date_registered = $object->date_registered;
			$this->date_created = $object->date_created;
			$this->date_expires = $object->date_expires;
			$this->registration_period = $object->registration_period;
			$this->register = $object->register;
			$this->company_id = $object->company_id;
			return $object;
		}

		public function add($parameters = array()) {
			if (! preg_match('/^\d+$/',$GLOBALS['_company']->id)) {
				$this->error = "company must be set for Site::Domain::add";
				return undef;
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
				$this->error = "SQL Error in company::CompanyDomain::add: ".$GLOBALS['_database']->ErrorMsg();
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

			if ($parameters['name'])
				$update_object_query .= ",
						domain_name = ".$GLOBALS['_database']->qstr($parameters['name'],get_magic_quotes_gpc());

			if (preg_match('/^(0|1)$/',$parameters['active']))
				$update_object_query .= ",
						active = ".$parameters['active'];

			if (preg_match('/^\d+$/',$parameters['status']))
				$update_object_query .= ",
						status = ".$parameters['status'];

			# Update Object
			$update_object_query = "
				UPDATE	company_domains
				SET		id = id";
			
			$update_object_query .= "
				WHERE	id = ?
			";

			$GLOBALS['_database']->Execute(
				$update_object_query,
				array($this->id)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Site::Domain::update: ".$GLOBALS['_database']->ErrorMsg();
				return undef;
			}
			
			return $this->details($id);
		}
	}
?>