<?php
	namespace S4Engine\Error;

	class InvalidServer Extends \S4Engine\Error\Error {
		public function __construct() {
			$this->_typeId = 4;
			$this->_typeName = "Invalid Server";
			$this->_description = "Server ID Invalid or Unrecognized";
		}
	}