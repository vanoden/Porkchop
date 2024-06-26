<?php
	namespace Document\S4;

	class PingResponse Extends \Document\S4Factory {
		public function parse(&$string): bool {
			if (parent::parse($string)) {
				// Parse the Data
			}
			else return false;

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