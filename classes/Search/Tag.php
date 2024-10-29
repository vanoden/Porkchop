<?php
	namespace Search;

	class Tag Extends \BaseModel {
		public $class = null;
		public $category = null;
		public $value = null;

		public function __contruct($id = 0) {
			$this->_tableName = 'search_tags';
			$this->_addFields('id','class','category','value');

			parent::__construct($id);
		}
	
		public function validClass($class): bool {
			if (!preg_match('/^[A-Z][a-zA-Z0-9]*::[A-Z][a-zA-Z0-9]*$/',$class)) return false;
			$classCheckString = '\\'.str_replace('::','\\',$class);
			if (class_exists($classCheckString)) return true;
			app_log("Class ".$classCheckString." does not exist",'error',__FILE__,__LINE__);
			error_log("Class ".$classCheckString." does not exist");
			return false;
		}

		public function validCategory($category): bool {
			if (preg_match('/^[a-zA-Z][a-zA-Z0-9\.\-\_\s]*$/',$category)) return true;
			return false;
		}

		public function validValue($value): bool {
			if (preg_match('/^[\w\-\.\_\s]+$/',$value)) return true;
			return false;
		}
	}