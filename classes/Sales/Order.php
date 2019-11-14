<?php
	namespace Sales;

	class Order extends \ORM\BaseModel {
		public $id;
		public $name;
		public $abbreviation;

		public function add($parameters) {
			$add_object_query = "
				INSERT
				INTO	sales_orders
				(		id,customer_id,salesperson_id,status)
				VALUES
				(		null,?,?,'NEW')
			";
			$GLOBALS['_database']->Execute($add_object_query,array($parameters["name"]));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Sales::Order::add(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
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
			return $this->details();
		}

		public function get($name) {
			$get_object_query = "
				SELECT	id
				FROM	sales_orders
				WHERE	name = ?
			";

			$rs = $GLOBALS['_database']->Execute($get_object_query,array($name));
			if (! $rs) {
				$this->_error = "SQL Error in Sales::Order::get(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($this->id) = $rs->FetchRow();
			if ($this->id) {
				return $this->details();
			}
			else {
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
				$this->name = $object->name;
				$this->abbreviation = $object->abbreviation;
				return true;
			}
			else {
				return false;
			}
		}

		public function addItem($item) {
			
		}

		public function items() {

		}

		public function error() {
			return $this->_error;
		}
	}
