<?php
	namespace Document\S4;

	class PingRequest Extends \Document\S4\BaseClass {
		public function parse($string): bool {
			// Parse the Data
			$this->_assetId = ord($string[0]) * 256 + ord($string[1]);
			$this->_timestamp = ord($string[8]) * (256 * 256 * 256) + ord($string[9]) * (256 * 256) + ord($string[10]) * 256 + ord($string[11]);
			return true;
		}

		public function build(&$string): int {
			// Build the data
			$length = 0;
			return $length;
		}

		public function typeName(): string {
			return "Ping Request";
		}
	}