<?php
	namespace Database\Schema\Table;

	class Column {
		private $_error;
		public $exists;
		public $name;

		public function __construct($name = null) {
			if (isset($name)) {
				if (preg_match('/^\w[\w\_]*$/',$name)) {
					$this->name = $name;
				}
			}
		}

		public function error() {
			return $this->_error;
		}
	}
