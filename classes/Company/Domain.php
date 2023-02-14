<?php
	namespace Company;

	class Domain Extends \BaseClass {
		private $schema_version = 1;
		public $id;
		public $status;
		public $comments;
		public $location_id;
		public $name;
		public $date_registered;
		public $date_created;
		public $date_expires;
		public $registration_period;
		public $registrar;
		public $company;

		public function __construct($id = 0) {
			$this->_tableName = 'company_domains';
			$this->_tableUKColumn = 'domain_name';
    		parent::__construct($id);
		}

		public function details(): bool {
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if (isset($object->id)) {
				$this->status = $object->status;
				$this->comments = $object->comments;
				$this->name = $object->domain_name;
				$this->date_registered = $object->date_registered;
				$this->date_created = $object->date_created;
				$this->date_expires = $object->date_expires;
				$this->registration_period = $object->registration_period;
				$this->location_id = $object->location_id;
				$this->registrar = $object->register;
				$this->company = new Company($object->company_id);
				return true;
			}
			else {
				$this->status = null;
				$this->comments = null;
				$this->name = null;
				$this->date_registered = null;
				$this->date_created = null;
				$this->date_expires = null;
				$this->registration_period = null;
				$this->registrar = null;
				$this->company = new Company();
				return false;
			}
		}

		public function add($parameters = []) {
			$bind_params = array();
			if (! isset($parameters['company_id'])) {
				if (preg_match('/^\d+$/',$GLOBALS['_SESSION_']->company->id)) {
					$parameters['company_id'] = $GLOBALS['_SESSION_']->company->id;
				}
				else {
					$this->error("company must be set");
					return false;
				}
			}
			if (! preg_match('/\w/',$parameters['name'])) {
				$this->error("name parameter required");
				return false;
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
				(		?,?,?)";
			array_push($bind_params,$parameters['company_id'],$parameters['name'],$parameters['status']);

			$GLOBALS['_database']->Execute($add_object_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			
			return $this->update($this->id,$parameters);
		}

		public function update($parameters = []): bool {
			if (! preg_match('/^\d+$/',$this->id)) {
				$this->error("Valid id required for details in Company::Domain::update");
				return false;
			}

			$bind_params = array();

			# Update Object
			$update_object_query = "
				UPDATE	company_domains
				SET		id = id";

			if (isset($parameters['name']) && $parameters['name']) {
				$update_object_query .= ",
						domain_name = ?";
				array_push($bind_params,$parameters['name']);
			}

			if (isset($parameters['active']) && preg_match('/^(0|1)$/',$parameters['active'])) {
				$update_object_query .= ",
						active = ?";
				array_push($bind_params,$parameters['active']);
			}

			if (isset($parameters['status']) && preg_match('/^\d+$/',$parameters['status'])) {
				$update_object_query .= ",
						status = ?";
				array_push($bind_params,$parameters['status']);
			}

			if (isset($parameters['registrar'])) {
				$update_object_query .= ",
						register = ?";
				array_push($bind_params,$parameters['registrar']);
			}

			if (isset($parameters['date_registered'])) {
				$update_object_query .= ",
						date_registered = ?";
				array_push($bind_params,get_mysql_date($parameters['date_registered']));
			}

			if (isset($parameters['date_expires'])) {
				$update_object_query .= ",
						date_expires = ?";
				array_push($bind_params,get_mysql_date($parameters['date_expires']));
			}

			if (isset($parameters['location_id']) && strlen($parameters['location_id'])) {
				$location = new \Company\Location($parameters['location_id']);
				if (! $location->id) {
					$this->error("Location ID not found");
					return false;
				}
				$update_object_query .= ",
					location_id = ?";
				array_push($bind_params,$location->id);
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

		public function location(): Location {
			return new Location($this->location_id);
		}
	}
