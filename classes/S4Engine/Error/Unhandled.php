<?php
	namespace S4Engine\Error;

	class Unhandled Extends \S4Engine\Error\Error {
		public function __construct() {
			$this->_typeId = 255;
			$this->_typeName = "Unhandled Error";
			$this->_description = "An unhandled error has occurred";
		}
	}