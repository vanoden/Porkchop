<?php
	namespace Support;

	class ShipmentItem {
	
		public $error;
		public $id;

		public function __construct($id = 0) {}

        /**
         * find shipment item by serial number
         *
         * @param string $serialNumber
         */
        function findBySerial($serialNumber = 0) {
			$get_request_query = "
				SELECT	id, action_id, shipment_id, user_id, product_id, serial_number, quantity
				FROM	support_shipment_items
				WHERE	serial_number = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_request_query,
				array($serialNumber)
			);
			
			if (! $rs) {
				$this->error = "SQL Error in ShipmentItem::findBySerial: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $rs->FetchObject();
        }
	}
