<?php
	namespace Document\S4;

	class PingResponse Extends \Document\S4\Message {
		public function __construct() {
			$this->_typeId = 4;
			$this->_typeName = "Ping Response";
		}

		public function parse(array $array): bool {
			return true;
		}

		public function build(array &$array): int {
			// Build the data
			return 0;
		}
	}