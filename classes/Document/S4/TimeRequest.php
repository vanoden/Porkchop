<?php
	namespace Document\S4;

	/**
	 * Response to a Authentication Request
	 * @package Document\S4
	 */
	class TimeRequest Extends \Document\S4\Message {
		public function __construct() {
			$this->_typeId = 11;
			$this->_typeName = "Time Request";
		}

		/**
		 * Parse the message content
		 * @param array $array Incoming buffer
		 * @return bool True if successful
		 */
		public function parse(array $array = null): bool {
			return true;
		}

		/**
		 * Build the message content
		 * @param array &$array Output variable for content
		 * @return int Length of the content
		 */
		public function build(array &$array): int {
			return 0;
		}
	}