<?php
	namespace Product;

	class Unique Extends \Product\Item {

		public $product_id;
		public $serial_number;

		public function __construct() {
		}

		public function validSerialNumber($string): bool {
			if (preg_match('/^[\w\.\-\_]+$/',$string)) return true;
			else return false;
		}
	}
