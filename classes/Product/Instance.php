<?php
	namespace Product;

	class Instance extends Item {
		public $id;
		public $error;
		public $errno;
		public $code;
		public $name;
		public $product;
		public $serial_number;
		public $organization;
		public $organization_id;
		private $_flat = false;

		public function __construct($id = 0,$flat = false) {
			$this->_flat = $flat;
			if (is_numeric($id) && $id > 0) {
				$this->id = $id;
				$this->details();
			}
		}
		
		public function add($parameters) {
			$this->error = null;

			# See If Existing Unit Present
			$exists = $this->get($parameters["code"],$parameters['product_id']);
			if ($this->error) return null;
			if ($exists->id) {
				$this->error = "Asset with code ".$parameters['code']." already exists";
				return null;
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
				$this->error = "SQL Error adding asset: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();

			# Record Event
			$event = new \Action\Event();
			$event->add(
				"MonitorAsset",
				[	"code"  => $parameters["code"],
					"timestamp" => date("Y-m-d H:i:s"),
					"user"  => $GLOBALS['_SESSION_']->customer->code,
					"description"   => "MonitorAsset Created",
				]
			);
			
			if ($event->error) app_log("Failed to add change to history: ".$event->error,'error',__FILE__,__LINE__);
			return $this->update($parameters);
		}
		
		public function getSimple($code) {
			$this->error = null;

			$bind_params = array();

			$get_object_query = "
				SELECT	asset_id
				FROM	monitor_assets
				WHERE	asset_code = ?
			";
			array_push($bind_params,$code);

			if (! $GLOBALS['_SESSION_']->customer->can('manage product instances')) {
				$get_object_query .= " AND organization_id = ?";
				array_push($bind_params,$GLOBALS['_SESSION_']->customer->organization->id);
			}

			$rs = $GLOBALS['_database']->Execute($get_object_query,$bind_params);
			if (! $rs) {
				$this->error = "SQL Error in Monitor::Asset::getSimple():" .$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
		}
		
		# Get Specific Hub
		public function get($code,$product_id) {
			$this->error = null;
		
			$bind_params = array();

			$get_object_query = "
				SELECT	asset_id
				FROM	monitor_assets
				WHERE	asset_code = ?
				AND		product_id = ?
			";
			array_push($bind_params,$code,$product_id);

			if (! $GLOBALS['_SESSION_']->customer->can('manage product instances')) {
				$get_object_query .= "
				AND	organization_id = ?";
				array_push($bind_params,$GLOBALS['_SESSION_']->customer->organization->id);
			}

			$rs = $GLOBALS['_database']->Execute($get_object_query,$bind_params);
			if (! $rs) {
				$this->error = "SQL Error in Product::Instance::get():" .$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
		}

		public function update($parameters = array()) {
			$this->error = null;
			if (! preg_match('/^\d+$/',$this->id)) {
				$this->error = "Valid asset id required for update";
				return null;
			}

			$bind_params = array();

			# Get Current Details
			$current_object = $this->details();
			if (! $current_object->id) {
				$this->error = "No matching asset to update";
				return null;
			}

			# Update Object Query
			$update_object_query = "
				UPDATE	monitor_assets
				SET		asset_id = asset_id
			";

			if (isset($parameters['code']) && preg_match('/^[\w\-\.\_]+$/',$parameters['code'])) {
				$update_object_query .= ",
						asset_code = ?";
				array_push($bind_params,$parameters['code']);
			}
			if (isset($parameters['name'])) {
				$update_object_query .= ",
						asset_name = ?";
				array_push($bind_params,$parameters['name']);
			}
			if (isset($parameters['product_id']) && preg_match('/^\d+$/',$parameters['product_id'])) {
				$update_object_query .= ",
						product_id = ?";
				array_push($bind_params,$parameters['product_id']);
			}
			if (isset($parameters['organization_id']) && preg_match('/^\d+$/',$parameters['organization_id'])) {
				if ($GLOBALS['_SESSION_']->customer->can('manage product instances')) {
					$update_object_query .= ",
						organization_id = ?";
					array_push($bind_params,$parameters['organization_id']);
				} else {
					$this->error = "Insufficient privileges for update";
					return null;
				}
			}

			$update_object_query .= "
				WHERE	asset_id = ?
			";
			array_push($bind_params,$this->id);

			$GLOBALS['_database']->Execute($update_object_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Product::Instance::update(): ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			} else {
				# Get Some Event Info
				if (isset($parameters['product_id'])) $product = new \Product\Item($parameters['product_id']);
				else $product = new \Product\Item();
				if (isset($parameters['organization_id'])) $organization = new \Register\Organization($parameters['organization_id']);
				else $organization = new \Register\Organization();

				if (isset($GLOBALS['_config']->action)) {
				
					# Record Event
					$event = new \Action\Event();
					$event->add(
						"MonitorAsset",
						[	"code"  => $parameters["code"],
							"timestamp" => date("Y-m-d H:i:s"),
							"user"  => $GLOBALS['_SESSION_']->customer->code,
							"description"   => "MonitorAsset Updated",
							"product"	=> $product->code,
							"organization"	=> $organization->code,
						]
					);
					if ($event->error) app_log("Failed to add change to history: ".$event->error,'error',__FILE__,__LINE__);
				}
				return $this->details();
			}
		}

		public function transfer($org_id,$reason) {
			if ($this->update(array('organization_id' => $org_id))) {
				app_log("Transfered ".$this->serial_number." to $org_id",'notice');
				return true;
			}
			else return false;
		}
		
		public function details() {
			$this->error = null;
			$get_object_query = "
				SELECT	asset_id id,
						asset_code code,
						asset_name name,
						organization_id,
						product_id
				FROM	monitor_assets
				WHERE	asset_id = ?
			";

			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($this->id)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Product::Instance::details(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			} else {
				$object = $rs->FetchNextObject(false);
				if (isset($object)) {
					$this->id = $object->id;
					$this->code = $object->code;
					$this->name = $object->name;
					$this->organization_id = $object->organization_id;
					$this->product_id = $object->product_id;

					if (! $this->_flat) {
						$this->organization = new \Register\Organization($object->organization_id);
						$this->product = new \Product\Item($object->product_id);
					}

					return $object;
				}
			}
		}
		
		public function track() {
			$this->error = null;
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
				$this->error = "SQL Error in Product::Instance::setMetadata: ".$GLOBALS['_database']->ErrorMsg();
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
				$this->error = "SQL Error in Product::Instance::deleteMetadata: ".$GLOBALS['_database']->ErrorMsg();
			    var_dump($this->error);
				return null;
			}
			return 1;
		}
		
		
		public function getMetadata() {
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
				$this->error = "SQL Error in Product::Instance::getMetadata: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$array = array();
			while (list($label,$value) = $rs->FetchRow()) {
				$array[$label] = $value;
			}
			return $array;
		}
		
		public function metadata($key) {
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
				$this->error = "SQL Error in Product::Instance::metadata(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($value) = $rs->FetchRow();
			return $value;
		}

		public function getTickets($parameters = array()) {
			$ticketList = new \Support\Request\ItemList();
			$parameters['product_code']	= $this->product->code;
			$parameters['serial_number'] = $this->code;
			return $ticketList->find($parameters);
		}

		public function lastTicket($parameters = array()) {
			$ticketList = new \Support\Request\ItemList();
			$parameters['product_code']	= $this->product->code;
			$parameters['serial_number'] = $this->code;
			return $ticketList->last($parameters);
		}
		
		public function error() {
			return $this->error;
		}
	}
