<?php
	namespace Shipping;
	
	class Package {

    	private $_error;
		public $id;
		public $shipment;
		public $number;
		public $tracking_code;
		public $status;
		public $condition;
		public $height;
		public $width;
		public $depth;
		public $weight;
		public $shipping_cost;
		public $date_received;
		public $user_received;
		
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
