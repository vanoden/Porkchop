<?php
	namespace Document\S4;

	/**
	 * S4 Reading Request
	 * Contains Datalogger Reading Information to Posting to Web Portal
	 * @package Document\S4
	 */
	class ReadingPost Extends \Document\S4\Message {
		public function __construct() {
			$this->_typeId = 5;
			$this->_typeName = "Reading Post";
		}
		/**
		 * Parse the Reading Request
		 * @param mixed $string 
		 * @return bool 
		 */
		public function parse($string): bool {
			if (count($string) != 14) {
				print "Invalid Reading Post: ".count($string)." of 14 chars\n";
				$this->error("Invalid Reading Post: ".count($string)." of 14 chars");
				return false;
			}

			// Parse the Data
			app_log("Parse Reading Meta");
			$this->_assetId = ord($string[0]) * 256 + ord($string[1]);
			$this->_sensorId = ord($string[2]) * 256 + ord($string[3]);

			app_log("Parse Reading Timestamp");
			$this->_timestamp = $this->timestampFromBytes(array($string[4], $string[5], $string[6], $string[7]));

			app_log("Parse Reading Value");
			$this->valueType(ord($string[8]));		// Value Type 0 = Float, 1 = Int, 2 = String, 3 = Boolean

			$_control = ord($string[9]);			// Control Char (Exp or Negation)
			print "Ctrl: ".$_control."\n";

			$this->_value = $this->floatFromBytes(array($string[10], $string[11], $string[12], $string[13]), $_control);
			print "Value: ".$this->_value."\n";
			return true;
		}
		public function build(&$string): int {
			// Build the data
			$meta = [];

			// 2 Byte Asset ID
			$meta[0] = floor($this->_assetId / 256);
			$meta[1] = $this->_assetId % 256;

			// 2 Byte Sensor ID
			$meta[2] = floor($this->_sensorId / 256);
			$meta[3] = floor($this->_sensorId % 256);

			// 4 Bytes Timestamp
			$timeArray = $this->timestampToBytes($this->_timestamp);
			app_log("Timestamp: ".$this->_timestamp." -> ".ord($timeArray[0]).".".ord($timeArray[1]).".".ord($timeArray[2]).".".ord($timeArray[3]));
			$meta[4] = $timeArray[0];
			$meta[5] = $timeArray[1];
			$meta[6] = $timeArray[2];
			$meta[7] = $timeArray[3];

			// 1 Byte Value Type
			$meta[8] = ord($this->valueTypeChar());	// 0 = Float, 1 = Int, 2 = String, 3 = Boolean

			// 1 Byte Control Byte (Exp, Negation)
			$control = 0;
			$meta[9] = $control;

			// Value
			$valArray = [];
			$this->floatToBytes($this->_value, $valArray, $control);
			app_log("Float: ".$this->_value." -> ".ord($valArray[0]).".".ord($valArray[1]).".".ord($valArray[2]).".".ord($valArray[3])." Control: ".$control);
			$meta[10] = ord($valArray[0]);
			$meta[11] = ord($valArray[1]);
			$meta[12] = ord($valArray[2]);
			$meta[13] = ord($valArray[3]);

			// Pack It!
			$string = pack("C*", ...$meta);
			$in = "";		// Incoming chars for debug output
			for ($i = 0; $i < strlen($string); $i++) {
				$in .= $i."[".ord(substr($string,$i,1))."]";
			}
			app_log($in);
			return strlen($string);
		}
		public function typeName(): string {
			return "Reading Request";
		}
	}
