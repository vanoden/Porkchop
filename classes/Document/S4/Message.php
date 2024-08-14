<?php
	namespace Document\S4;

	/**
	 * Base class for Document::S4 Messages
	 */
	class Message Extends \BaseClass {
		protected $_typeId = 0;			// ID of the message type
		protected $_timestamp;			// Time of delivery
		protected $_assetId = 0;		// Asset ID to assocated message/reading with
		protected $_sensorId = 0;		// Sensor ID to associate message/reading with
		protected $_valueType = 0;		// Type of value being sent: float, int, string
		protected $_value = 0;			// Value being sent
		protected $_typeName = "";		// Type of message in Readable Format

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

		public function content($string = null): string {
			if (isset($string)) {
				$this->_value = $string;
			}
			return $this->_value;
		}

		public function valueType(): int {
			return $this->_valueType;
		}

		/**
		 * Get the content of this message
		 * @return float Value of message
		 */
		public function value(): float {
			return $this->_value;
		}

		/**
		 * Get the timestamp of this message
		 * @return int Unix timestamp
		 */
		public function timestamp(): int {
			return $this->_timestamp;
		}

		/**
		 * Get the type id of this message
		 * @return int Unique ID for type
		 */
		public function typeId(): int {
			return $this->_typeId;
		}

		/**
		 * Get the type name of this message
		 * @return string Human readable name of type
		 */
		public function typeName(): string {
			return $this->_typeName;
		}

		public function arrayPrint($string) {
			for ($i = 0 ; $i < strlen($string) ; $i++) {
				print "[".ord(substr($string,$i,1))."]";
			}
		}
	}
