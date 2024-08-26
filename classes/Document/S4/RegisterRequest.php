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
			print "Parse It: ";
			$stage = 0;  // 0 = serial number, 1 = model number
			for ($i = 0; $i < count($array); $i ++) {
				print "[".ord($array[$i])."]";
				if (ord($array[$i]) == 0 && $stage == 1) {
					$stage = 1;
				}
				elseif ($stage == 1) {
					$this->_serialNumber .= $array[$i];
				}
				else {
					$this->_modelNumber .= $array[$i];
				}
			}
			return true;
		}

		public function build(&$string): int {
			// Build the data
			$string = "";
			print_r($this->_serialNumber);
			print("\n");
			print_r($this->_modelNumber);
			print("\n");
			$string = pack("C*",$this->_serialNumber);
			$string .= pack("C",0);
			$string .= pack("C*",$this->_modelNumber);
			return strlen($string);
		}
	}
