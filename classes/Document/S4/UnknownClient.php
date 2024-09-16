<?php
	namespace Document\S4;

	class UnknownClient Extends \Document\S4\Message {
		public function __construct() {
			$this->_typeId = 8;
			$this->_typeName = "System Error Response";
		}

		public function parse(array $array): bool {
			return true;
		}

		public function build(array &$array): int {
			return 0;
		}
	}