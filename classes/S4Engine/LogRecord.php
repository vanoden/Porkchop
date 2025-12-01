<?php
	namespace S4Engine;

	class LogRecord Extends \BaseModel {
		public $module = "s4engine";
		public int $_id = 0;
		public $_functionBytes = [0x00, 0x00];
		public $_clientBytes = [0x00, 0x00];
		public $_serverBytes = [0x00, 0x00];
		public $_lengthBytes = [0x00, 0x00];
		public $_sessionBytes = [0x00, 0x00, 0x00, 0x00];
		public $_contentBytes = [];
		public $_timeCreated;
		public $_remoteAddress = "";
		public bool $_successful = false;

		public function __construct($id = 0) {
			$this->clearError();
			$this->_id = $id;
			if ($id > 0) {
				$this->details();
			}
		}

		/** @method public function add($parameters = array()): bool
		 * Add a new log record to the database
		 * @param array $parameters
		 *  - functionBytes: byte array of length 2
		 *  - clientBytes: byte array of length 2
		 *  - serverBytes: byte array of length 2
		 *  - lengthBytes: byte array of length 2
		 *  - sessionBytes: byte array of length 4
		 *  - contentBytes: byte array of variable length
		 *  - checksum: byte array of length 2
		 * @return bool
		 */
		public function add($parameters = array()): bool {
			// Clear Previous Error
			$this->clearError();

			// Intialize Database Service
			$database = new \Database\Service();

			// Insert Record
			$insert_record_query = "
				INSERT INTO s4engine_log
				(	function_id,
					client_id,
					server_id,
					content_length,
					session_code,
					body,
					checksum,
					remote_address,
					time_created
				) VALUES (?,?,?,?,?,?,?,?,sysdate())
			";

			if (empty($parameters['remoteAddress'])) $parameters['remoteAddress'] = $_SERVER['REMOTE_ADDR'];

			app_log("Function Bytes (before): ".ord($parameters['functionBytes'][0]).",".ord($parameters['functionBytes'][1]),'info');
			app_log("Client Bytes (before): ".ord($parameters['clientBytes'][0]).",".ord($parameters['clientBytes'][1]),'info');
			app_log("Server Bytes (before): ".ord($parameters['serverBytes'][0]).",".ord($parameters['serverBytes'][1]),'info');
			app_log("Session Bytes (before): ".ord($parameters['sessionBytes'][0]).",".ord($parameters['sessionBytes'][1]).",".ord($parameters['sessionBytes'][2]).",".ord($parameters['sessionBytes'][3]),'info');
			app_log("Length Bytes (before): ".ord($parameters['lengthBytes'][0]).",".ord($parameters['lengthBytes'][1]),'info');
			$length = ord($parameters['lengthBytes'][0])*256 + ord($parameters['lengthBytes'][1]);
			$content = [];
			for ($i = 0; $i < $length; $i++) {
				$content[] = ord($parameters['contentBytes'][$i]);
			}
			app_log("Content Bytes (before): ".implode(",",$content),'info');
			app_log("Checksum Bytes (before): ".ord($parameters['checksum'][0]).",".ord($parameters['checksum'][1]).",'info");

			// Add Parameters
			$database->AddParamBinary($parameters['functionBytes'],2);
			$database->AddParamBinary($parameters['clientBytes'],2);
			$database->AddParamBinary($parameters['serverBytes'],2);
			$database->AddParamBinary($parameters['lengthBytes'],2);
			$database->AddParamBinary($parameters['sessionBytes'],4);
			$database->AddParamBinary($parameters['contentBytes'],ord($parameters['lengthBytes'][0])*256 + ord($parameters['lengthBytes'][1]));
			$database->AddParamBinary($parameters['checksum'],2);
			$database->AddParam($parameters['remoteAddress']);

			// Execute Query
			if (! $database->Execute($insert_record_query)) {
				$this->SQLError("Inserting S4Engine::Log record: ".$database->Error());
				return false;
			}

			// Get Inserted ID
			$this->_id = $database->Insert_ID();

			return true;
		}

		public function store(): bool {
			// Clear Previous Error
			$this->clearError();

			// Intialize Database Service
			$database = new \Database\Service();

			// Update Record
			$update_record_query = "
				INSERT
				INTO	s4engine_log
				(	function_id,
					client_id,
					server_id,
					content_length,
					session_code,
					body,
					remote_address,
					time_created
				)
				VALUES (?,?,?,?,?,?,?,sysdate())
			";

			// Add Parameters
			$database->AddParamBinary($this->_functionBytes,2);
			$database->AddParamBinary($this->_clientBytes,2);
			$database->AddParamBinary($this->_serverBytes,2);
			$database->AddParamBinary($this->_lengthBytes,2);
			$database->AddParamBinary($this->_sessionBytes,4);
			$length = ord($this->_lengthBytes[0])*256 + ord($this->_lengthBytes[1]);
			$database->AddParamBinary($this->_contentBytes,$length);
			$database->AddParam($_SERVER['REMOTE_ADDR']);

			// Execute Query
			$database->trace(9);
			$database->debug = 'log';
			if (! $database->Execute($update_record_query)) {
				$this->SQLError("Updating S4Engine::Log record: ".$database->Error());
				return false;
			}

			$this->_id = $database->Insert_ID();

			return true;
		}
		public function setFailure($errorMessage): bool {
			// Clear Previous Error
			$this->clearError();

			// Intialize Database Service
			$database = new \Database\Service();

			// Update Record
			$update_record_query = "
				UPDATE s4engine_log
				SET success = 0,
					error = ?
				WHERE id = ?
			";

			// Add Parameters
			$database->AddParam($errorMessage);
			$database->AddParam($this->_id);

			// Execute Query
			if (! $database->Execute($update_record_query)) {
				$this->SQLError("Updating S4Engine::Log record: ".$database->Error());
				return false;
			}

			return true;
		}

		public function setSuccess(): bool {
			// Clear Previous Error
			$this->clearError();

			// Intialize Database Service
			$database = new \Database\Service();
app_log("Setting log record ".$this->_id." to success",'info');
			// Update Record
			$update_record_query = "
				UPDATE s4engine_log
				SET success = 1
				WHERE id = ?
			";

			// Add Parameters
			$database->AddParam($this->_id);

			// Execute Query
			if (! $database->Execute($update_record_query)) {
				$this->SQLError("Updating S4Engine::Log record: ".$database->Error());
				return false;
			}

			return true;
		}

		public function getSuccess(): bool {
			return $this->_successful;
		}

		/** @method public function details()
		 * Load the details of this log record from the database
		 * @return bool
		 */
		public function details(): bool {
			// Clear Previous Error
			$this->clearError();

			// Intialize Database Service
			$database = new \Database\Service();

			// Get Details from Database
			$get_record_query = "
				SELECT *
				FROM 	s4engine_log
				WHERE 	id = ?
			";

			// Add Parameters
			$database->AddParam($this->_id);

			// Execute Query
			$rs = $database->Execute($get_record_query);
			if (! $rs) {
				$this->SQLError("Getting S4Engine::Log details: ".$database->Error());
				return false;
			}

			$object = $rs->FetchNextObject(false);
			if ($object) {
				$this->_id = $object->id;
				$this->_functionBytes = $object->function_id;
				$this->_clientBytes = $object->client_id;
				$this->_serverBytes = $object->server_id;
				$this->_lengthBytes = $object->length_bytes;
				$this->_sessionBytes = $object->session_code;
				$this->_lengthBytes = $object->content_length;
				$this->_contentBytes = $object->body;
				$this->_timeCreated = $object->time_created;
				$this->_remoteAddress = $object->remote_address;
				$this->error($object->error);
				$this->_successful = ($object->success == 1);
				if (empty($this->_contentBytes)) $this->_contentBytes = [];

				# Display Session as String of Integers
				$asInts = "";
				for ($i = 0; $i < 4; $i++) {
					$asInts.= "[".ord($this->_sessionBytes[$i])."]";
				}
				app_log("Session ".$object->id." Bytes (loaded): $asInts",'info');

				# Display Content as String of Integers
				$length = ord($this->_lengthBytes[0])*256 + ord($this->_lengthBytes[1]);
				$asInts = "";
				for ($i = 0; $i < $length; $i ++) {
					$asInts .= "[".ord($this->_contentBytes[$i])."]";
				}
				app_log("Content ".$object->id." Bytes (loaded): $asInts",'info');
				return true;
			} else {
				$this->error("S4Engine::Log record not found");
				return false;
			}
		}

		/** @method public functionName()
		 * Get the name of the function based on the function ID
		 * @return string
		 */
		public function functionName() {
			switch ($this->functionID()) {
				case 1: return "RegisterRequest"; break;
				case 2: return "RegisterResponse"; break;
				case 3: return "PingRequest"; break;
				case 4: return "PingResponse"; break;
				case 5: return "ReadingPost"; break;
				case 6: return "NotReady"; break;
				case 7: return "Acknowledgement"; break;
				case 8: return "UnknownClient"; break;
				case 9: return "FaultPost"; break;
				case 10: return "BadRequestResponse"; break;
				case 11: return "TimeRequest"; break;
				case 12: return "TimeResponse"; break;
				case 13: return "AuthRequest"; break;
				case 14: return "AuthResponse"; break;
				case 15: return "BumpTestPost"; break;
				case 16: return "CalVerifyPost"; break;
				case 17: return "UnparseableContent"; break;
				case 18: return "MessagePost"; break;
				case 19: return "Unauthorized"; break;
				case 20: return "SensorRequest"; break;
				case 21: return "SensorResponse"; break;
				default:     return "Unknown Function (".$this->functionID().")"; break;
			}
		}

		/** @method public id()
		 * Get the ID of this log record
		 * @return int
		 */
		public function id() {
			return $this->_id;
		}

		/** @method public timestamp()
		 * Get the timestamp of when the log record was created
		 * @return string (timestamp)
		 */
		public function timestamp() {
			return $this->_timeCreated;
		}

		/** @method public success()
		 * Get whether the log record indicates a successful operation
		 * @return bool
		 */
		public function success() {
			return $this->_successful;
		}

		/** @method public functionID()
		 * Get the decimal function ID from the function bytes
		 * @return int
		 */
		public function functionID() {
			return ord($this->_functionBytes[0])*256 + ord($this->_functionBytes[1]);
		}

		/** @method public functionBytes()
		 * Get the raw function bytes as an array
		 * @return array
		 */
		public function functionBytes($bytes = null): array {
			if ($bytes !== null) {
				$this->_functionBytes = $bytes;
			}
			return array(ord($this->_functionBytes[0]), ord($this->_functionBytes[1]));
		}

		/** @method public clientID()
		 * Get the decimal client ID from the client bytes
		 * @return int
		 */
		public function clientID() {
			return ord($this->_clientBytes[0])*256 + ord($this->_clientBytes[1]);
		}

		/** @method public function clientBytes()
		 * Get the raw client bytes as an array
		 * @return array
		 */
		public function clientBytes($bytes = null): array {
			if ($bytes !== null) {
				$this->_clientBytes = $bytes;
			}
			return array(ord($this->_clientBytes[0]), ord($this->_clientBytes[1]));
		}

		/** @method public function serverID()
		 * Get the decimal server ID from the server bytes
		 * @return int
		 */
		public function serverID() {
			return ord($this->_serverBytes[0])*256 + ord($this->_serverBytes[1]);
		}

		/** @method public function serverBytes()
		 * Get the raw server bytes as an array
		 * @return array
		 */
		public function serverBytes($bytes = null): array {
			if ($bytes !== null) {
				$this->_serverBytes = $bytes;
			}
			return array(ord($this->_serverBytes[0]), ord($this->_serverBytes[1]));
		}

		/** @method public function contentLength()
		 * Get the decimal content length from the length bytes
		 * @return int
		 */
		public function contentLength($length = null) {
			if ($length !== null) {
				$this->_lengthBytes = [chr(floor($length / 256)), chr($length % 256)];
			}
			return ord($this->_lengthBytes[0])*256 + ord($this->_lengthBytes[1]);
		}

		/** @method public function lengthBytes()
		 * Get the raw length bytes as an array
		 * @return array
		 */
		public function lengthBytes($bytes = null): array {
			if ($bytes !== null) {
				$this->_lengthBytes = $bytes;
			}
			return array(ord($this->_lengthBytes[0]), ord($this->_lengthBytes[1]));
		}

		/** @method public function contentBytes()
		 * Get/Set the raw content bytes as an array
		 * @return array
		 */
		public function contentBytes($bytes = null, $length = null): array {
			if ($bytes !== null) {
				$this->_contentBytes = $bytes;
				$this->contentLength($length);
			}
			$byteArray = [];
			for ($i = 0; $i < $this->contentLength(); $i++) {
				$byteArray[] = $this->_contentBytes[$i];
			}
			return $byteArray;
		}

		/** @method public contentDebug()
		 * Get the content bytes in a debug format (decimal bytes)
		 * @return string
		 */
		public function contentDebug() {
			$code = "";
			app_log("Content Bytes (debug): ".print_r($this->_contentBytes,true),'info');
			#if (!is_array($this->_contentBytes)) return "----";
			$length = $this->contentLength();
			for ($i = 0; $i < $length; $i++) {
				$code .= "[".ord($this->_contentBytes[$i])."]";
			}
			return $code;
		}

		/** @method public function sessionCode()
		 * Get the hexadecimal session code from the session bytes
		 * @return string
		 */
		public function sessionCode() {
			$code = "";
			#for ($i = 0; $i < count($this->_sessionBytes); $i++) {
			for ($i = 0; $i < 4; $i++) {
				$code .= dechex(ord($this->_sessionBytes[$i]));
			}
			return $code;
		}

		/** @method public function sessionBytes()
		 * Get the raw session bytes as an array
		 * @return array
		 */
		public function sessionBytes($bytes = null): array {
			if ($bytes !== null) {
				$this->_sessionBytes = $bytes;
				print "Heres the sessionBytes: ";
				for ($i = 0; $i < 4; $i++) {
					print "[".ord($this->_sessionBytes[$i])."]";
				}
				print "\n";
			}
			if (!is_array($this->_sessionBytes)) return [];
			$bytes = [];
			for ($i = 0; $i < count($this->_sessionBytes); $i++) {
				$bytes[] = ord($this->_sessionBytes[$i]);
			}
			return $bytes;
		}

		/** @method public function sessionCodeDebug()
		 * Get the session code in a debug format (decimal bytes)
		 * @return string
		 */
		public function sessionCodeDebug() {
			$code = "";
			app_log("Session Bytes (debug): ".print_r($this->_sessionBytes,true),'info');
			#if (!is_array($this->_sessionBytes)) return "----";
			for ($i = 0; $i < 4; $i++) {
				$code .= "[".ord($this->_sessionBytes[$i])."]";
			}
			return $code;
		}

		/** @method public function message()
		 * Get the S4Engine message object from the content bytes
		 * @return \Document\S4Message
		 */
		public function message() {
			$docFactory = new \Document\S4Factory();
			$message = $docFactory->get($this->functionID());
			if (! $message) {
				$this->error("Failed to get message type ".$this->functionID());
				return null;
			}
			$message->fromBytes($this->_contentBytes);
			return $message;
		}

		/** @method public function remoteAddress()
		 * Get the remote IP address from which the message was received
		 * @return string
		 */
		public function remoteAddress() {
			return $this->_remoteAddress;
		}

		/** @method public ints2bytes($intArray)
		 * Convert an array of integers (0-255) to a byte string
		 * @param array $intArray
		 * @return string String of Bytes
		 */
		public static function ints2bytes($intArray, $length = null): string {
			$byteString = "";;
			foreach ($intArray as $intValue) {
				$byteString .= chr($intValue);
			}
			if ($length !== null) {
				$byteString = substr($byteString, 0, $length);
			}
			return $byteString;
		}

		/** @method public bytes2ints($byteArray)
		 * Convert an array of bytes to an array of integers (0-255)
		 * @param string $byteArray
		 * @return array
		 */
		public static function bytes2ints($byteArray, $length = null) {
			$intArray = [];
			for ($i = 0; $i < strlen($byteArray); $i++) {
				$byteValue = substr($byteArray,$i,1);
				$intArray[] = ord($byteValue);
			}
			if ($length !== null) {
				$intArray = array_slice($intArray, 0, $length);
			}
			return $intArray;
		}
	}
