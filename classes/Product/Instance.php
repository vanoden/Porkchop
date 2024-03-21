<?php
	namespace Product;

	class Instance extends \BaseModel {
	
		public $id;
		public $code;
		public $name;
		public $product_id;
		public $organization;
		public $organization_id;
        public $asset_code;
		private $_flat = false;

		public function __construct($id = 0,$flat = false) {
			$this->_tableName = "monitor_assets";
			$this->_tableIDColumn = "asset_id";
			$this->_tableUKColumn = null;
            $this->_aliasField("asset_code","code");
			$this->_flat = $flat;
    		parent::__construct($id);
		}

		public function __call($name,$parameters) {
			if ($name == 'get' && count($parameters) == 2) return $this->getWithProduct($parameters[0],$parameters[1]);
			elseif ($name == 'get') return $this->getSimple($parameters[0]);
			else {
				$this->error("Invalid method called");
				return null;
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
				return null;
			}

			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
		}
		
		# Get Specific Hub
		public function getWithProduct($code,$product_id) {

			$this->clearError();

			$database = new \Database\Service();

			$product = new \Product\Item($product_id);
			if (!$product->exists()) {
				$this->error("Product Not Found");
				return false;
			}

			$get_object_query = "
				SELECT	asset_id
				FROM	monitor_assets
				WHERE	asset_code = ?
				AND		product_id = ?
			";
			$database->AddParam($code);
			$database->AddParam($product_id);

			$rs = $database->Execute($get_object_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			list($id) = $rs->FetchRow();
			if (! $id) {
				$this->error("Product Instance not found");
				return false;
			}
			$this->id = $id;
			return $this->details();
		}

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

				return $this->details();
			}
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
