<?php
	namespace Shipping;
	
	class Shipment {
	
		private $_error;
		public $id;
		public $document;
		public $date_entered;
		public $date_shipped;
		public $status;
		public $send_contact_id;
		public $send_location_id;
		public $rec_contact_id;
		public $rec_location_id;
		public $vendor;
		
		public function __construct($id = 0) {
			if (is_numeric($id) && $id > 0) {
				$this->id = $id;
				return $this->details();
			}
		}

		public function add($parameters) {
			return $this->update($parameters);
		}

		public function update($parameters) {
			return $this->details();
		}

		public function details() {
			return true;
		}

		public function error() {
			return $this->_error;
		}
	}

