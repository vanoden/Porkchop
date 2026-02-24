<?php
	namespace Register;

	class Organization Extends \BaseModel {

		public string $name = "";
		public ?string $code = null;
		public string $status = 'NEW';
		public bool $is_reseller = false;
		public bool $is_customer = true;
		public bool $is_vendor = false;
		public $reseller;
		public string $notes = "";
		public ?int $assigned_reseller_id = null;
		public ?int $password_expiration_days = null;
		public ?int $default_billing_location_id = null;
		public ?int $default_shipping_location_id = null;
		public string $website_url = "";
		public ?int $time_based_password = 0;
		public ?string $account_number = null;
		private bool $_nocache = false;

		/** @constructor */
		public function __construct($id = null,$options = array()) {

			$this->_tableName = "register_organizations";

			// Set Valid Statuses
			$this->_addStatus(array('NEW','ACTIVE','EXPIRED','HIDDEN','DELETED'));

			// Disable cache if requested
			if (isset($options['nocache']) && $options['nocache']) $this->_nocache = true;

			// Load ID'd Record
    		parent::__construct($id);
		}

		/** @method add(parameters)
		 * Add a new Organization
		 * @param array $parameters
		 * @return bool
		 */
		public function add($parameters = []) {
			
			app_log("Register::Organization::add()",'trace',__FILE__,__LINE__);
			$database = new \Database\Service;

			if (empty($parameters['code'])) $parameters['code'] = uniqid();
			$this->clearError();
			$add_object_query = "
				INSERT
				INTO	register_organizations
				(		id,code,name,date_created)
				VALUES
				(		null,?,?,sysdate())
			";

			$database->AddParam($parameters['code']);
			$database->AddParam($parameters['name']);

			$rs = $database->Execute($add_object_query);
			if (! $rs) {			
				$this->SQLError($database->ErrorMsg());
				return null;
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

			$this->auditRecord('ORGANIZATION_CREATED','Organization has been added');
			return $this->update($parameters);
		}

		/** @method update(parameters)
		 * Update an existing Organization
		 * @param array $parameters
		 * @return bool
		 */
		public function update($parameters = []): bool {
			// Reset any previous errors
			$this->clearError();

			// Initiate Database Service
			$database = new \Database\Service;

			app_log("Register::Organization::update()",'trace',__FILE__,__LINE__);

			// Bust Cache
			$cache_key = "organization[".$this->id."]";
			$cache_item = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
			$cache_item->delete();

			$update_object_query = "
				UPDATE	register_organizations
				SET		id = id
			";

			$audit_message = "";
			if (!empty($parameters['name']) && $parameters['name'] != $this->name) {
				if (! $this->validName($parameters['name'])) {
					$this->error("Valid name required for Organization::update");
					return false;
				}
				$update_object_query .= ",
						name = ?";
				$database->AddParam($parameters['name']);
				$audit_message .= "name updated to '".$parameters['name']."'. ";
			}
			if (!empty($parameters['status']) && $parameters['status'] != $this->status) {
				if (! $this->validStatus($parameters['status'])) {
					$this->error("Valid status required for Organization::update");
					return false;
				}
				$update_object_query .= ",
						status = ?";
				$database->AddParam($parameters['status']);
				$audit_message .= "status updated to '".$parameters['status']."'. ";
			}
			if (!empty($parameters['is_reseller']) && is_numeric($parameters['is_reseller']) && $parameters['is_reseller'] != $this->is_reseller) {
				$update_object_query .= ",
						is_reseller = ?";
				$database->AddParam($parameters['is_reseller']);
				$audit_message .= "is_reseller updated to '".$parameters['is_reseller']."'. ";
			}
			if (!empty($parameters['is_customer']) && is_numeric($parameters['is_customer']) && $parameters['is_customer'] != $this->is_customer) {
				$update_object_query .= ",
						is_customer = ?";
				$database->AddParam($parameters['is_customer']);
				$audit_message .= "is_customer updated to '".$parameters['is_customer']."'. ";
			}
			if (!empty($parameters['is_vendor']) && is_numeric($parameters['is_vendor']) && $parameters['is_vendor'] != $this->is_vendor) {
				$update_object_query .= ",
						is_vendor = ?";
				$database->AddParam($parameters['is_vendor']);
				$audit_message .= "is_vendor updated to '".$parameters['is_vendor']."'. ";
			}
			if (!empty($parameters['assigned_reseller_id']) && is_numeric($parameters['assigned_reseller_id']) && $parameters['assigned_reseller_id'] != $this->assigned_reseller_id) {
				$update_object_query .= ",
						assigned_reseller_id = ?";
				$database->AddParam($parameters['assigned_reseller_id']);
				$audit_message .= "assigned_reseller_id updated to '".$parameters['assigned_reseller_id']."'. ";
			}
			if (isset($parameters['notes']) && $parameters['notes'] != $this->notes) {
				$update_object_query .= ",
						notes = ?";
				$database->AddParam($parameters['notes']);
				$audit_message .= "notes updated. ";
			}
			if (!empty($parameters['password_expiration_days']) && is_numeric($parameters['password_expiration_days']) && $parameters['password_expiration_days'] != $this->password_expiration_days) {
				$update_object_query .= ",
						password_expiration_days = ?";
                $database->AddParam($parameters['password_expiration_days']);
				$audit_message .= "password_expiration_days updated to '".$parameters['password_expiration_days']."'. ";
            }
            if (!empty($parameters['default_billing_location_id']) && $parameters['default_billing_location_id'] != $this->default_billing_location_id) {
			    $update_object_query .= ",
			    default_billing_location_id = ?";
			    $database->AddParam($parameters['default_billing_location_id']);
				$audit_message .= "default_billing_location_id updated to '".$parameters['default_billing_location_id']."'. ";
    		}
		    if (!empty($parameters['default_shipping_location_id']) && $parameters['default_shipping_location_id'] != $this->default_shipping_location_id	) {
			    $update_object_query .= ",
			    default_shipping_location_id = ?";
			    $database->AddParam($parameters['default_shipping_location_id']);
			    $audit_message .= "default_shipping_location_id updated to '".$parameters['default_shipping_location_id']."'. ";
		    }
			if (!empty($parameters['website_url']) && $parameters['website_url'] != $this->website_url) {
				$update_object_query .= ",
						website_url = ?";
				$database->AddParam($parameters['website_url']);
				$audit_message .= "website_url updated to '".$parameters['website_url']."'. ";
			}
			if (!empty($parameters['time_based_password']) && is_numeric($parameters['time_based_password']) && $parameters['time_based_password'] != $this->time_based_password) {
				$update_object_query .= ",
						time_based_password = ?";
				$database->AddParam($parameters['time_based_password']);
				$audit_message .= "time_based_password updated to '".$parameters['time_based_password']."'. ";
			}
			if (!empty($parameters['account_number']) && $parameters['account_number'] != $this->account_number) {
				$update_object_query .= ",
						account_number = ?";
				$database->AddParam($parameters['account_number']);
				$audit_message .= "account_number updated to '".$parameters['account_number']."'. ";
			}

			$update_object_query .= "
				WHERE	id = ?
			";
			$database->AddParam($this->id);
			query_log($update_object_query);
			$rs = $database->Execute($update_object_query);

			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// audit the update event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => $audit_message,
				'class_name' => get_class($this),
				'class_method' => 'update'
			));	
						
			return $this->details();
		}

		/** @method details()
		 * Get the details for this Organization
		 * @return bool
		 */
		public function details(): bool {
			app_log("Register::Organization::details()[".$this->id."]",'trace',__FILE__,__LINE__);

			// Clear any previous errors
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			$cache_key = "organization[".$this->id."]";
			$cache_item = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
			
			// Cached Organization Object, Yay!
			if ((! $this->_nocache) and ($this->id) and ($organization = $cache_item->get())) {
				$organization->_cached = true;
				$this->id = $organization->id;
				$this->name = $organization->name;
				$this->code = $organization->code;
				$this->status = $organization->status;
				$this->is_reseller = isset($organization->is_reseller) ? boolval($organization->is_reseller) : false;
				if (isset($organization->is_customer)) {
					$this->is_customer = boolval($organization->is_customer);
				} else {
					$this->is_customer = true; // Default to true if not set
				}
				$this->is_vendor = isset($organization->is_vendor) ? boolval($organization->is_vendor) : false;
				if (isset($organization->assigned_reseller_id)) {
					$this->assigned_reseller_id = $organization->assigned_reseller_id;
				} else {
					$this->assigned_reseller_id = null; // Default to null if not set
				}
				if (!empty($organization->notes)) $this->notes = $organization->notes;
				else $this->notes = "";
				$this->password_expiration_days = $organization->password_expiration_days;
				$this->default_billing_location_id = $organization->default_billing_location_id;
				$this->default_shipping_location_id = $organization->default_shipping_location_id;
				if (!empty($organization->website_url)) $this->website_url = $organization->website_url;
				else $this->website_url = "";
				if (isset($organization->time_based_password)) $this->time_based_password = $organization->time_based_password;
				if (isset($organization->account_number)) $this->account_number = $organization->account_number;
				$this->cached(true);
				$this->exists(true);

				// In Case Cache Corrupted
				if ($organization->id) {
					app_log("Organization '".$this->name."' [".$this->id."] found in cache",'trace',__FILE__,__LINE__);
					return true;
				}
				else {
					$this->error("Organization ".$this->id." returned unpopulated cache");
				}
			}

			// Get Details for Organization
			$get_details_query = "
				SELECT	*
				FROM	register_organizations
				WHERE	id = ?
			";

			$rs = $database->Execute(
				$get_details_query,
				array($this->id)
			);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if (is_object($object)) {
				$this->id = $object->id;
				$this->name = $object->name;
				$this->code = $object->code;
				$this->status = $object->status;
                $this->password_expiration_days = $object->password_expiration_days;
                $this->default_billing_location_id = $object->default_billing_location_id;
                $this->default_shipping_location_id = $object->default_shipping_location_id;
				if (!empty($object->website_url)) $this->website_url = $object->website_url;
				else $this->website_url = "";
				$this->is_reseller = isset($object->is_reseller) ? boolval($object->is_reseller) : false;
				$this->is_customer = isset($object->is_customer) ? boolval($object->is_customer) : true;
				$this->is_vendor = isset($object->is_vendor) ? boolval($object->is_vendor) : false;
				if (!empty($object->notes)) $this->notes = $object->notes;
				else $this->notes = "";
				$this->time_based_password = isset($object->time_based_password) ? $object->time_based_password : 0;
				if (isset($object->account_number)) $this->account_number = $object->account_number;
			}
			else {
				$this->id = null;
				return false;
			}

			// Cache Customer Object
			app_log("Setting cache key ".$cache_key,'debug',__FILE__,__LINE__);
			if ($object->id) $result = $cache_item->set($object);
			app_log("Cache result: ".$result,'trace',__FILE__,__LINE__);

			return true;
		}
		
		public function members($type = 'all', $status=array()) {
			app_log("Register::Organization::members()",'trace',__FILE__,__LINE__);
			$customerlist = new CustomerList();
			
			if ($type == 'automation') $automation = true;
			elseif ($type == 'human') $automation = false;
			else $automation = null;
			return $customerlist->find(array('organization_id' => $this->id,'automation' => $automation, 'status' => $status));
		}

		public function product($product_id) {
			$product = new \Product\Item($product_id);
			if ($product->error()) {
				$this->error($product->error());
				return null;
			}
			if (! $product->id) {
				$this->error("Product not found");
				return null;
			}
			return new \Register\Organization\OwnedProduct($this->id,$product->id);
		}

		public function activeCount() {
			$customerlist = new CustomerList();
			$customers = $customerlist->find(array("organization_id" => $this->id,"status" => array('NEW','ACTIVE')));
			return count($customers);
		}

		public function activeHumans() {
			$customerlist = new CustomerList();
			$customers = $customerlist->find(array("organization_id" => $this->id,'automation' => false, "status" => array('NEW','ACTIVE')));
			return count($customers);
		}

		public function activeDevices() {
			$customerlist = new CustomerList();
			$customers = $customerlist->find(array("organization_id" => $this->id,'automation' => true, "status" => array('NEW','ACTIVE')));
			return count($customers);
		}

		public function expire() {
			$update_org_query = "
				UPDATE	register_organizations
				SET		status = 'EXPIRED'
				WHERE	id = ?
			";
			$GLOBALS['_database']->Execute(
				$update_org_query,
				array($this->id)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			
			// Bust Cache
			$cache_key = "organization[".$this->id."]";
			$cache_item = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
			$cache_item->delete();

			return true;
		}

		public function locations($parameters = array()) {
			$get_locations_query = "
				SELECT	location_id
				FROM	register_organization_locations
				WHERE	organization_id = ?";
			$rs = $GLOBALS['_database']->Execute($get_locations_query,array($this->id));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			$locations = array();
			while (list($id) = $rs->FetchRow()) {
				$location = new \Register\Location($id,$parameters);
				array_push($locations,$location);
			}
			return $locations;
		}

		public function auditRecord($type,$notes,$admin_id = null) {
			// Validate type and notes are not NULL
			if (empty($type) || $type === null) {
				$this->error("Audit type is required and cannot be NULL");
				return false;
			}
			if ($notes === null) {
				$notes = 'Value not found';
			}

			$audit = new \Register\OrganizationAuditEvent();
			if (!isset($admin_id) && isset($GLOBALS['_SESSION_']->customer->id)) $admin_id = $GLOBALS['_SESSION_']->customer->id;

			if (!isset($admin_id) || empty($admin_id)) {
				$this->error("Admin User is not set");
				return false;
			}

			if (!isset($this->id)) {
				$this->error("Organization is not set");
				return false;
			}
			
			// validate type
			if ($audit->validClass($type) == false) {
				$this->error("Invalid audit class: ".$type);
				return false;
			}

			// add record if all good
			$audit->add(array(
				'organization_id'	=> $this->id,
				'admin_id'			=> $admin_id,
				'event_date'		=> date('Y-m-d H:i:s'),
				'event_class'		=> $type,
				'event_notes'		=> $notes
			));
			if ($audit->error()) {
				$this->error($audit->error());
				return false;
			}
			return true;
		}

		public function associatedWith($organization_id) {
			// Clear any previous errors
			$this->clearError();

			// Validate organization_id
			if (empty($organization_id) || !is_numeric($organization_id)) {
				$this->error("Valid organization_id is required for associatedWith");
				return false;
			}
			$associated_organization = new \Register\Organization($organization_id);
			if ($associated_organization->error()) {
				$this->error("Error loading associated organization: ".$associated_organization->error());
				return false;
			}
			if (! $associated_organization->id) {
				$this->error("Associated organization not found");
				return false;
			}

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query
			$check_association_query = "
				SELECT	1
				FROM	register_organization_associations
				WHERE	organization_id = ?
				AND		associated_organization_id = ?
			";

			// Bind Parameters
			$database->AddParam($this->id);
			$database->AddParam($organization_id);

			// Execute Query
			$database->trace(9);
			$database->debug='screen';
			$rs = $database->Execute($check_association_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			list($association_found) = $rs->FetchRow();
			if ($association_found) return true;
			else return false;
		}
	}