<?php
	namespace Company;

	class Domain Extends \BaseModel {
		public $status;
		public $comments;
		public $location_id;
		public $name = "";
		public $date_registered;
		public $date_created;
		public $date_expires;
		public $registration_period;
		public $registrar;
		public $company_id;

		/**
		 * Constructor
		 * @param int $id 
		 * @return void 
		 */
		public function __construct($id = 0) {
			$this->_tableName = 'company_domains';
			$this->_tableUKColumn = 'domain_name';
			$this->_cacheKeyPrefix = 'company.domain';
    		parent::__construct($id);
		}

		/**
		 * Add a new Domain
		 * @param array $parameters 
		 * @return bool 
		 */
		public function add($parameters = []) {
			$this->clearError();

			$database = new \Database\Service();

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
			$database->AddParam($parameters['company_id']);
			$database->AddParam($parameters['name']);
			$database->AddParam($parameters['status']);

			$database->Execute($add_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			$this->id = $database->Insert_ID();

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

		/**
		 * Update Domain Details
		 * @param array $parameters 
		 * @return bool 
		 */
		public function update($parameters = []): bool {
			$this->clearError();
			$this->clearCache();

			if (! preg_match('/^\d+$/',$this->id)) {
				$this->error("Valid id required for details in Company::Domain::update");
				return false;
			}

			$database = new \Database\Service();

			// Prepare Query
			$update_object_query = "
				UPDATE	company_domains
				SET		id = id";

			if (isset($parameters['name']) && $parameters['name']) {
				$update_object_query .= ",
						domain_name = ?";
				$database->AddParam($parameters['name']);
			}

			if (isset($parameters['active']) && preg_match('/^(0|1)$/',$parameters['active'])) {
				$update_object_query .= ",
						active = ?";
				$database->AddParam($parameters['active']);
			}

			if (isset($parameters['status']) && preg_match('/^\d+$/',$parameters['status'])) {
				$update_object_query .= ",
						status = ?";
				$database->AddParam($parameters['status']);
			}

			if (isset($parameters['registrar'])) {
				$update_object_query .= ",
						register = ?";
				$database->AddParam($parameters['registrar']);
			}

			if (isset($parameters['date_registered'])) {
				$update_object_query .= ",
						date_registered = ?";
				$database->AddParam(get_mysql_date($parameters['date_registered']));
			}

			if (isset($parameters['date_expires'])) {
				$update_object_query .= ",
						date_expires = ?";
				$database->AddParam(get_mysql_date($parameters['date_expires']));
			}

			if (isset($parameters['location_id']) && strlen($parameters['location_id'])) {
				$location = new \Company\Location($parameters['location_id']);
				if (! $location->id) {
					$this->error("Location ID not found");
					return false;
				}
				$update_object_query .= ",
					location_id = ?";
				$database->AddParam($location->id);
			}

			$update_object_query .= "
				WHERE	id = ?
			";
			$database->AddParam($this->id);

			$database->Execute($update_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
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

		/**
		 * Get Details for Specified Domain
		 * @return bool 
		 */
		public function details(): bool {
			$this->clearError();

			// Implement Object Cache
			$cache = $this->cache();
			$cachedData = $cache->get();
			if (!empty($cachedData) && !empty($cachedData->name)) {
				foreach ($cachedData as $key => $value) {
					$this->$key = $value;
				}
				$this->cached(true);
				$this->exists(true);
				return true;
			}

			// Initialize Database
			$database = new \Database\Service();

			// Prepare Query
			$get_details_query = "
				SELECT	*
				FROM	company_domains
				WHERE	id = ?
			";
			$database->AddParam($this->id);
			$rs = $database->Execute($get_details_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
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
				$this->company_id = $object->company_id;
				$cache->set($object);
				$this->cached(false);
				$this->exists(true);
				return true;
			}
			else {
				$this->id = 0;
				$this->status = null;
				$this->comments = null;
				$this->name = null;
				$this->date_registered = null;
				$this->date_created = null;
				$this->date_expires = null;
				$this->registration_period = null;
				$this->registrar = null;
				$this->company_id = 0;
				$this->exists(false);
				return false;
			}
		}

		public function name(): ?string {
			return $this->name;
		}

		public function company(): Company {
			return new Company($this->company_id);
		}

		public function location(): Location {
			return new Location($this->location_id);
		}

		public function validDomainName($string): bool {
			if (preg_match('/^\w[\w\.\-]+$/',$string)) {
				return true;
			}
			else {
				return false;
			}
		}
	}
