<?php
	namespace Document\S4;

	/**
	 * S4 Reading Request
	 * Contains Datalogger Reading Information to Posting to Web Portal
	 * @package Document\S4
	 */
	class ReadingRequest Extends \Document\S4\Message {
		/**
		 * Parse the Reading Request
		 * @param mixed $string 
		 * @return bool 
		 */
		public function parse($string): bool {
			if (count($string) != 14) {
				print "Invalid Reading Request: ".count($string)." chars\n";
				$this->error("Invalid Reading Request");
				return false;
			}

			// Parse the Data
			$this->_assetId = ord($string[0]) * 256 + ord($string[1]);
			$this->_sensorId = ord($string[2]) * 256 + ord($string[3]);
			$this->_valueType = ord($string[4]);
			$_readingExponent = ord($string[9]);
			print "Exp: ".$_readingExponent."\n";
			print ord($string[8]).":".ord($string[7]).":".ord($string[6]).":".ord($string[5])."\n";
			print (ord($string[5]) * (256 * 256 * 256))."+";
			print (ord($string[6]) * (256 * 256))."+";
			print (ord($string[7]) * 256)."+";
			print (ord($string[8]));
			print " = ". (ord($string[5]) * (256 * 256 * 256) + ord($string[6]) * (256 * 256) + ord($string[7]) * 256 + ord($string[8]))."\n";
			$this->_value = ord($string[5]) * (256 * 256 * 256) + ord($string[6]) * (256 * 256) + ord($string[7]) * 256 + ord($string[8]);
			print (ord($string[13]) * (256 * 256 * 256))."+";
			print (ord($string[12]) * (256 * 256))."+";
			print (ord($string[11]) * 256)."+";
			print (ord($string[10])."\n");
			$this->_value -= 2147483648;
			print "EXP: = ".pow(10, $_readingExponent)."\n";
			$this->_value /= pow(10, $_readingExponent + 1);
			print "Value: ".$this->_value."\n";
			$this->_timestamp = ord($string[13]) * (256 * 256 * 256) + ord($string[12]) * (256 * 256) + ord($string[11]) * 256 + ord($string[10]);
			return true;
		}
		public function build(&$string): int {
			// Build the data
			$length = 0;
			return $length;
		}
		public function typeName(): string {
			return "Reading Request";
		}
	}
