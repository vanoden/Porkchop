<?php
	namespace S4Engine\Error;

	class UnparseableContent Extends \S4Engine\Error\Error {
		public function __construct() {
			$this->_typeId = 6;
			$this->_typeName = "Unparseable Content";
			$this->_description = "The content provided could not be parsed correctly";
		}
	}