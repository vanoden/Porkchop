<?php
	namespace Document\S4;

	/**
	 * Response to a Ping Request
	 * @package Document\S4
	 */
	class PingResponse Extends \Document\S4\Message {
		/**
		 * Parse Ping Reponse Contents
		 * @param mixed $string 
		 * @return bool 
		 */
		public function parse($string): bool {
			return true;
		}
		/**
		 * Generate Ping Response Contents
		 * @param mixed $string
		 * @return int Length of the response
		 */
		public function build(&$string): int {
			// Build the data
			$string[0] = 1;
			$length = 0;
			return $length;
		}
	}