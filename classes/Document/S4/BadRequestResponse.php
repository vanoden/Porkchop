<?php
	namespace Document\S4;

	class BadRequestResponse Extends \Document\S4\Message {
		public function __construct() {
			$this->_typeId = 10;
			$this->_typeName = "Bad Request Response";
		}

		public function parse(array $array, $length = 0): bool {
			$this->_errorType = ord($array[0]);
			return true;
		}

		public function build(array &$array): int {
			$array[0] = chr($this->_errorType);
			return 1;
		}

		public function readable(): string {
			$errorFactory = new \S4Engine\Error\Factory();
			if (!$this->_errorType) {
				app_log("Error, no error",'error');
				return "No error type specified";
			}
			$error = $errorFactory->createError($this->_errorType);
			if ($error !== null) {
				return $error->description();
			}
			return "Error Type ".$this->_errorType;
		}
	}