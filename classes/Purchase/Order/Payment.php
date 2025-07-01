<?php
	namespace Purchase\Order;

	class Payment extends \BaseModel {

		public function __construct($id = null) {
			$this->_tableName = 'purchase_order_payments';
			if (!empty($id)) {
				$this->id = $id;
				return $this->details();
			}
		}
	}
