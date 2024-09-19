<?php
	namespace Document\S4;

	class SystemErrorResponse Extends \Document\S4\Message {
		public function __construct() {
			$this->_typeId = 20;
			$this->_typeName = "System Error Response";
		}

		public function parse(array $array = null): bool {
			return true;
		}

		public function build(array &$array): int {
			return 0;
		}
	}