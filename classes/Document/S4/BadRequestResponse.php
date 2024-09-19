<?php
	namespace Document\S4;

	class BadRequestResponse Extends \Document\S4\Message {
		public function __construct() {
			$this->_typeId = 10;
			$this->_typeName = "Bad Request Response";
		}

		public function parse(array $array = null): bool {
			return true;
		}

		public function build(array &$array): int {
			return 0;
		}
	}