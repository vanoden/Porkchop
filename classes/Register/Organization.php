<?php
	namespace Register;

	class Organization {
		public $error;
		public $name;
		public $code;
		public $status;
		public $id;
		public $is_reseller;
		public $reseller;
		public $notes;
		public $_cached;
        public $password_expiration_days;
		private $_nocache = false;

		public function __construct($id = 0,$options = array()) {
		
			// Clear Error Info
			$this->error = '';

			if (isset($options['nocache']) && $options['nocache']) {
				$this->_nocache = true;
			}

			if (isset($id) && is_numeric($id)) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters) {
			app_log("Register::Organization::add()",'trace',__FILE__,__LINE__);
			if (empty($parameters['code'])) $parameters['code'] = uniqid();
			$this->error = null;
			$add_object_query = "
				INSERT
				INTO	register_organizations
				(		id,code,name,date_created)
				VALUES
				(		null,?,?,sysdate())
			";
			$rs = $GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$parameters['code'],
					$parameters['name']
				)
			);
			if (! $rs) {			
				$this->error = "SQL Error in RegisterOrganization::add(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}

		public function update($parameters = array()) {
			app_log("Register::Organization::update()",'trace',__FILE__,__LINE__);
			$this->error = null;

			$bind_params = array();

			// Bust Cache
			$cache_key = "organization[".$this->id."]";
			$cache_item = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
			$cache_item->delete();

			$update_object_query = "
				UPDATE	register_organizations
				SET		id = id
			";

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
			if (isset($parameters['is_reseller']) && is_numeric($parameters['is_reseller'])) {
				$update_object_query .= ",
						is_reseller = ?";
				array_push($bind_params,$parameters['is_reseller']);
			}
			if (isset($parameters['assigned_reseller_id']) && is_numeric($parameters['assigned_reseller_id'])) {
				$update_object_query .= ",
						assigned_reseller_id = ?";
				array_push($bind_params,$parameters['assigned_reseller_id']);
			}
			if (isset($parameters['notes'])) {
				$update_object_query .= ",
						notes = ?";
				array_push($bind_params,$parameters['notes']);
			}
			if (isset($parameters['password_expiration_days'])) {
				$update_object_query .= ",
						password_expiration_days = ?";
                array_push($bind_params,$parameters['password_expiration_days']);
            }
			$update_object_query .= "
				WHERE	id = ?
			";
			array_push($bind_params,$this->id);
			query_log($update_object_query);
			$rs = $GLOBALS['_database']->Execute(
				$update_object_query,
				$bind_params
			);
			if (! $rs) {
				$this->error = "SQL Error in Register::Organization::update(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $this->details();
		}
		
		public function get($code) {
			app_log("Register::Organization::get($code)",'trace',__FILE__,__LINE__);
			$this->error = null;
			$get_object_query = "
				SELECT	id
				FROM	register_organizations
				WHERE	code = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($code)
			);
			if (! $rs) {
				$this->error = "SQL Error in Register::Organization::get(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
		}
		
		public function details() {
			app_log("Register::Organization::details()[".$this->id."]",'trace',__FILE__,__LINE__);
			$this->error = null;

			$cache_key = "organization[".$this->id."]";
			$cache_item = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
			
			// Cached Organization Object, Yay!
			if ((! $this->_nocache) and ($this->id) and ($organization = $cache_item->get())) {
				$organization->_cached = 1;
				$this->id = $organization->id;
				$this->name = $organization->name;
				$this->code = $organization->code;
				$this->status = $organization->status;
				if ($organization->is_reseller) $this->is_reseller = true;
				if (isset($this->assigned_reseller_id)) $this->reseller = new Organization($this->assigned_reseller_id);
				$this->notes = $organization->notes;
				$this->password_expiration_days = $organization->password_expiration_days;
				$this->_cached = $organization->_cached;

				// In Case Cache Corrupted
				if ($organization->id) {
					app_log("Organization '".$this->name."' [".$this->id."] found in cache",'trace',__FILE__,__LINE__);
					return $organization;
				}
				else {
					$this->error = "Organization ".$this->id." returned unpopulated cache";
				}
			}

			// Get Details for Organization
			$get_details_query = "
				SELECT	id,
						code,
						name,
						status,
						is_reseller,
						assigned_reseller_id,
						notes,
						password_expiration_days
				FROM	register_organizations
				WHERE	id = ?
			";
			query_log($get_details_query);
			$rs = $GLOBALS['_database']->Execute(
				$get_details_query,
				array($this->id)
			);
			if (! $rs) {
				$this->error = "SQL Error in Register::Organization::details(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if (is_object($object)) {
				$this->id = $object->id;
				$this->name = $object->name;
				$this->code = $object->code;
				$this->status = $object->status;
                $this->password_expiration_days = $object->password_expiration_days;
				if ($object->is_reseller) $this->is_reseller = true;
				if ($object->assigned_reseller_id) $this->reseller = new Organization($object->assigned_reseller_id);
				$this->notes = $object->notes;
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
		public function members($type = 'all') {
			app_log("Register::Organization::members()",'trace',__FILE__,__LINE__);
			$customerlist = new CustomerList();
			if ($type == 'automation') $automation = true;
			elseif ($type == 'human') $automation = false;
			else $automation = null;
			return $customerlist->find(array('organization_id' => $this->id,'automation' => $automation));
		}
		public function product($product_id) {
			$product = new \Product\Item($product_id);
			if ($product->error()) {
				$this->error = $product->error();
				return null;
			}
			if (! $product->id) {
				$this->error = "Product not found";
				return null;
			}
			return new \Register\Organization\OwnedProduct($this->id,$product->id);
		}

		public function activeCount() {
			$customerlist = new CustomerList();
			$customers = $customerlist->find(array("organization_id" => $this->id,"status" => array('NEW','ACTIVE')));
			return count($customers);
		}

		public function exists() {
			if (isset($this->id) && is_numeric($this->id) && $this->id > 0) return true;
			return false;
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
				$this->error = "SQL Error in Register::Organization::expire(): ".$GLOBALS['_database']->ErrorMsg();
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
				$this->error = "SQL Error in Register::Organization::locations(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$locations = array();
			while (list($id) = $rs->FetchRow()) {
				$location = new \Register\Location($id,$parameters);
				array_push($locations,$location);
			}
			return $locations;
		}
		public function error() {
			return $this->error;
		}
    }
