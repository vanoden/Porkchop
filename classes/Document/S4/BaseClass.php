<?php
	namespace Document\S4;

	class BaseClass Extends \BaseClass {
		protected $_timestamp;			// Time of delivery
		protected $_assetId = 0;		// Asset ID to assocated message/reading with
		protected $_sensorId = 0;		// Sensor ID to associate message/reading with
		protected $_valueType = 0;		// Type of value being sent: float, int, string
		protected $_value = 0;			// Value being sent
	
		public function assetId(): int {
			return $this->_assetId;
		}

		public function sensorId(): int {
			return $this->_sensorId;
		}

		public function valueType(): int {
			return $this->_valueType;
		}

		public function value(): float {
			return $this->_value;
		}

		public function timestamp(): int {
			return $this->_timestamp;
		}
	}