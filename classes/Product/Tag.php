<?php
	namespace Product;

	class Tag extends \BaseModel {

		public $type;
        public $product_id;
		public $name;		

		public function __construct($id = 0) {
			$this->_database = new \Database\Service();
			$this->_tableName = 'product_tags';
			$this->_addFields(array('id','product_id','name'));
			parent::__construct($id);
		}
}
