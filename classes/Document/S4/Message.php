<?php
	namespace Document\S4;

	/**
	 * Base class for Document::S4 Messages
	 */
	class Message Extends \BaseClass {
		protected $_typeId = 0;				// ID of the message type
		protected $_clientId = 0;		// ID of the client sending request, receiving response
		protected $_serverId = 0;		// ID of the server receiving request, sending response
		protected $_timestamp;			// Time of delivery
		protected $_assetId = 0;		// Asset ID to assocated message/reading with
		protected $_sensorId = 0;		// Sensor ID to associate message/reading with
		protected $_valueType = 0;		// Type of value being sent: float, int, string
		protected $_value = 0;			// Value being sent
		protected $_typeName = "";		// Type of message in Readable Format

		/**
		 * Get/Set Client ID
		 * @param mixed $value 
		 * @return int id
		 */
		public function clientId($value = null): int {
			if ($value !== null) {
				$this->_clientId = $value;
			}
			return $this->_clientId;
		}

		/**
		 * Get/Set Server ID
		 * @param mixed $value 
		 * @return int id
		 */
		public function serverId($value = null): int {
			if ($value !== null) {
				$this->_serverId = $value;
			}
			return $this->_serverId;
		}

		/**
		 * Get/Set Asset ID
		 * @param mixed $value 
		 * @return int id
		 */
		public function assetId($value = null): int {
			if ($value !== null) {
				$this->_assetId = $value;
			}
			return $this->_assetId;
		}

		/**
		 * Get/Set Sensor ID
		 * @param mixed $value 
		 * @return int id
		 */
		public function sensorId($value = null): int {
			if ($value !== null) {
				$this->_sensorId = $value;
			}
			return $this->_sensorId;
		}

		public function content(): string {
			return "You got this";
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
	
		public function typeName(): string {
			return $this->_typeName;
		}
	}
