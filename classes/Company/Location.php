<?php
	namespace Company;

	class Location {
		private $schema_version = 1;
		public $error;
		public $id;
		public $company_id;
		public $code;
		public $address_1;
		public $address_2;
		public $city;
		public $state_id;
		public $zip_code;
		public $zip_ext;
		public $content;
		public $order_number_sequence;
		public $active;
		public $name;
		public $service_contact;
		public $sales_contact;
		public $domain_id;
		public $host;

		public function __construct($id = 0) {
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function get($name) {
			$get_object_query = "
				SELECT	id
				FROM	company_locations
				WHERE	code = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($name)
			);
			if (! $rs) {
				$this->error = "SQL Error in Company::Location::get(): ".$GLOBALS['_database']->ErrorMsg();
				return undef;
			}
			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
		}

		public function getByHost($hostname) {
			$get_object_query = "
				SELECT	id
				FROM	company_locations
				WHERE	host = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($hostname)
			);
			if (! $rs) {
				$this->error = "SQL Error in Company::Location::getByHost(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($id) = $rs->FetchRow();
			$this->id = $id;
			$this->details();
		}

		public function details() {
			$get_details_query = "
				SELECT	*
				FROM	company_locations
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_details_query,
				array($this->id)
			);
			if (! $rs) {
				$this->error = "SQL Error in Company::Domain::details: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$object = $rs->FetchNextObject(false);
			if ($object) {
				$this->id = $object->id;
				$this->company = new \Company\Company($object->company_id);
				$this->code = $object->code;
				$this->address_1 = $object->address_1;
				$this->address_2 = $object->address_2;
				$this->city = $object->city;
				$this->state_id = $object->state_id;
				$this->zip_code = $object->zip_code;
				$this->zip_ext = $object->zip_ext;
				$this->content = $object->content;
				$this->order_number_sequence = $object->order_number_sequence;
				$this->active = $object->active;
				$this->name = $object->name;
				$this->service_contact = $object->service_contact;
				$this->sales_contact = $object->sales_contact;
				$this->domain = new \Company\Domain($object->domain_id);
				$this->host = $object->host;
				return $object;
			}
			else {
				return new \stdClass();
			}
		}

		public function add($parameters = array()) {
			if (! preg_match('/^\d+$/',$parameters['company_id'])) {
				$this->error = "company_id parameter required for Company::Domain::add";
				return undef;
			}
			if (! preg_match('/\w/',$parameters['code'])) {
				$this->error = "code parameter required in Company::Domain::add";
				return undef;
			}
	
			$add_object_query = "
				INSERT
				INTO	company_locations
				(		company_id,
						code
				)
				VALUES
				(		?,?
				)
			";

			$GLOBALS['_database']->Execute(
				$add_object_query,
				array($parameters["company_id"],$parameters["code"])
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Company::Domain::add: ".$GLOBALS['_database']->ErrorMsg();
				return undef;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();

			return $this->update($parameters);
		}

		public function update($parameters = array()) {
			if (! preg_match('/^\d+$/',$this->id)) {
				$this->error = "Valid id required for details in Company::Domain::update";
				return undef;
			}

			# Update Object
			$update_object_query = "
				UPDATE	company_locations
				SET		id = id";

			$bind_params = array();
			if (isset($parameters['name'])) {
				$update_object_query .= ",
					name = ?";
				array_push($bind_params,$parameters['name']);
			}

			if (preg_match('/^\w[\w\-\.]+$/',$parameters['host'])) {
				$update_object_query .= ",
					host = ?";
				array_push($bind_params,$parameters['host']);
			}

			if (preg_match('/^\d+$/',$parameters['domain_id'])) {
				$update_object_query .= ",
					domain_id = ?";
				array_push($bind_params,$parameters['domain_id']);
			}

			$update_object_query .= "
				WHERE	id = ?
			";
			array_push($bind_params,$this->id);
			$GLOBALS['_database']->Execute($update_object_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Company::Location::update: ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			return $this->details();
		}
	}
