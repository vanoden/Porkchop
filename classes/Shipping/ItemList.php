<?php
	namespace Shipping;

	class ItemList {
		private $_error;
		private $_count = 0;

		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	shipping_items
				WHERE	id = id";

			$bind_params = array();
			
			if (isset($parameters['shipment_id'])) {
				$find_objects_query .= "
				AND		shipment_id = ?";
				array_push($bind_params,$parameters['shipment_id']);
			}

			if (isset($parameters['package_id'])) {
				$find_objects_query .= "
				AND		package_id = ?";
				array_push($bind_params,$parameters['package_id']);
			}

			if (isset($parameters['serial_number'])) {
				$find_objects_query .= "
				AND		serial_number = ?";
				array_push($bind_params,$parameters['serial_number']);
			}
			
			
			if (isset($parameters['product_id'])) {
				$find_objects_query .= "
				AND		product_id = ?";
				array_push($bind_params,$parameters['product_id']);
			}

			$rs = $GLOBALS['_database']->Execute($find_objects_query,$bind_params);
			if (! $rs) {
				$this->_error = "SQL Error in Shipping::ItemList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$items = array();
			while (list($id) = $rs->FetchRow()) {
				$item = new \Shipping\Item($id);
				array_push($items,$item);
				$this->_count ++;
			}
			return $items;
		}

		public function error() {
			return $this->_error;
		}

		public function count() {
			return $this->_count;
		}
	}
