<?php
	namespace Document\S4;

	/**
	 * Request to Register a new session.
	 * @package Document\S4
	 */
	class RegisterRequest Extends \Document\S4\Message {
		private $_sessionCode = "";

		public function __construct() {
			$this->_typeId = 1;
			$this->_typeName = "Register Request";
		}

		public function parse($array): bool {
			// Parse the Data
			$chars = "RegisterRequest::parse(): ";
			$stage = 0;  // 0 = serial number, 1 = model number
			for ($i = 0; $i < count($array); $i ++) {
				$chars .= "[".ord($array[$i])."]";
				if (ord($array[$i]) == 0 && $stage == 0) {
					$stage = 1;
				}
				elseif ($stage == 1) {
					$this->_serialNumber .= $array[$i];
				}
				else {
					$this->_modelNumber .= $array[$i];
				}
			}
			app_log($chars,'info');
			return true;
		}

		public function build(&$string): int {
			// Build the data - There has got to be a better way!  But this works...
			$string = "";
			$len = strlen($this->_serialNumber);
			$pack = "C".$len;
			$char = [];
			for ($i = 0; $i < $len; $i ++) {
				$char[$i] = ord(substr($this->_serialNumber,$i,1));
			}
			$string = pack($pack, ...$char);
			$string .= pack("C",0);
			$len = strlen($this->_modelNumber);
			$pack = "C".$len;
			$char = [];
			for ($i = 0; $i < $len; $i ++) {
				$char[$i] = ord(substr($this->_modelNumber,$i,1));
			}
			$string .= pack($pack, ...$char);
			return strlen($string);
		}
	}
