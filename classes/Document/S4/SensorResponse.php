<?php
	namespace Document\S4;

	class SensorResponse Extends \Document\S4\Message {

		public function __construct() {
			$this->_typeId = 23;
			$this->_typeName = "Sensor Response";
			$this->_sensorId = 0;
		}

		public function parse(array $array, $length = 0): bool {
			// Parse the Data
			$sensorId = "";
			$sensorIdBytes = "";
			$sensorModelId = "";
			$sensorModelIdBytes = "";

			for ($i = 0; $i < 4; $i++) {
				$sensorIdBytes .= $array[$i];
			}
			$sensorId = ord($sensorIdBytes [0]) << 24 | ord($sensorIdBytes [1]) << 16 | ord($sensorIdBytes [2]) << 8 | ord($sensorIdBytes [3]);

			for ($i = 4; $i < 6; $i++) {
				$sensorModelIdBytes .= $array[$i];
			}
			$sensorModelId = ord($sensorModelIdBytes [0]) << 8 | ord($sensorModelIdBytes [1]);			
			return true;
		}

		public function build(array &$array): int {
			// Build the data
			$sensorId = 0;			# Need to get actual value from db
			$sensorModelId = 0;		# Need to get actual value from db

			$array[] = chr(($sensorId >> 24) & 0xFF);
			$array[] = chr(($sensorId >> 16) & 0xFF);
			$array[] = chr(($sensorId >> 8) & 0xFF);
			$array[] = chr($sensorId & 0xFF);
			$array[] = chr(($sensorModelId >> 8) & 0xFF);
			$array[] = chr($sensorModelId & 0xFF);
			return count($array);
		}
	}