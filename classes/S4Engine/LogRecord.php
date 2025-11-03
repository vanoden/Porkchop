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

			if (empty($parameters['functionBytes'])) $parameters['functionBytes'] = [0x00, 0x00];
			if (empty($parameters['clientBytes'])) $parameters['clientBytes'] = [0x00, 0x00];
			if (empty($parameters['serverBytes'])) $parameters['serverBytes'] = [0x00, 0x00];
			if (empty($parameters['lengthBytes'])) $parameters['lengthBytes'] = [0x00, 0x00];
			if (empty($parameters['sessionBytes'])) $parameters['sessionBytes'] = [0x00, 0x00, 0x00, 0x00];
			if (empty($parameters['contentBytes'])) $parameters['contentBytes'] = [];
			if (empty($parameters['checksum'])) $parameters['checksum'] = [0x00, 0x00];
			if (empty($parameters['remoteAddress'])) $parameters['remoteAddress'] = $_SERVER['REMOTE_ADDR'];

			$parameters['clientBytes'][0] = dechex(ord($parameters['clientBytes'][0]));
			$parameters['clientBytes'][1] = dechex(ord($parameters['clientBytes'][1]));
			app_log("Client Bytes: ".implode(",",$parameters['clientBytes']),'info');
			$parameters['serverBytes'][0] = dechex(ord($parameters['serverBytes'][0]));
			$parameters['serverBytes'][1] = dechex(ord($parameters['serverBytes'][1]));
			app_log("Server Bytes: ".implode(",",$parameters['serverBytes']),'info');
			app_log("Session Bytes: ".implode(",",$parameters['sessionBytes']),'info');
			for ($i = 0; $i < 4; $i++) {
				#$parameters['sessionBytes'][$i] = dechex(ord($parameters['sessionBytes'][$i]));
				$parameters['sessionBytes'][$i] = hex2bin(dechex($parameters['sessionBytes'][$i]));
			}
			app_log("Session Bytes (after): ".implode(",",$parameters['sessionBytes']),'info');

			// Add Parameters
			$database->AddParamBinary($parameters['functionBytes']);
			$database->AddParamBinary($parameters['clientBytes']);
			$database->AddParamBinary($parameters['serverBytes']);
			$database->AddParamBinary($parameters['lengthBytes']);
			$database->AddParam($parameters['sessionBytes']);
			$database->AddParamBinary($parameters['contentBytes']);
			$database->AddParamBinary($parameters['checksum']);
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

			if ($object = $rs->FetchNextObject(false)) {
				$this->_id = $object->id;
				$this->_functionBytes = $object->function_id;
				$this->_clientBytes = $object->client_id;
				$this->_serverBytes = $object->server_id;
				$this->_lengthBytes = $object->length_bytes;
				$this->_sessionBytes = array_values(unpack("C*", $object->session_code));
				$this->_lengthBytes = $object->content_length;
				$this->_contentBytes = $object->content_bytes;
				$this->_timeCreated = $object->time_created;
				$this->_remoteAddress = $object->remote_address;
				$this->error($object->error);
				$this->_successful = ($object->success == 1);
				if (empty($this->_contentBytes)) $this->_contentBytes = [];
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
		public function functionBytes() {
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
		public function clientBytes() {
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
		public function serverBytes() {
			return array(ord($this->_serverBytes[0]), ord($this->_serverBytes[1]));
		}

		/** @method public function contentLength()
		 * Get the decimal content length from the length bytes
		 * @return int
		 */
		public function contentLength() {
			return ord($this->_lengthBytes[0])*256 + ord($this->_lengthBytes[1]);
		}

		/** @method public function lengthBytes()
		 * Get the raw length bytes as an array
		 * @return array
		 */
		public function lengthBytes() {
			return array(ord($this->_lengthBytes[0]), ord($this->_lengthBytes[1]));
		}

		/** @method public function contentBytes()
		 * Get the raw content bytes as an array
		 * @return array
		 */
		public function contentBytes() {
			$bytes = [];
			for ($i = 0; $i < count($this->_contentBytes); $i++) {
				$bytes[] = ord($this->_contentBytes[$i]);
			}
			return $bytes;
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
		public function sessionBytes() {
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
			if (!is_array($this->_sessionBytes)) return "----";
			for ($i = 0; $i < count($this->_sessionBytes); $i++) {
				if ($i > 0) $code .= ".";
				$code .= ord($this->_sessionBytes[$i]);
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
			$message->fromByteArray($this->_contentBytes);
			return $message;
		}

		/** @method public function remoteAddress()
		 * Get the remote IP address from which the message was received
		 * @return string
		 */
		public function remoteAddress() {
			return $this->_remoteAddress;
		}
	}