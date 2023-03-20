<?php
	namespace Database\Schema\Table;

	class Column {
		private $_error;
		public $exists;
		public $name;
		public $schema_name;
		public $table_name;
		public $default;
		public $nullable;
		public $data_type;
		public $character_maximum_length;
		public $numeric_precision;
		public $numeric_scale;
		public $datetime_precision;
		public $type;
		public $key;
		public $comment;

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
