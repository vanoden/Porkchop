<?php
	namespace Company;

	class Location Extends \BaseModel {
		private $schema_version = 1;
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
		public $name = "";
		public $service_contact;
		public $sales_contact;
		public $domain_id;
		public $host;

		/**
		 * Constructor
		 * @param int $id 
		 * @return void 
		 */
		public function __construct($id = 0) {
			$this->_tableName = 'company_locations';
			$this->_cacheKeyPrefix = 'company.location';
    		parent::__construct($id);
		}

		/**
		 * Get the location by the host name WRAPPER
		 * @param string $hostname
		 * @return bool
		 */
		public function getByHost(string $hostname): bool {
			return $this->get($hostname);
		}

		/**
		 * Get the location by the host name
		 * @param string $hostname
		 * @return bool
		 */
		public function get(string $hostname): bool {
			$this->clearError();

			// Connect to Database
			$database = new \Database\Service();

			// Prepare Query
			$get_object_query = "
				SELECT	id
				FROM	company_locations
				WHERE	host = ?
			";

			// Bind Parameters
			$database->AddParam($hostname);

			// Execute Query
			$rs = $database->Execute($get_object_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			list($id) = $rs->FetchRow();

			if (empty($id)) $id = 0;
			$this->id = $id;
			return $this->details();
		}

		/**
		 * Get the location details
		 * @return bool
		 */
		public function details(): bool {
			$this->clearError();

			if (empty($this->id)) {
				$this->error("id required for details in Company::Location::details");
				return false;
			}

			$cache = $this->cache();
			$cachedData = $cache->get();
			if (!empty($cachedData) && !empty($cachedData->id) && !empty($cachedData->name)) {
				foreach ($cachedData as $key => $value) {
					$this->$key = $value;
				}
				$this->cached(true);
				$this->exists(true);
				return true;
			}

			// Connect to Database
			$database = new \Database\Service();

			// Prepare Query
			$get_details_query = "
				SELECT	*
				FROM	company_locations
				WHERE	id = ?
			";

			// Bind Parameters
			$database->AddParam($this->id);

			// Execute Query
			$rs = $database->Execute($get_details_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
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

				$object->phone_code = null;
				$object->phone_pre = null;
				$object->phone_post = null;
				$object->fax_code = null;
				$object->fax_pre = null;
				$object->fax_post = null;

				// Cache the data
				$cache->set($object);

				// Didn't come from cache
				$this->cached(false);
				$this->exists(true);
			}
			else {
				$this->id = 0;
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

		/**
		 * Add a new location
		 * @param array $parameters 
		 * @return bool 
		 */
		public function add($parameters = []) {
			$this->clearError();

			$database = new \Database\Service();

			if (! preg_match('/^\d+$/',$parameters['company_id'])) {
				$this->error("company_id parameter required");
				return false;
			}
			if (! preg_match('/\w/',$parameters['code'])) {
				$this->error("code parameter required");
				return false;
			}

			// Prepare Query
			$add_object_query = "
				INSERT
				INTO	company_locations
				(		company_id,
						code,
						host
				)
				VALUES
				(		?,?,?
				)
			";

			$database->AddParam($parameters['company_id']);
			$database->AddParam($parameters['code']);
			$database->AddParam($parameters['code']);

			$database->Execute($add_object_query);

			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			$this->id = $database->Insert_ID();
app_log("New Location ".$this->id." created for ".$this->company()->id,'notice',__FILE__,__LINE__);
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
		 * Update the location
		 * @param array $parameters 
		 * @return bool 
		 */
		public function update($parameters = []): bool {
			$this->clearError();
			$this->clearCache();

			$database = new \Database\Service();

			if (! preg_match('/^\d+$/',$this->id)) {
				$this->error("Valid id required for details in Company::Domain::update");
				return null;
			}

			# Update Object
			$update_object_query = "
				UPDATE	company_locations
				SET		id = id";

			if (isset($parameters['name'])) {
				$update_object_query .= ",
					name = ?";
				$database->AddParam($parameters['name']);
			}

			if (preg_match('/^\w[\w\-\.]+$/',$parameters['host'])) {
				$update_object_query .= ",
					host = ?";
				$database->AddParam($parameters['host']);
			}

			if (preg_match('/^\d+$/',$parameters['domain_id'])) {
				$update_object_query .= ",
					domain_id = ?";
				$database->AddParam($parameters['domain_id']);
			}

			$update_object_query .= "
				WHERE	id = ?
			";

			$database->AddParam($this->id);

			$database->trace(9);
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
		 * Get the location name
		 * @return string 
		 */
		public function name(): string {
			if (!isset($this->name)) $this->name = "";
			return $this->name;
		}

		/**
		 * Get the location domain
		 * @return \Company\Domain
		 */
		public function domain() {
			return new \Company\Domain($this->domain_id);
		}

		/**
		 * Get the Company
		 * @return \Company\Company
		 */
		public function company() {
			return new \Company\Company($this->company_id);
		}
	}
