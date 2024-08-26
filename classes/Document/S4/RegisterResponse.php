<?php
	namespace Document\S4;

	/**
	 * Response to a Request to Register a new session.
	 * @package Document\S4
	 */
	class RegisterResponse Extends \Document\S4\Message {
		private $_sessionCode = "";

		public function __construct() {
			$this->_typeId = 2;
			$this->_typeName = "Register Response";
		}

		public function parse($array): bool {
			$stage = 0;  // 0 = serial number, 1 = model number
			// Parse the Data
			print $this->_serialNumber."\n";
			print $this->_modelNumber."\n";
			for ($i = 0; $i < count($array); $i ++) {
				print "[".ord($array[$i])."]";
				if (ord($array[$i]) == 0 && $stage == 1) {
					$stage = 2;
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
			for ($i = 0; $i < strlen($this->_serialNumber); $i ++) {
				$string .= substr($this->_serialNumber,$i,1);
			}
			$string .= chr(0);
			for ($i = 0; $i < strlen($this->_modelNumber); $i ++) {
				$string .= substr($this->_modelNumber,$i,1);
			}
			print "build(): ";
			for ($i = 0; $i < strlen($string); $i++) {
				print "[".ord(substr($string,$i,1))."]";
			}
			print "\n";
			return strlen($this->_serialNumber) + strlen($this->_modelNumber) + 1;
		}
	}
