<?php
	namespace Document\S4;

	enum ValueType {
		case Float;
		case Int;
		case String;
		case Boolean;
	}

	/**
	 * Base class for Document::S4 Messages
	 */
	abstract class Message Extends \BaseClass {
		protected int $_typeId = 0;							// ID of the message type
		protected ?int $_timestamp = null;						// Time of delivery
		protected int $_assetId = 0;						// Asset ID to assocated message/reading with
		protected int $_sensorId = 0;						// Sensor ID to associate message/reading with
		protected ValueType $_valueType = ValueType::Int;	// Type of value being sent: float, int, string
		protected $_value = 0;								// Value being sent
		protected string $_typeName = "";					// Type of message in Readable Format
		protected $_serialNumber = "";
		protected $_modelNumber = "";
		protected $_success = false;

		/**
		 * Definition for parse method
		 * @param array $array
		 */
		abstract public function parse(array $array): bool;

		/**
		 * Definition for build method
		 * @param array &$array
		 * @return int Number of bytes written
		 */
		abstract public function build(array &$array): int;

		/**
		 * Get/Set Asset ID
		 * @param mixed $value|null
		 * @return int id
		 */
		public function assetId(int $value = null): ?int {
			if ($value !== null) {
				$this->_assetId = $value;
			}
			return $this->_assetId;
		}

		/**
		 * Get/Set Sensor ID
		 * @param mixed $value|null
		 * @return int id
		 */
		public function sensorId($value = null): ?int {
			if ($value !== null) {
				$this->_sensorId = $value;
			}
			return $this->_sensorId;
		}

		/**
		 * Get/Set the content of this message
		 * @param string $string|null
		 * @return string
		 */
		public function content($string = null): string {
			if (isset($string)) {
				$this->_value = $string;
			}
			return $this->_value;
		}

		/**
		 * Get the value type of this message
		 * @param int $typeId|null
		 * @return ValueType
		 */
		public function valueType(int $typeId = null): ValueType {
			if ($typeId !== null) {
				switch($typeId) {
					case 0:
						$this->_valueType = ValueType::Float;
						break;
					case 1:
						$this->_valueType = ValueType::Int;
						break;
					case 2:
						$this->_valueType = ValueType::String;
						break;
					default:
						$this->_valueType = ValueType::Int;
				}
			}
			return $this->_valueType;
		}

		/**
		 * Get the character representation of the value type
		 * @return string
		 */
		public function valueTypeChar(): string {
			switch ($this->_valueType) {
				case ValueType::Float:
					return chr(0);
				case ValueType::Int:
					return chr(1);
				case ValueType::String:
					return chr(2);
			}
		}

		/**
		 * Get the content of this message
		 * @return float Value of message
		 */
		public function value($value = null) {
			if ($value !== null) {
				$this->_value = $value;
			}
			return $this->_value;
		}

		/**
		 * Get/Set the Serial Number
		 * @param string $value|null
		 * @return string
		 */
		public function serialNumber($value = null) {
			if ($value !== null) {
				$this->_serialNumber = $value;
			}
			return $this->_serialNumber;
		}

		/**
		 * Get/Set the Model Number
		 * @param mixed $value|null
		 * @return string
		*/
		public function modelNumber($value = null) {
			if ($value !== null) {
				$this->_modelNumber = $value;
			}
			return $this->_modelNumber;
		}

		/**
		 * Get the timestamp of this message
		 * @return int Unix timestamp
		 */
		public function timestamp(int $timestamp = null): ?int {
			if ($timestamp !== null) {
				$this->_timestamp = $timestamp;
			}
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

		/**
		 * Get/Set the success status of this message
		 * @param bool $value|null
		 * @return bool
		 */
		public function success(bool $value = null): bool {
			if (!is_null($value)) {
				app_log("Setting success to $value",'info');
				$this->_success = $value;
			}
			return $this->_success;
		}

		/**
		 * Print the individual characters of a string for diagnostics
		 * @param mixed $string
		 */
		public function arrayPrint($string) {
			for ($i = 0 ; $i < strlen($string) ; $i++) {
				print "[".ord(substr($string,$i,1))."]";
			}
		}

		/**
		 * Return a number from a byte array
		 * @param mixed $charArray char array
		 * @return float Value
		 */
		protected function floatFromBytes($charArray,$controlChar = 0): float {
			$sigFigs = 7;
			$value = (ord($charArray[0]) * (256 * 256 * 256)) + (ord($charArray[1]) * (256 * 256)) + (ord($charArray[2]) * 256) + ord($charArray[3]);
			$mult = pow(10,$controlChar - $sigFigs);
			if ($controlChar > 128) {
				$controlChar -= 128;
				$value = -1 * $value;
			}
			$value -= 2147483648;
			$value *= $mult;
			return $value;
		}

		/**
		 * Return the UTC timestamp from a byte array
		 * @param mixed $charArray char array
		 * @return int number of bytes
		 */
		protected function floatToBytes(float $value, &$charArray, &$controlChar): int {
			$length = 4;
			$originalValue = $value;
			if ($value < 0) {
				$controlChar += 128;
				$value = abs($value);
			}
			if ($value > 999999999) {
				// Leave control char as is
			}
			elseif ($value > 99999999) {
				$controlChar += 1;
				$value *= 10;
			}
			elseif ($value > 9999999) {
				$controlChar += 2;
				$value *= 100;
			}
			elseif ($value > 999999) {
				$controlChar += 3;
				$value *= 1000;
			}
			elseif ($value > 99999) {
				$controlChar += 4;
				$value *= 10000;
			}
			elseif ($value > 9999) {
				$controlChar += 5;
				$value *= 100000;
			}
			elseif ($value > 999) {
				$controlChar += 6;
				$value *= 1000000;
			}
			elseif ($value > 99) {
				$controlChar += 7;
				$value *= 10000000;
			}
			elseif ($value > 9) {
				$controlChar += 8;
				$value *= 100000000;
			}
			else {
				$controlChar += 9;
				$value *= 1000000000;
			}

			$charArray[0] = chr(floor($value / (256 * 256 * 256)));
			$charArray[1] = chr(floor($value / (256 * 256)));
			$charArray[2] = chr(floor($value / 256));
			$charArray[3] = chr($value % 256);
			app_log("Float: $originalValue -> $value -> ".ord($charArray[0]).".".ord($charArray[1]).".".ord($charArray[2]).".".ord($charArray[3])." Control: $controlChar");
			$gotBack = $this->floatFromBytes($charArray, $controlChar);
			app_log("Restores as $gotBack");
			return $length;
		}

		/**
		 * Return the UTC timestamp from a byte array
		 * @param mixed $charArray char array
		 * @return int UTC timestamp
		 */
		protected function timestampFromBytes($charArray): int {
			$timestamp = ord($charArray[0]) * (256 * 256 * 256) + ord($charArray[1]) * (256 * 256) + ord($charArray[2]) * 256 + ord($charArray[3]);
			return $timestamp;
		}

		/**
		 * Convert a timestamp to a byte array
		 * @param int $timestamp
		 * @return string
		 */
		protected function timestampToBytes(int $timestamp): array {
			app_log("Timestamp: $timestamp");
			$array[0] = chr(floor($timestamp / (256 * 256 * 256)));
			$array[1] = chr(floor($timestamp / (256 * 256)));
			$array[2] = chr(floor($timestamp / 256));
			$array[3] = chr($timestamp % 256);
			//$gotback = $this->timestampFromBytes($array);
			//app_log("Got Back Timestamp: $timestamp -> ".ord($array[0]).".".ord($array[1]).".".ord($array[2]).".".ord($array[3])." -> $gotback");
			return $array;
		}

		/**
		 * Pad a string to a given length with null chars
		 * @param string $string
		 * @param int $length
		 * @return string
		 */
		protected function padString($string, $length): string {
			$pad = $length - strlen($string);
			if ($pad > 0) {
				$string .= str_repeat(chr(0),$pad);
			}
			return $string;
		}

		/**
		 * Declare virtual methods
		 */
		public function login(string $string = null): string {
			return "";
		}
		public function password(string $string = null): string {
			return "";
		}
	}
