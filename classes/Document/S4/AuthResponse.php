<?php
	namespace Document\S4;

	/**
	 * Response to a Authentication Request
	 * @package Document\S4
	 */
	class AuthResponse Extends \Document\S4\Message {
		private $_successful = false;

		public function __construct() {
			$this->_typeId = 14;
			$this->_typeName = "Auth Response";
		}

		/**
		 * Parse the message content
		 * @param mixed &$string Output variable for buffer
		 * @return bool True if successful
		 */
		public function parse(&$string): bool {
			if ($string[0] == 1) {
				$this->_successful = true;
			}
			else {
				$this->_successful = false;
			}
			return true;
		}

		/**
		 * Build the message content
		 * @param mixed &$string Output variable for content
		 * @return int Length of the content
		 */
		public function build(&$string): int {
			// Build the data
			if ($this->_successful) {
				$string[0] = 1;
			}
			else {
				$string[0] = 0;
			}
			$length = 1;
			return $length;
		}
	}