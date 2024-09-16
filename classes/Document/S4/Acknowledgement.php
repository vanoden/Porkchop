<?php
	namespace Document\S4;

	/**
	 * Response to a Ping Request
	 * @package Document\S4
	 */
	class Acknowledgement Extends \Document\S4\Message {
		private $_successful = false;

		public function __construct() {
			$this->_typeId = 7;
			$this->_typeName = "Acknowledgement";
		}

		/**
		 * Parse the message content
		 * @param mixed &$string Output variable for buffer
		 * @return bool True if successful
		 */
		public function parse(array $array): bool {
			if ($array[0] == 1) {
				$this->_successful = true;
			}
			else {
				$this->_successful = false;
			}
			$this->_timestamp = $this->timestampFromBytes(array($array[1], $array[2], $array[3], $array[4]));
			return true;
		}

		/**
		 * Build the message content
		 * @param mixed &$string Output variable for content
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