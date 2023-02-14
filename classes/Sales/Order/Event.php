<?php
	namespace Sales\Order;

	class Event extends \BaseClass {
		public $id;

		public function add($parameters = []) {
			$add_object_query = "
				INSERT
				INTO	sales_order_events
				(		id,order_id,date_event,user_id,new_status)
				VALUES
				(		null,?,sysdate(),?,?)
			";
            if (!isset($parameters['user_id'])) $parameters['user_id'] = $GLOBALS['_SESSION_']->customer->id;
			$GLOBALS['_database']->Execute($add_object_query,array($parameters["order_id"],$parameters['user_id'],$parameters['new_status']));
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
			if (isset($parameters['new_status'])) {
				$update_object_query .= ", new_status = ?";
				array_push($bind_params,$parameters['new_status']);
			}
			if (isset($parameters['abbreviation'])) {
				$update_object_query .= ", abbreviation = ?";
				array_push($bind_params,$parameters['abbreviation']);
			}
            if (isset($parameters['message'])) {
                $update_object_query .= ", message = ?";
                array_push($bind_params,$parameters['message']);
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
				$this->order_id = $object->order_id;
				$this->user_id = $object->user_id;
				$this->date_event = $object->date_event;
                $this->new_status = $object->new_status;
                $this->message = $object->message;
				return true;
			}
			else {
				return false;
			}
		}
	}
