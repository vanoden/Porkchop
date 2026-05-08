<?php
	namespace S4Engine\Error;

	class UnparseableEnvelope Extends \S4Engine\Error\Error {
		public function __construct() {
			$this->_typeId = 5;
			$this->_typeName = "Unparseable Envelope";
			$this->_description = "The envelope provided could not be parsed correctly";
		}
	}