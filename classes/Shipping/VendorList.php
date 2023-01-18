<?php
	namespace Shipping;

	class VendorList {
		private $_error;
		private $_count = 0;

		public function find($parameters = array()) {
		
			$find_objects_query = "
				SELECT	id
				FROM	shipping_vendors
				WHERE	id = id";

			$bind_params = array();

			if ($parameters['shipment_id']) {
				$find_objects_query .= "
				AND		shipment_id = ?";
				array_push($bind_params,$parameters['shipment_id']);
			}

			$rs = $GLOBALS['_database']->Execute($find_objects_query,$bind_params);
			if (! $rs) {
				$this->_error = "SQL Error in Shipping::VendorList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$vendors = array();
			while (list($id) = $rs->FetchRow()) {
				$vendor = new \Shipping\Vendor($id);
				array_push($vendors,$vendor);
				$this->_count ++;
			}
			return $vendors;
		}
		
		public function findUnique() {
			$find_objects_query = "
				SELECT	name
				FROM	shipping_vendors
				WHERE	id = id
				GROUP BY name";

			$bind_params = array();
			$rs = $GLOBALS['_database']->Execute($find_objects_query,$bind_params);
			if (! $rs) {
				$this->_error = "SQL Error in Shipping::VendorList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$names = array();
			while (list($name) = $rs->FetchRow()) {
				array_push($names,$name);
				$this->_count ++;
			}
			return $names;
		}
		

		public function error() {
			return $this->_error;
		}

		public function count() {
			return $this->_count;
		}
	}
