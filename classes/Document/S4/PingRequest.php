<?php
	namespace Document\S4;

	class PingRequest Extends \Document\S4\Message {
		public function __construct() {
			$this->_typeId = 3;
			$this->_typeName = "Ping Request";
		}

		public function parse($string): bool {
			// Parse the Data
			return true;
		}

		public function build(&$string): int {
			// Build the data
			return 0;
		}
	}
