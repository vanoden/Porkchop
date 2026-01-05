<?php
	namespace S4Engine\Error;

	class InvalidSession Extends \S4Engine\Error\Error {
		public function __construct() {
			$this->_typeId = 1;
			$this->_typeName = "Invalid Session";
			$this->_description = "The session ID provided is invalid or has expired";
		}
	}