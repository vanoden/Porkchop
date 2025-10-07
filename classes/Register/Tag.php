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

		/**
		 * Validate tag name
		 * @param string $string
		 * @return bool
		 */
		public function validName($string): bool {
			if (preg_match('/^\w[\w\-\_\s]*$/',$string)) return true;
			else return false;
		}

		/**
		 * Validate tag type
		 * @param string $string
		 * @return bool
		 */
		public function validType($string): bool {
			$validTypes = array('ORGANIZATION','USER','CONTACT','LOCATION');
			if (in_array($string, $validTypes)) return true;
			else return false;
		}
}
