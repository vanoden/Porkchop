<?php
	namespace Document\S4;

	class PingResponse Extends \Document\S4Factory {
		public function parse($string): bool {
			return true;

		}
		public function build(&$string): int {
			// Build the data
			$string[0] = 1;
			$length = 0;
			return $length;
		}
	}