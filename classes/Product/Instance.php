<?php
	namespace Product;

	class Instance extends \BaseModel {
		public $code;
		public $name;
		public $product_id;
		public $organization;
		public $organization_id;
        public $asset_code;

		/**
		 * Constructor
		 * @param int $id
		 * @param bool $flat
		 */
		public function __construct($id = 0) {
			$this->_tableName = "monitor_assets";
			$this->_tableIDColumn = "asset_id";
			$this->_tableUKColumn = null;
            $this->_aliasField("asset_code","code");
			$this->_auditEvents = true;
    		parent::__construct($id);
		}

		/**
		 * Polymorphic wrapper of the get method allowing
		 * request with or without a specified product id
		 * @param mixed $name
		 * @param mixed $parameters
		 * @return bool
		 */
		public function __call($name,$parameters) {
			if ($name == 'get' && count($parameters) == 2) return $this->getWithProduct($parameters[0],$parameters[1]);
			elseif ($name == 'get') return $this->getSimple($parameters[0]);
			else {
				$this->error("Invalid method called");
				return false;
			}
		}

		public function add($parameters = []) {

			$this->clearError();

			# See If Existing Unit Present
			$exists = new \Product\Instance();
			if ($exists->getWithProduct($parameters["code"],$parameters['product_id'])) {
				$this->error("Product with code ".$parameters['code']." already exists");
				return false;
			}

			# Prepare Query to Add Device
			$add_object_query = "
				INSERT
				    INTO	monitor_assets
				    (		asset_code,
						    product_id,
						    organization_id,
							company_id
				    )
				VALUES
				(	?,?,?,?)
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$parameters['code'],
					$parameters['product_id'],
					$parameters['organization_id'],
					$GLOBALS['_SESSION_']->company->id
				)
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

			# Record Event
			$event = new \Action\Event();
			$event->addEvent(
				"MonitorAsset",
				[	"code"  => $parameters["code"],
					"timestamp" => date("Y-m-d H:i:s"),
					"user"  => $GLOBALS['_SESSION_']->customer->code,
					"description"   => "MonitorAsset Created",
				]
			);
			
			if ($event->error()) app_log("Failed to add change to history: ".$event->error(),'error',__FILE__,__LINE__);
			return $this->update($parameters);
		}

		/**
		 * Get a product instance by code w/o product id
		 * If multiple instances have the same product id, the first
		 * one found will be returned
		 * @param mixed $code 
		 * @return bool 
		 */
		public function getSimple($code) {
		
			$this->clearError();
			$database = new \Database\Service();

			$get_object_query = "
				SELECT	asset_id
				FROM	monitor_assets
				WHERE	asset_code = ?
			";
			$database->AddParam($code);

			$rs = $database->Execute($get_object_query);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}

			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
		}
		
		/**
		 * Get specific product instance by code and product id
		 * @param string code
		 * @param int product_id
		 * @return bool
		 */
		public function getWithProduct($code,$product_id) {
			$this->clearError();
			$database = new \Database\Service();

			// Validate the Instance Code (Serial Number)
			if (! $this->validCode($code)) {
				$this->error("Invalid code");
				return false;
			}

			// Get the product
			$product = new \Product\Item($product_id);
			if (!$product->exists()) {
				$this->error("Product Not Found");
				return false;
			}

			// Prepare Query
			$get_object_query = "
				SELECT	asset_id
				FROM	monitor_assets
				WHERE	asset_code = ?
				AND		product_id = ?
			";
			// Add Parameters and Execute Query
			$database->AddParam($code);
			$database->AddParam($product->id);
			$rs = $database->Execute($get_object_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// Fetch the id from the result set
			list($id) = $rs->FetchRow();
			if (! $id) {
				$this->error("Product Instance '$code' of Product '".$product->code."' not found");
				return false;
			}

			// Store the current id
			$this->id = $id;

			// Call and return the results of the details() method
			return $this->details();
		}

		/**
		 * Update a product instance properties
		 * @param array $parameters
		 * @return bool
		 */
		public function update($parameters = []): bool {
			$this->clearError();
			$database = new \Database\Service();

			if (! is_numeric($this->id)) {
				$this->error("Valid asset id required for update");
				return false;
			}

			# Get Current Details
			if (! $this->id) {
				$this->error("No matching asset to update");
				return false;
			}

			# Update Object Query
			$update_object_query = "
				UPDATE	monitor_assets
				SET		asset_id = asset_id
			";

            foreach ($this->_aliasFields as $alias => $real) {
                if (isset($parameters[$alias])) {
                    $parameters[$real] = $parameters[$alias];
                    unset($parameters[$alias]);
                }
            }

			if (isset($parameters['code']) && $this->validCode($parameters['code'])) {
				$update_object_query .= ",
						asset_code = ?";
				$database->AddParam($parameters['code']);
			}
			if (isset($parameters['name'])) {
				$update_object_query .= ",
						asset_name = ?";
				$database->AddParam($parameters['name']);
			}
			if (is_numeric($parameters['product_id'])) {
				$update_object_query .= ",
						product_id = ?";
				$database->AddParam($parameters['product_id']);
			}
			if (is_numeric($parameters['organization_id'])) {
				if ($GLOBALS['_SESSION_']->customer->can('manage product instances')) {
					$update_object_query .= ",
						organization_id = ?";
					$database->AddParam($parameters['organization_id']);
				} else {
					$this->error("Insufficient privileges for update");
					return false;
				}
			}

			$update_object_query .= "
				WHERE	asset_id = ?
			";
			$database->AddParam($this->id);

			$database->Execute($update_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			else {
				# Get Some Event Info
				if (isset($parameters['product_id'])) $product = new \Product\Item($parameters['product_id']);
				else $product = new \Product\Item();
				if (isset($parameters['organization_id'])) $organization = new \Register\Organization($parameters['organization_id']);
				else $organization = new \Register\Organization();

				if (isset($GLOBALS['_config']->action)) {
				
					# Record Event
					$event = new \Action\Event();
					$event->addEvent(
						"MonitorAsset",
						[	"code"  => $parameters["code"],
							"timestamp" => date("Y-m-d H:i:s"),
							"user"  => $GLOBALS['_SESSION_']->customer->code,
							"description"   => "MonitorAsset Updated",
							"product"	=> $product->code,
							"organization"	=> $organization->code,
						]
					);
					if ($event->error()) app_log("Failed to add change to history: ".$event->error(),'error',__FILE__,__LINE__);
				}

                $cache = $this->cache();
                if (isset($cache)) $cache->delete();

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

		/**
		 * Special product update function just to change the code of a product
		 * instance.  The regular update method should not allow this as special
		 * privileges and auditing are required.
		 * @param mixed $new_code 
		 * @param mixed $reason 
		 * @return bool 
		 */
		public function changeCode($new_code, $reason): bool {
			$this->clearError();
			$database = new \Database\Service();

			app_log("Changing product code from ".$this->code." to ".$new_code,'notice',__FILE__,__LINE__);

			// Check the users authorization - Should really be done in the interface
			if (! $GLOBALS['_SESSION_']->customer->can('manage products')) {
				$this->error("You do not have permissions for this task.");
				app_log($GLOBALS['_SESSION_']->customer->login." failed to update products because not product manager role",'notice',__FILE__,__LINE__);
				app_log(print_r($GLOBALS['_SESSION_'],true),'debug',__FILE__,__LINE__);
				return false;
			}

			// Validate the new code
			if (! $this->validCode($new_code)) {
				$this->error("Invalid code");
				return false;
			}

			// Bust the existing cache
			$cache_key = "product.instance[".$this->id."]";
			$cache_item = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
			$cache_item->delete();

			// Prepare the query
			$update_product_query = "
				UPDATE	monitor_assets
				SET		asset_code = ?
				WHERE	asset_id = ?";

			// Add Parameters and Execute Query
			$database->AddParam($new_code);
			$database->AddParam($this->id);
			$database->Execute($update_product_query);

			// Check for errors
			if ($database->ErrorMsg()) {
				$this->error($database->ErrorMsg());
				return false;
			}
			
			// audit the update event
			app_log("Logging event for product code change",'debug');
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Changed code from '.$this->code.' to '.$new_code,
				'class_name' => get_class($this),
				'class_method' => __FUNCTION__
			));

			return true;
		}

		public function transfer($org_id,$reason) {
			if ($this->update(array('organization_id' => $org_id))) {
				app_log("Transfered ".$this->code." to $org_id",'notice');
				return true;
			}
			else return false;
		}

		public function organization() {
			return new \Register\Organization($this->organization_id);
		}

		public function track() {
			$this->clearError();
		}
	
		public function setMetadata($key,$value) {    
			app_log("Setting metadata '$key' to '$value' for '".$this->code."'",'debug',__FILE__,__LINE__);
			$set_object_query = "
				INSERT
				INTO	monitor_asset_metadata
				(asset_id,`key`,value)
				VALUES	(?,?,?)
				ON DUPLICATE KEY UPDATE
				VALUE = ?
			";
			$GLOBALS['_database']->Execute(
				$set_object_query,
				array($this->id,$key,$value,$value)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			return 1;
		}
		
		public function deleteMetadata($key) {
			
			app_log("Removing metadata '$key' for '".$this->code."'",'debug',__FILE__,__LINE__);
			$set_object_query = "
				DELETE
				FROM	monitor_asset_metadata
				WHERE `asset_id` = ? AND `key` = ?
			";
			$GLOBALS['_database']->Execute(
				$set_object_query,
				array($this->id,$key)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}

			// audit the delete event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Deleted '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'deleteMetadata'
			));	

			return 1;
		}
		
		public function allMetadata() {
			$get_object_query = "
				SELECT	`key`,value
				FROM	monitor_asset_metadata
				WHERE	asset_id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($this->id)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			$array = array();
			while (list($label,$value) = $rs->FetchRow()) {
				$array[$label] = $value;
			}
			return $array;
		}
		
		public function getAllMetadata() {
			$get_value_query = "
				SELECT	value
				FROM	monitor_asset_metadata
				WHERE	asset_id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_value_query,
				array($this->id)
			);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			list($value) = $rs->FetchRow();
			return $value;
		}		
		
		public function getMetadata($key) {
			$get_value_query = "
				SELECT	value
				FROM	monitor_asset_metadata
				WHERE	asset_id = ?
				AND		`key` = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_value_query,
				array($this->id,$key)
			);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			list($value) = $rs->FetchRow();
			return $value;
		}

		public function getTickets($parameters = array()) {
			$ticketList = new \Support\Request\ItemList();
			$parameters['product_code']	= $this->product()->code;
			$parameters['serial_number'] = $this->code;
			return $ticketList->find($parameters);
		}

		public function lastTicket($parameters = array()) {
			$ticketList = new \Support\Request\ItemList();
			$parameters['product_code']	= $this->product()->code;
			$parameters['serial_number'] = $this->code;
			return $ticketList->last($parameters);
		}

		public function product() {
			return new \Product\Item($this->product_id);
		}
	}
