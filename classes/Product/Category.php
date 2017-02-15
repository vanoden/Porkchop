<?php
	namespace Product;

	class Category {
		public $id;
		public $name;
		public $error;
		public function __construct($id = 0) {
			# Database Initialization
			$init = new ProductInit();
			if ($init->error) {
				$this->error = $init->error;
				return 0;
			}
		}
	}
?>
