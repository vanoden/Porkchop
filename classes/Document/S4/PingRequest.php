<?php
	namespace Document\S4;

	class PingRequest Extends \Document\S4\Message {
		public function __constructor() {
			$this->_typeId = 1;
			$this->_typeName = "Ping Request";
		}

		public function parse($string): bool {
			// Parse the Data
			$this->_assetId = ord($string[0]) * 256 + ord($string[1]);
			$this->_timestamp = ord($string[8]) * (256 * 256 * 256) + ord($string[9]) * (256 * 256) + ord($string[10]) * 256 + ord($string[11]);
			return true;
		}

		public function build(&$string): int {
			// Build the data
			$string = "";
			return strlen($string);
		}

		public function typeName(): string {
			return "Ping Request";
		}
	}
