<?php
	namespace Document\S4;

	class PingResponse Extends \Document\S4\Message {
		public function __construct() {
			$this->_typeId = 4;
			$this->_typeName = "Ping Response";
		}

		public function parse(&$string): bool {
			return true;
		}

		public function build(&$string): int {
			// Build the data
			$length = 0;
			return $length;
		}
	}