<?php
	namespace Register;

	class Tag extends \ORM\BaseModel {

		public $type;
        public $register_id;
		public $name;		

		public function __construct($id = 0) {
			$this->_tableName = 'register_tags';
			$this->_addFields(array('id','type','register_id','name'));
			parent::__construct($id);
		}
}
