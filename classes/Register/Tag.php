<?php
	namespace Register;

	class Tag extends \BaseModel {

		public $type;
        public $register_id;
		public $name;		
		protected $_database;

		public function __construct($id = 0) {
			$this->_database = new \Database\Service();
			$this->_tableName = 'register_tags';
			$this->_addFields(array('id','type','register_id','name'));
			parent::__construct($id);
		}
}
