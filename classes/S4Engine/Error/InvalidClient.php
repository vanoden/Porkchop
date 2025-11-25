<?php
	namespace S4Engine\Error;

	class InvalidClient Extends \S4Engine\Error\Error {
		public function __construct() {
			$this->_typeId = 3;
			$this->_typeName = "Invalid Client";
			$this->_description = "Client ID Invalid or Unrecognized";
		}
	}