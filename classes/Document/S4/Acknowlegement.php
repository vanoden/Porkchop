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
		public function parse(&$string): bool {
			if ($string[0] == 1) {
				$this->_successful = true;
			}
			else {
				$this->_successful = false;
			}
			$this->_timestamp = $this->timestampFromBytes(array($string[1], $string[2], $string[3], $string[4]));
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
			if (empty($this->_timestamp)) $this->_timestamp = time();
			$string = array_merge($string,$this->timestampToBytes($this->_timestamp));
			$length = 5;
			return $length;
		}
	}