<?php
	namespace Register\Organization;

	class Location extends \BaseModel {
	
		private $schema_version = 15;
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
				FROM	register_locations
				WHERE	code = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($name)
			);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
		}

		public function getByHost($hostname) {
			$get_object_query = "
				SELECT	id
				FROM	register_locations
				WHERE	host = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($hostname)
			);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			list($id) = $rs->FetchRow();
			$this->id = $id;
			$this->details();
		}

		public function details(): bool {
			$get_details_query = "
				SELECT	*
				FROM	register_locations
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_details_query,
				array($this->id)
			);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}

			$object = $rs->FetchNextObject(false);
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
			return $object;
		}

		public function add($parameters = []) {

			if (! preg_match('/^\d+$/',$parameters['company_id'])) {
				$this->error("company_id parameter required for Register::Organization::Location::add()");
				return false;
			}
			if (! preg_match('/\w/',$parameters['code'])) {
				$this->error("code parameter required in Register::Organization::Location::add()");
				return false;
			}
	
			$add_object_query = "
				INSERT
				INTO	register_locations
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
				return false;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			
            // audit the add event
            $auditLog = new \Site\AuditLog\Event();
            $auditLog->add(array(
                'instance_id' => $this->id,
                'description' => 'Added new '.$this->_objectName(),
                'class_name' => get_class($this),
                'class_method' => 'add'
            ));

			return $this->update($parameters);
		}

		public function update($parameters = array()): bool {

			if (! preg_match('/^\d+$/',$this->id)) {
				$this->error("Valid id required for details in Register::Organization::Location::update()");
				return false;
			}

			$bind_params = array();
			// Update Object
			$update_object_query = "
				UPDATE	register_locations
				SET		id = id";
			
			if (preg_match('/^[\w\-\.]+$/',$parameters['host'])) {
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
			$GLOBALS['_database']->Execute(
				$update_object_query,
				$bind_params
			);
			
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}

			// audit the update event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Updated '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'update'
			));	
			
			return $this->details();
		}
	}
