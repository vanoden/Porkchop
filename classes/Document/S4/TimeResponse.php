<?php
	namespace Document\S4;

	/**
	 * Response to a Authentication Request
	 * @package Document\S4
	 */
	class TimeResponse Extends \Document\S4\Message {
		public function __construct() {
			$this->_typeId = 12;
			$this->_typeName = "Time Response";
		}

		/**
		 * Parse the message content
		 * @param array $array Incoming buffer
		 * @return bool True if successful
		 */
		public function parse(array $array, $length = 0): bool {
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
			// Build the data: 4 Bytes Timestamp
			$timeArray = $this->timestampToBytes(time());
			app_log("Timestamp: ".$this->_timestamp." -> ".ord($timeArray[0]).".".ord($timeArray[1]).".".ord($timeArray[2]).".".ord($timeArray[3]));
			$array[0] = $timeArray[0];
			$array[1] = $timeArray[1];
			$array[2] = $timeArray[2];
			$array[3] = $timeArray[3];
			return count($array);
		}
	}