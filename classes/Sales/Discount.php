<?php
	namespace Sales;

	class Discount Extends \BaseClass {

		public $type;
		public $amount;
		public $date_active;
		public $status;

		public function __construct($id = 0) {
			$this->_tableName = "sales_discounts";
			$this->_cacheKeyPrefix = "sales.discounts";
    		parent::__construct($id);
		}

		public function add($parameters = []) {
			return $this->update($parameters);
		}
	}
?>
