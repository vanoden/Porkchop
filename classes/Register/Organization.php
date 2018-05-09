<?php
	namespace Register;

	class Organization {
		public $error;
		public $name;
		public $code;
		public $status;
		public $id;
		
		public function __construct($id = 0) {
			# Clear Error Info
			$this->error = '';

			# Database Initialization
			$schema = new Schema();
			if ($schema->error) {
				$this->error = "Failed to initialize schema: ".$schema->error;
			}
			elseif ($id) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters) {
			app_log("Register::Organization::add()",'trace',__FILE__,__LINE__);
			$this->error = null;
			$add_object_query = "
				INSERT
				INTO	register_organizations
				(		id,code)
				VALUES
				(		null,?)
			";

			$rs = $GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$parameters['code']
				)
			);
			if (! $rs) {
				$this->error = "SQL Error in \Register\Organization::add: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}

		public function update($parameters = array()) {
			app_log("Register::Organization::update()",'trace',__FILE__,__LINE__);
			$this->error = null;
			
			# Bust Cache
			$cache_key = "organization[".$this->id."]";
			cache_unset($cache_key);

			$update_object_query = "
				UPDATE	register_organizations
				SET		id = id
			";

			if (isset($parameters['name']))
				$update_object_query .= ",
						name = ".$GLOBALS['_database']->qstr($parameters['name'],get_magic_quotes_gpc());

			if (isset($parameters['status']))
				$update_object_query .= ",
						status = ".$GLOBALS['_database']->qstr($parameters['status'],get_magic_quotes_gpc());

			$update_object_query .= "
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$update_object_query,
				array($this->id)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in \Register\Organization::update: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $this->details();
		}
		public function get($code = '') {
			app_log("Register::Organization::get()",'trace',__FILE__,__LINE__);
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
				$this->error = "SQL Error in RegisterOrganization::get: ".$GLOBALS['_database']->ErrorMsg();
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

			# Cached Organization Object, Yay!
			if (($this->id) and ($organization = cache_get($cache_key))) {
				$organization->_cached = 1;
				$this->id = $organization->id;
				$this->name = $organization->name;
				$this->code = $organization->code;
				$this->status = $organization->status;
				$this->_cached = $organization->_cached;

				# In Case Cache Corrupted
				if ($organization->id) {
					app_log("Organization '".$this->name."' [".$this->id."] found in cache",'debug',__FILE__,__LINE__);
					return $organization;
				}
				else {
					$this->error = "Organization ".$this->id." returned unpopulated cache";
				}
			}

			# Get Details for Organization
			$get_details_query = "
				SELECT	id,
						code,
						name,
						status
				FROM	register_organizations
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_details_query,
				array($this->id)
			);
			if (! $rs) {
				$this->error = "SQL Error in register::Organization::details: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$object = $rs->FetchNextObject(false);
			if (is_object($object)) {
				$this->id = $object->id;
				$this->name = $object->name;
				$this->code = $object->code;
				$this->status = $object->status;
			}
			else {
				return new \stdClass();
			}

			# Cache Customer Object
			app_log("Setting cache key ".$cache_key);
			if ($object->id) $result = cache_set($cache_key,$object);
			app_log("Cache result: ".$result);

			return $object;
		}
		public function members() {
			app_log("Register::Organization::members()",'trace',__FILE__,__LINE__);
			$customerlist = new CustomerList();
			#print "Finding members of org $id<br>\n";
			return $customerlist->find(array('organization_id' => $this->id));
		}
		public function product($product_id) {
			$product = new \Product\Item($product_id);
			if ($product->error) {
				$this->error = $product->error;
				return null;
			}
			if (! $product->id) {
				$this->error = "Product not found";
				return null;
			}
			return new \Register\Organization\OwnedProduct($this->id,$product->id);
		}
    }

?>