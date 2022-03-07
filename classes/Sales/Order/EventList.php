<?php
	namespace Sales\Order;

	class EventList {
		public $_count;
		public $_error;

		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	sales_order_events
				WHERE	id = id
			";

			$bind_params = array();

			if (!empty($parameters['order_id'])) {
				$find_objects_query .= "
				AND		order_id = ?";
				array_push($bind_params,$parameters['order_id']);
			}

			$rs = $GLOBALS['_database']->Execute($find_objects_query,$bind_params);
			if (! $rs) {
				$this->error("SQL Error in Sales::Order::EventList::find(): ".$GLOBALS['_database']->ErrorMsg());
				return null;
			}
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$object = new \Sales\Order\Event($id);
				array_push($objects,$object);
				$this->_count ++;
			}
			return $objects;
		}

		public function error($message = null) {
			if (!empty($message)) $this->_error = $message;
			return $this->_error;
		}

		public function count() {
			return $this->_count;
		}
	}
?>
