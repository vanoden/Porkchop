<?php
	namespace Shipping;

	class VendorList Extends \BaseListClass {
		public function find($parameters = array()) {
			$this->clearError();
			$this->resetCount();

			$find_objects_query = "
				SELECT	id
				FROM	shipping_vendors
				WHERE	id = id";

			$bind_params = array();

			if (isset($parameters['shipment_id'])) {
				$find_objects_query .= "
				AND		shipment_id = ?";
				array_push($bind_params,$parameters['shipment_id']);
			}

			$rs = $GLOBALS['_database']->Execute($find_objects_query,$bind_params);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}

			$vendors = array();
			while (list($id) = $rs->FetchRow()) {
				$vendor = new \Shipping\Vendor($id);
				array_push($vendors,$vendor);
				$this->incrementCount();
			}
			return $vendors;
		}
	}
