<?php
	namespace Shipping;
	
	class Shipment {
		private $_error;
		public $id;
		public $code;
		private $vendor_id;
		public $date_entered;
		public $date_received;
		
		public function __construct($id = 0) {
			if (is_numeric($id) && $id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters = array()) {
			$bind_params = array();

			if (! isset($parameters['code'])) $parameters['code'] = uniqid();
			if (! isset($parameters['status'])) $parameters['status'] = 'NEW';
			if (! isset($parameters['send_contact_id'])) {
				$this->_error = "Sending contact required";
				return false;
			}
			if (! isset($parameters['send_location_id'])) {
				$this->_error = "Sending location required";
				return false;
			}
			if (! isset($parameters['rec_contact_id'])) {
				$this->_error = "Receiving contact required";
				return false;
			}
			if (! isset($parameters['rec_location_id'])) {
				$this->_error = "Receiving location required";
				return false;
			}

			$add_object_query = "
				INSERT
				INTO	shipping_shipments
				(		code,date_entered,status,send_contact_id,send_location_id,rec_contact_id,rec_location_id)
				VALUES
				(		?,sysdate,?,?,?,?,?)
			";

			$GLOBALS["_database"]->Execute($add_object_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Shipping::Vendor::add() ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}

			$this->id = $GLOBAL['_database']->Insert_ID();
			return $this->update($parameters);
		}

		public function update($parameters = array()) {
			$bind_params = array();
			$update_object_query = "
				UPDATE	shipping_shipments
				SET		id = id
			";

			if (isset($parameters['status'])) {
				$update_object_query .= ",
						status = ?";
				array_push($bind_params,$parameters['status']);
			}
			if(isset($parameters['type']) && isset($parameters['number'])) {
				$parameters['document_number'] = sprintf("%s-%06d",$parameters['type'],$parameters['number']);
			}
			if (isset($parameters['document_number'])) {
				$update_object_query .= ",
						document_number = ?";
				array_push($bind_params,$parameters['document_number']);
			}
			if (get_mysql_date($parameters['date_shipped'])) {
				$update_object_query .= ",
						date_shipped = ?";
				array_push($bind_params,get_mysql_date($parameters['date_shipped']));
			}
			if (isset($parameters['vendor_id'])) {
				$update_object_query .= ",
						vendor_id = ?";
				array_push($bind_params,$parameters['vendor_id']);
			}

			$update_object_query .= "
				WHERE	id = ?";
			array_push($bind_params,$this->id);

			$GLOBALS["_database"]->Execute($update_object_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Shipping::Vendor::update() ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}

			return $this->details();
		}

		public function get($code) {
			$get_object_query = "
				SELECT	id
				FROM	shipping_shipments
				WHERE	code = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_object_query,array($code));
			if (! $rs) {
				$this->_error = "SQL Error in Shipping::Vendor::get() ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($this->id) = $rs->FetchRow();
			return $this->details();
		}

		public function details() {
			$get_object_query = "
				SELECT	*
				FROM	shipping_shipments
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_object_query,array($this->id));
			if (! $rs) {
				$this->_error = "SQL Error in Shipping::Vendor::details() ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if ($object->id) {
				$this->id = $object->id;
				$this->document_number = $object->document_number;
				$this->date_entered = $object->date_entered;
				$this->date_shipped = $object->date_shipped;
				$this->status = $object->status;
				$this->send_contact_id = $object->send_contact_id;
				$this->send_location_id = $object->send_location_id;
				$this->rec_contact_id = $object->rec_contact_id;
				$this->rec_location_id = $object->rec_location_id;
				$this->vendor_id = $object->vendor_id;
			}
			return true;
		}

		public function vendor() {
			return new \Shipping\Vendor($this->vendor_id);
		}

		public function send_contact() {
			return new \Register\Customer($this->send_contact_id);
		}

		public function send_location() {
			return new \Register\Location($this->send_location_id);
		}

		public function rec_contact() {
			return new \Register\Customer($this->rec_contact_id);
		}

		public function rec_location() {
			return new \Register\Location($this->rec_location_id);
		}

		public function add_package($parameters) {
			$parameters['shipment_id'] = $this->id;
			$package = new \Shipping\Package();
			if ($package->add($parameters)) {
				return $package;
			}
			else {
				$this->_error = "Error adding package: ".$package->error();
				return null;
			}
		}

		public function add_item($parameters) {
			$parameters['shipment_id'] = $this->id;
			$item = new \Shipping\Item();
			if ($item->add($parameters)) {
				return $item;
			}
			else {
				$this->_error = "Error adding item: ".$item->error();
				return null;
			}
		}

		public function error() {
			return $this->_error;
		}
	}
