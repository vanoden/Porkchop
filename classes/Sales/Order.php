<?php
	namespace Sales;

	class Order extends \ORM\BaseModel {
	
		public $id;
		public $customer_id;
		public $salesperson_id;
		public $status;

		public function add($parameters) {
			$customer = new \Register\Customer($parameters['customer_id']);
			if (! $customer->id) {
				$this->_error = "Customer not found";
				return false;
			}
			$salesperson = new \Register\Admin($parameters['salesperson_id']);
			if (! $salesperson->id) {
				$this->_error = "Salesperson not found";
				return false;
			}
			if ($parameters['status']) $status = $parameters['status'];
			else $status = 'NEW';
			if ($parameters['code']) $code = $parameters['code'];
			else $code = uniqid();

			$add_object_query = "
				INSERT
				INTO	sales_orders
				(		id,code,customer_id,salesperson_id,status)
				VALUES
				(		null,?,?,?,?)
			";
			$GLOBALS['_database']->Execute($add_object_query,array($code,$customer->id,$salesperson->id,$status));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Sales::Order::add(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			$this->addEvent(array('new_status' => $status,'user_id' => $GLOBALS['_SESSION_']->customer->id,'type' => "CREATE"));
			return $this->update($parameters);
		}

		public function update($parameters = array()) {
			$update_object_query = "
				UPDATE	sales_orders
				SET		id = id";

			$bind_params = array();
			if (isset($parameters['status'])) {
				$update_object_query .= ", status = ?";
				array_push($bind_params,$parameters['status']);
			}

			$update_object_query .= "
				WHERE	id = ?";
			array_push($bind_params,$this->id);

			$GLOBALS['_database']->Execute($update_object_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Sales::Order::update(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$this->addEvent(array('new_status' => $status,'user_id' => $GLOBALS['_SESSION_']->customer->id,'type' => "UPDATE"));
			return $this->details();
		}

		public function get($code) {
			$get_object_query = "
				SELECT	id
				FROM	sales_orders
				WHERE	code = ?
			";

			$rs = $GLOBALS['_database']->Execute($get_object_query,array($code));
			if (! $rs) {
				$this->_error = "SQL Error in Sales::Order::get(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($this->id) = $rs->FetchRow();
			if ($this->id) {
				return $this->details();
			} else {
				return false;
			}
		}

		public function details() {
			$get_details_query = "
				SELECT	*
				FROM	sales_orders
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_details_query,array($this->id));
			if (! $rs) {
				$this->_error = "SQL Error in Sales::Order::details(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if ($this->id) {
				app_log("Got details for ".$this->id);
				$this->id = $object->id;
				$this->code = $object->code;
				$this->salesperson_id = $object->salesperson_id;
				$this->status = $object->status;
				$this->customer_id = $object->customer_id;
				return true;
			} else {
				return false;
			}
		}

		public function salesperson() {
			return new \Register\User($this->salesperson_id);
		}

		public function customer() {
			return new \Register\User($this->customer_id);
		}

		
		public function addItem($item) {
			
		}

		public function items() {

		}

		private function addEvent($parameters = array()) {
			$event = new \Sales\Order\Event();
			$parameters['order_id'] = $this->id;
			$event->add($parameters);
		}
		public function error() {
			return $this->_error;
		}
	}
