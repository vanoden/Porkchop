<?php
	namespace Purchase\Order;

	class Payment extends \BaseModel {

		public function __construct($id = null) {
			if (!empty($id)) {
				$this->id = $id;
				return $this->details();
			}
		}
	}
