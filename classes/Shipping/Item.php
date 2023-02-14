<?php
	namespace Shipping;
	
	class Item extends \BaseClass {
	
		public $package_id;
		public $product_id;
		public $serial_number;
		public $condition;
		public $quantity;
		public $description;

		public function __construct($id = 0) {
			$this->_tableName = 'shipping_items';
			$this->_addFields(array('id','package_id','product_id','shipment_id','serial_number','condition','quantity', 'description'));
			parent::__construct($id);
		}
		public function product() {
			return new \Product\Item($this->product_id);
		}

		public function package() {
			return new \Shipping\Package($this->package_id);
		}
	}
