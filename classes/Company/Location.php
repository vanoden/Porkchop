<?php
	namespace Company;

	class Location Extends \BaseModel {
		private $schema_version = 1;
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
			$this->_tableName = 'company_locations';
    		parent::__construct($id);
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			list($id) = $rs->FetchRow();
			$this->id = $id;
			$this->details();
		}

		public function details(): bool {
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}

			$object = $rs->FetchNextObject(false);
			if ($object) {
				$this->id = $object->id;
				$this->company_id = $object->company_id;
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
				$this->domain_id = $object->domain_id;
				$this->host = $object->host;
				$this->cached(true);
				$this->exists(true);
			}
			else {
				$this->id = null;
				$this->company_id = null;
				$this->code = null;
				$this->address_1 = null;
				$this->address_2 = null;
				$this->city = null;
				$this->state_id = null;
				$this->zip_code = null;
				$this->zip_ext = null;
				$this->content = null;
				$this->order_number_sequence = null;
				$this->active = null;
				$this->name = null;
				$this->service_contact = null;
				$this->sales_contact = null;
				$this->domain_id = null;
				$this->host = null;
			}
			return true;
		}

		public function add($parameters = []) {
			if (! preg_match('/^\d+$/',$parameters['company_id'])) {
				$this->error("company_id parameter required");
				return false;
			}
			if (! preg_match('/\w/',$parameters['code'])) {
				$this->error("code parameter required");
				return false;
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();

			return $this->update($parameters);
		}

		public function update($parameters = []): bool {
			if (! preg_match('/^\d+$/',$this->id)) {
				$this->error("Valid id required for details in Company::Domain::update");
				return null;
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			return $this->details();
		}

		public function domain() {
			return new \Company\Domain($this->domain_id);
		}
	}
