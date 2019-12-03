<?php
	namespace Shipping;

	class ShipmentList {
		private $_error;
		private $_count = 0;

		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	shipping_shipments
				WHERE	id = id";

			$bind_params = array();

			if ($parameters['document_id']) {
				$find_objects_query .= "
				AND		document_id = ?";
				array_push($bind_params,$parameters['document_id']);
			}

			$rs = $GLOBALS['_database']->Execute($find_objects_query,$bind_params);
			if (! $rs) {
				$this->_error = "SQL Error in Shipping::ShipmentList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$shipments = array();
			while (list($id) = $rs->FetchRow()) {
				$shipment = new \Shipping\Shipment($id);
				array_push($shipments,$shipment);
				$this->_count ++;
			}
			return $shipments;
		}

		public function error() {
			return $this->_error;
		}

		public function count() {
			return $this->_count;
		}
	}
?>
