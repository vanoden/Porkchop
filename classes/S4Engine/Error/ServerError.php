<?php
	namespace S4Engine\Error;

	class ServerError Extends \S4Engine\Error\Error {
		public function __construct() {
			$this->_typeId = 7;
			$this->_typeName = "Server Error";
			$this->_description = "An internal server error has occurred";
		}
	}