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

		public function parse(array $array): bool {
			// Parse the Data
			$chars = "RegisterRequest::parse(): ";
			$stage = 0;  // 0 = serial number, 1 = model number
			$realchars = "";
			for ($i = 0; $i < count($array); $i ++) {
				$chars .= "[".ord($array[$i])."]";
				$realchars .= $array[$i];
				if (ord($array[$i]) == 0 && $stage == 0) {
					$stage = 1;
				}
				elseif ($stage == 1) {
					$this->_modelNumber .= $array[$i];
				}
				else {
					$this->_serialNumber .= $array[$i];
				}
			}
			app_log($chars,'info');
			//app_log($realchars,'info');
			return true;
		}

		public function build(array &$array): int {
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
			$array = str_split($string);
			return count($array);
		}
	}
