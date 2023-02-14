<?php
	namespace Shipping;
	
	class Vendor extends \BaseModel {
	
		public $name;
		public $account_number;

		public function __construct($id = 0) {
			$this->_database = new \Database\Service();		
			$this->_tableName = 'shipping_vendors';
			$this->_addFields(array('id', 'name', 'account_number'));
			$this->_tableUKColumn = 'name';
			parent::__construct($id);
		}

        /**
         * add by params
         * 
         * @param array $parameters, name value pairs to add and populate new object by
         */
		public function add($parameters = []) {
			
			if (! isset($parameters['name'])) {
				$this->error("Name required");
				return false;
			}
			
			return parent::add($parameters);
		}

		public function get($name): bool {
			app_log("Getting vendor $name");
			return parent::get($name,'name');
		}
	}
