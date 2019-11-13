<?php
	namespace Shipping;
	
	class Vendor extends \ORM\BaseModel {
		public $id;
		public $name;
		public $account_number;
		public $tableName = 'shipping_vendors';
        public $fields = array('id', 'name', 'account_number');
        
        /**
         * add by params
         * 
         * @param array $parameters, name value pairs to add and populate new object by
         */
		public function add($parameters = array()) {
			
			if (! isset($parameters['name'])) {
				$this->_error = "Name required";
				return false;
			}
			
			parent::add($parameters);
		}        
	}
