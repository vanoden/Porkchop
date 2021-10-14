<?php
	namespace Sales;

	class Discount {
		public $id;
		public $type;
		public $amount;
		public $date_active;
		public $status;

		public function __construct($id = 0) {
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters) {
			return $this->update($parameters);
		}

		public function update($parameters) {
			return $this->details();
		}

		public function details() {
		}
	}
?>
