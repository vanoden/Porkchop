<?php
	namespace Shipping;
	
	class Item extends \ORM\BaseModel {
		public $id;
		public $package;
		public $product;
		public $serial_number;
		public $condition;
		public $quantity;
		public $description;
		public $tableName = 'shipping_items';
        public $fields = array('id','package_id','product_id','serial_number','condition','quantity', 'description');
	}
