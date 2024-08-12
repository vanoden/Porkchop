<?php
	namespace Document\S4;

	class AuthRequest Extends \Document\S4\Message {
		public function __constructor() {
			$this->_typeName = "Auth Request";
		}

		public function parse(&$string): bool {
			if (parent::parse($string)) {
				// Parse the Data
				$this->_assetId = ord($string[0]) * 256 + ord($string[1]);
				$_authArray = array();
				for ($i = 0; $i < 16; $i++) {
					$_authArray[] = ord($string[$i + 2]);
				}
				$this->_timestamp = ord($string[8]) * (256 * 256 * 256) + ord($string[9]) * (256 * 256) + ord($string[10]) * 256 + ord($string[11]);
			}
			else return false;
		}
		public function build(&$string): int {
			// Build the data
			$length = 0;
			return $length;
		}
	}