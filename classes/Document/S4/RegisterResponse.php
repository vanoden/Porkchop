<?php
	namespace Document\S4;

	/**
	 * Response to a Request to Register a new session.
	 * @package Document\S4
	 */
	class RegisterResponse Extends \Document\S4\RegisterRequest {
		private $_sessionCode = "";

		public function __construct() {
			$this->_typeId = 2;
			$this->_typeName = "Register Response";
		}
	
		public function readable(): string {
			return "";
		}
	}