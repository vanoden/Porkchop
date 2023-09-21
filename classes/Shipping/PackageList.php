<?php
	namespace Shipping;

	class PackageList {
		private $_error;
		private $_count = 0;

		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	shipping_packages
				WHERE	id = id";

			$bind_params = array();

			if (isset($parameters['shipment_id']) && !empty($parameters['shipment_id'])) {
				$find_objects_query .= "
				AND		shipment_id = ?";
				array_push($bind_params,$parameters['shipment_id']);
			}

			$rs = $GLOBALS['_database']->Execute($find_objects_query,$bind_params);
			if (! $rs) {
				$this->_error = "SQL Error in Shipping::PackageList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$packages = array();
			while (list($id) = $rs->FetchRow()) {
				$package = new \Shipping\Package($id);
				array_push($packages,$package);
				$this->_count ++;
			}
			return $packages;
		}

		public function error() {
			return $this->_error;
		}

		public function count() {
			return $this->_count;
		}
	}
