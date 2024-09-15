<?php
	namespace Company;

	class Company Extends \BaseModel {

		public $login;
		public $name;
		public $primary_domain;
		public $status;
		public $deleted;

		public function __construct($id = 0) {
			$this->_tableName = 'company_companies';
			$this->_tableUKColumn = 'name';
			$this->_cacheKeyPrefix = 'site.company';
    		parent::__construct($id);
		}

		public function add($parameters = []) {
			if (! preg_match('/\w/',$parameters['name'])) {
				$this->error("name parameter required in Company::Company::add");
				return 0;
			}
			
			$add_object_query = "
				INSERT
				INTO	company_companies
				(name)
				VALUES
				(?)";

			$GLOBALS['_database']->Execute($add_object_query,array($parameters['name']));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return 0;
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

		public function update($parameters = []): bool {
			if (! preg_match('/^\d+$/',$this->id)) {
				$this->error("Valid id required for details in company::Company::update");
				return false;
			}

			$cache = $this->cache();

			# Update Object
			$update_object_query = "
				UPDATE	company_companies
				SET		id = id";

			$bind_params = array();
			if (isset($parameters['name'])) {
				$update_object_query .= ",
					name = ?";
				array_push($bind_params,$parameters['name']);
			}
			if (isset($parameters['status'])) {
				$update_object_query .= ",
					status = ?";
				array_push($bind_params,$parameters['status']);
			}

			$update_object_query .= "
				WHERE	id = ?
			";
			array_push($bind_params,$this->id);

			$GLOBALS['_database']->Execute(
				$update_object_query,$bind_params
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			$cache->delete();

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

		/**
		 * Get the company name
		 * @return string
		 */
		public function name(): string {
			return $this->name;
		}

		/**
		 * Get an array of locations for the company
		 * @return array locations
		 */
		public function locations() {
			$locationList = new \Company\LocationList();
			$locations = $locationList->find();
			if ($locationList->error()) $this->error($locationList->error());

			return $locations;
		}

		/**
		 * Get an array of domains for the company
		 * @return array domains
		 */
		public function domains() {
			$domainList = new \Company\DomainList();
			$domains = $domainList->find();
			if ($domainList->error()) $this->error($domainList->error());

			return $domains;
		}
	}
