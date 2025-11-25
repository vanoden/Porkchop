<?php
	namespace S4Engine\Error;

	class InvalidFunction Extends \S4Engine\Error\Error {
		public function __construct() {
			$this->_typeId = 2;
			$this->_typeName = "Invalid Function";
			$this->_description = "The function requested is not supported by the server";
		}
	}