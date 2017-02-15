<?php
	namespace Product;

	class Unique {
		public $product_id;
		public $serial_number;
		public $error;

		public function __construct() {
			# Database Initialization
			$init = new ProductInit();
			if ($init->error) {
				$this->error = $init->error;
				return 0;
			}
		}
	}
?>
