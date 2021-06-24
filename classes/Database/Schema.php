<?php
	namespace Database;

	class Schema {
		private $_error;
		private $name;

		public function __construct($name) {
			
		}

		public function error() {
			return $this->error;
		}
	}
