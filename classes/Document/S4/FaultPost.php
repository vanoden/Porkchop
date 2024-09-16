<?php
	namespace Document\S4;

	/**
	 * S4 Fault Post
	 * Contains Datalogger Reading Information to Posting to Web Portal
	 * @package Document\S4
	 */
	class FaultPost Extends \Document\S4\Message {
		/**
		 * Parse the Reading Request
		 * @param mixed $string 
		 * @return bool 
		 */
		public function parse( array $array): bool {
			if (count($array) != 9) {
				print "Invalid Reading Request: ".count($array)." chars\n";
				$this->error("Invalid Reading Request");
				return false;
			}

			// Parse the Data
			$this->_assetId = ord($array[0]) * 256 + ord($array[1]);
			$this->_sensorId = ord($array[2]) * 256 + ord($array[3]);
			$this->_valueType = ord($array[4]);
			$_readingExponent = ord($array[9]);
			print "Exp: ".$_readingExponent."\n";
			print ord($array[8]).":".ord($array[7]).":".ord($array[6]).":".ord($array[5])."\n";
			print (ord($array[5]) * (256 * 256 * 256))."+";
			print (ord($array[6]) * (256 * 256))."+";
			print (ord($array[7]) * 256)."+";
			print (ord($array[8]));
			print " = ". (ord($array[5]) * (256 * 256 * 256) + ord($array[6]) * (256 * 256) + ord($array[7]) * 256 + ord($array[8]))."\n";
			$this->_value = ord($array[5]) * (256 * 256 * 256) + ord($array[6]) * (256 * 256) + ord($array[7]) * 256 + ord($array[8]);
			print (ord($array[13]) * (256 * 256 * 256))."+";
			print (ord($array[12]) * (256 * 256))."+";
			print (ord($array[11]) * 256)."+";
			print (ord($array[10])."\n");
			$this->_value -= 2147483648;
			print "EXP: = ".pow(10, $_readingExponent)."\n";
			$this->_value /= pow(10, $_readingExponent + 1);
			print "Value: ".$this->_value."\n";
			$this->_timestamp = ord($array[13]) * (256 * 256 * 256) + ord($array[12]) * (256 * 256) + ord($array[11]) * 256 + ord($array[10]);
			return true;
		}
		public function build(array &$array): int {
			// Build the data
			$length = 0;
			return $length;
		}
		public function typeName(): string {
			return "Reading Request";
		}
	}
