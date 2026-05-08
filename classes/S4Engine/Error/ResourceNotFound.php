<?php
	namespace S4Engine\Error;

	class ResourceNotFound Extends \S4Engine\Error\Error {
		public function __construct() {
			$this->_typeId = 8;
			$this->_typeName = "Resource Not Found Error";
			$this->_description = "A requested resource could not be found on the server";
		}
	}