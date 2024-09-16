<?php
	namespace Document\S4;

	/**
	 * Response to a Authentication Request
	 * @package Document\S4
	 */
	class AuthResponse Extends \Document\S4\Message {
		public function __construct() {
			$this->_typeId = 14;
			$this->_typeName = "Auth Response";
		}

		/**
		 * Parse the message content
		 * @param array $array Incoming buffer
		 * @return bool True if successful
		 */
		public function parse(array $array = null): bool {
			if ($array[0] == 1) {
				$this->_success = true;
			}
			else {
				$this->_success = false;
			}
			return true;
		}

		/**
		 * Build the message content
		 * @param array &$array Output variable for content
		 * @return int Length of the content
		 */
		public function build(array &$array): int {
			// Build the data
			if ($this->_success) {
				$array[0] = chr(1);
			}
			else {
				$array[0] = chr(0);
			}
			return count($array);
		}
	}