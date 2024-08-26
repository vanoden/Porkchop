<?php
	namespace Document\S4;

	class Unauthorized Extends \Document\S4\Message {
		public function __construct() {
			$this->_typeId = 19;
			$this->_typeName = "Unauthorized";
		}

		public function parse(&$string): bool {
			return true;
		}

		public function build(&$string): int {
			return 0;
		}
	}