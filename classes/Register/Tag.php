<?php
	namespace Register;

	class Tag extends \ORM\BaseModel {
	
		public $id;
		public $type;
        public $register_id;
		public $name;		
		public $tableName = 'register_tags';
        public $fields = array('id','type','register_id','name');

		public function __construct($id = 0) {
			parent::__construct($id);
		}
}
