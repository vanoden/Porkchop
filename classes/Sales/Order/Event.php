<?php
	namespace Sales\Order;

	class Event extends \ORM\BaseModel {
		public $id;

		public function add($parameters) {
			$add_object_query = "
				INSERT
				INTO	sales_order_events
				(		id,order_id,date_event,user_id)
				VALUES
				(		null,?,?,?)
			";
			$GLOBALS['_database']->Execute($add_object_query,array($parameters["name"]));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Sales::Event::add(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}

		public function update($parameters = array()) {
			$update_object_query = "
				UPDATE	sales_order_events
				SET		id = id";

			$bind_params = array();
			if (isset($parameters['status'])) {
				$update_object_query .= ", name = ?";
				array_push($bind_params,$parameters['name']);
			}
			if (isset($parameters['abbreviation'])) {
				$update_object_query .= ", abbreviation = ?";
				array_push($bind_params,$parameters['abbreviation']);
			}

			$update_object_query .= "
				WHERE	id = ?";
			array_push($bind_params,$this->id);

			$GLOBALS['_database']->Execute($update_object_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Sales::Event::update(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			return $this->details();
		}

		public function details() {
			$get_details_query = "
				SELECT	*
				FROM	sales_order_events
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_details_query,array($this->id));
			if (! $rs) {
				$this->_error = "SQL Error in Sales::Event::details(): ".$GLOBALS['_database']->ErrorMsg();
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

		public function error() {
			return $this->_error;
		}
	}
