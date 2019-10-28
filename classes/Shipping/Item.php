<?php
	namespace Shipping;
	
	class Item {
		private $_error;
		public $id;
		public $package;
		public $product;
		public $serial_number;
		public $condition;
		public $quantity;
		
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
