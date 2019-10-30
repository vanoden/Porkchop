<?php
	namespace Shipping;
	
	class Package {

    	private $_error;
		public $id;
		public $shipment;
		public $number;
		public $tracking_code;
		public $status;
		public $condition;
		public $height;
		public $width;
		public $depth;
		public $weight;
		public $shipping_cost;
		public $date_received;
		public $user_received_id;

		public function __construct($id = 0) {
			if (is_numeric($id) && $id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters = array()) {
			$bind_params = array();
			if ($parameters['shipment_id']) {
				$shipment = new \Shipping\Shipment($parameters['shipment_id']);
				if (! $shipment->id) {
					$this->_error = "Shipment Not Found";
					return false;
				}
				else array_push($bind_params,$shipment->id);
			}
			else {
				$this->_error = "Shipment ID Required";
				return false;
			}
			$number = $this->get_next_number($shipment->id);
			if (! isset($number)) {
				return false;
			}
			array_push($bind_params,$number);

			$add_object_query = "
				INSERT
				INTO	shipping_packages
				(		shipment_id,
						`number`
				)
				VALUES (
						?,?
				)
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
				UPDATE	shipping_packages
				SET		id = id
			";

			if (isset($parameters['tracking_code'])) {
				$update_object_query .= ",
						tracking_code = ?";
				array_push($bind_params,$update_object_query);
			}
			if (isset($parameters['status'])) {
				$update_object_query .= ",
						status = ?";
				array_push($bind_params,$parameters['status']);
			}
			if (isset($parameters['condition'])) {
				$update_object_query .= ",
						condition = ?";
				array_push($bind_params,$parameters['condition']);
			}
			if (isset($parameters['height'])) {
				$update_object_query .= ",
						height = ?";
				array_push($bind_params,$parameters['height']);
			}
			if (isset($parameters['width'])) {
				$update_object_query .= ",
						width = ?";
				array_push($bind_params,$parameters['width']);
			}
			if (isset($parameters['depth'])) {
				$update_object_query .= ",
						depth = ?";
				array_push($bind_params,$parameters['depth']);
			}
			if (isset($parameters['weight'])) {
				$update_object_query .= ",
						weight = ?";
				array_push($bind_params,$parameters['weight']);
			}

			if (isset($parameters['user_received']) && is_numeric($parameters['user_received'])) {
				$customer = new \Register\Customer($parameters['user_received']);
				if (! $customer->id) {
					$this->_error = "Customer not found";
					return false;
				}
				$update_object_query .= ",
						user_received_id = ?";
				array_push($bind_params,$customer->id);
			}
			elseif (get_mysql_query($parameters['date_received'])) {
				$update_object_query .= "
						date_received = ?
				";
				array_push($bind_params,get_mysql_date($parameters['date_received']));
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
				FROM	shipping_packages
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
				FROM	shipping_packages
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_object_query,array($this->id));
			if (! $rs) {
				$this->_error = "SQL Error in Shipping::Vendor::details() ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if (is_numeric($object->id)) {
				$this->id = $object->id;
				$this->shipment_id = $object->id;
				$this->number = $object->id;
				$this->tracking_code = $object->tracking_code;
				$this->status = $object->status;
				$this->condition = $object->condition;
				$this->height = $object->height;
				$this->weight = $object->weight;
				$this->width = $object->width;
				$this->depth = $object->depth;
				$this->date_received = $object->date_received;
				$this->user_received_id = $object->user_received_id;
			}
			return true;
		}

		public function user_received() {
			return new \Register\Customer($this->user_received_id);
		}

		public function shipment() {
			return new \Shipping\Shipment($this->shipment_id);
		}

		private function get_next_number($shipment_id) {
			$get_next_query = "
				SELECT	max(`number`)
				FROM	shipping_packages
				WHERE	shipment_id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_next_query,array($shipment_id));
			if (! $rs) {
				$this->_error = "SQL Error in Shipping::Package::get_next_number(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($number) = $rs->FetchRow();
			if (is_numeric($number)) return $number + 1;
			else return 1;
		}

		public function error() {
			return $this->_error;
		}
	}
