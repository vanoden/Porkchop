<?php
	namespace Shipping;
	
	class Item extends \ORM\BaseModel {
		public $id;
		public $package_id;
		public $product_id;
		public $serial_number;
		public $condition;
		public $quantity;
		public $description;
		public $tableName = 'shipping_items';
        public $fields = array('id','package_id','product_id','shipment_id','serial_number','condition','quantity', 'description');

		public function product() {
			return new \Product\Item($this->product_id);
		}

		public function package() {
			return new \Shipping\Package($this->package_id);
		}
	}
