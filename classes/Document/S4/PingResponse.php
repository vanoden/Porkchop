<?php
	namespace Document\S4;

	class PingResponse Extends \Document\S4\Message {
		public function __construct() {
			$this->_typeId = 2;
			$this->_typeName = "Ping Response";
		}
		public function parse(&$string): bool {
			$this->_assetId = ord($string[0]) * 256 + ord($string[1]);
			$this->_timestamp = ord($string[2]) * (256 * 256 * 256) + ord($string[3]) * (256 * 256) + ord($string[4]) * 256 + ord($string[5]);
			return true;
		}

		public function build(&$string): int {
			// Build the data
			$string[0] = $this->_assetId / 256;
			$string[1] = $this->_assetId % 256;
			$this->_timestamp = time();
			$string[2] = $this->_timestamp / (256 * 256 * 256);
			$string[3] = $this->_timestamp / (256 * 256);
			$string[4] = $this->_timestamp / 256;
			$string[5] = $this->_timestamp % 256;
			$length = 6;
			return $length;
		}
	}