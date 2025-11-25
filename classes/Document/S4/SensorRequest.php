<?php
	namespace Document\S4;

	class SensorRequest Extends \Document\S4\Message {

		public function __construct() {
			$this->_typeId = 22;
			$this->_typeName = "Sensor Request";
			$this->_sensorId = 0;
		}

		public function parse(array $array, $length = 0): bool {
			// Parse the Data
			$this->_sensorId = 0;
			app_log("Parsing $length bytes of data",'info');

			# Read the Sensor Code from the Request
			$code = "";
			for ($i = 0; $i < $length; $i++) {
				$code .= ord($array[$i]);
			}

			# Get the Sensor ID with the matching code
			$sensor = new \Monitor\Sensor();
			return true;
		}

		public function build(array &$array): int {
			// Build the data
			
			return count($array);
		}
	}