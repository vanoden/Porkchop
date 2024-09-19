<?php
	namespace Document\S4;

	/**
	 * S4 Reading Request
	 * Contains Datalogger Reading Information to Posting to Web Portal
	 * @package Document\S4
	 */
	class ReadingPost Extends \Document\S4\Message {

// ASSETID      SENSORID     TIMESTAMP    TYP VAL             
// [0][0][0][1] [0][0][0][1] [0][0][0][0] [0] [0][0][0][0][0]

		/**
		 * Constructor
		 * @return void 
		 */
		public function __construct() {
			$this->_typeId = 5;
			$this->_typeName = "Reading Post";
		}
		/**
		 * Parse the Reading Request
		 * @param array $array 
		 * @return bool 
		 */
		public function parse(array $array): bool {
			if (count($array) != 18) {
				print "Invalid Reading Post: ".count($array)." of 14 chars\n";
				$this->error("Invalid Reading Post: ".count($array)." of 14 chars");
				return false;
			}

			// Parse the Data
			app_log("Parse Reading Meta",'trace');
			$this->_assetId = ord($array[0])*256*256*256 + ord($array[1])*256*256 + ord($array[2])*256 + ord($array[3]);
			$this->_sensorId = ord($array[4])*256*256*256 + ord($array[5])*256*256 + ord($array[6])*256 + ord($array[7]);

			app_log("Parse Reading Timestamp",'trace');
			$this->_timestamp = $this->timestampFromBytes(array($array[8], $array[9], $array[10], $array[11]));

			app_log("Parse Reading Type",'trace');
			$this->valueType(ord($array[12]));		// Value Type 0 = Float, 1 = Int, 2 = String, 3 = Boolean

			$_control = ord($array[17]);			// Control Char (Exp or Negation)
			//print "Ctrl: ".$_control."\n";

			$this->_value = $this->floatFromBytes(array($array[16], $array[15], $array[14], $array[13]), $_control);
			//print "Value: ".$this->_value."\n";
			return true;
		}

		/**
		 * Build the Reading Request
		 * @param array $array 
		 * @return int 
		 */
		public function build(array &$array): int {
			// Build the data
			$meta = [];

			// 4 Byte Asset ID
			$id = $this->_assetId;
			$meta[0] = floor($id / (256*256*256));
			$id -= $meta[0] * 256*256*256;
			$meta[1] = floor($id / (256*256));
			$id -= $meta[1] * 256*256;
			$meta[2] = floor($id / 256);
			$meta[3] = $id % 256;

			// 4 Byte Sensor ID
			$id = $this->_sensorId;
			$meta[4] = floor($id / (256*256*256));
			$id -= $meta[4] * 256*256*256;
			$meta[5] = floor($id / (256*256));
			$id -= $meta[5] * 256*256;
			$meta[6] = floor($id / 256);
			$meta[7] = $id % 256;

			// 4 Bytes Timestamp
			$timeArray = $this->timestampToBytes($this->_timestamp);
			app_log("Timestamp: ".$this->_timestamp." -> ".ord($timeArray[0]).".".ord($timeArray[1]).".".ord($timeArray[2]).".".ord($timeArray[3]));
			$meta[8] = $timeArray[0];
			$meta[9] = $timeArray[1];
			$meta[10] = $timeArray[2];
			$meta[11] = $timeArray[3];

			// 1 Byte Value Type
			$meta[12] = ord($this->valueTypeChar());	// 0 = Float, 1 = Int, 2 = String, 3 = Boolean

			// 1 Byte Control Byte (Exp, Negation)
			$control = 0;
			$meta[13] = $control;

			// Value
			$valArray = [];
			$this->floatToBytes($this->_value, $valArray, $control);
			app_log("Float: ".$this->_value." -> ".ord($valArray[0]).".".ord($valArray[1]).".".ord($valArray[2]).".".ord($valArray[3])." Control: ".$control);
			$meta[14] = ord($valArray[0]);
			$meta[15] = ord($valArray[1]);
			$meta[16] = ord($valArray[2]);
			$meta[17] = ord($valArray[3]);

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
