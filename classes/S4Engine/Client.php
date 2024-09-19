<?php
	namespace S4Engine;
	
	/**
	 * Client Object
	 */
	class Client Extends \BaseModel {
		public int $_number = 0;				// Client Number
		public string $_serialNumber = "";	// Serial Number for Client Device
		public string $_modelNumber = "";	// Model Number for Client Device
		public ?int $userId = 0;				// Register::Customer ID

		/**
		 * Constructor
		 * @param int $id 
		 * @return void 
		 */
		public function __construct($id = 0) {
			$this->_tableName = "s4engine_clients";
			$this->_tableIDColumn = "id";
			$this->_tableUKColumn = "number";
			$this->_addFields("number","serial_number","model_number","user_id");

			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		/**
		 * Add a new client to the database
		 * @param array $params Parameters for the new client
		 * @return bool
		 */
		public function add($params = []): bool {
			$this->clearError();

			$database = new \Database\Service();

			if (empty($params["number"])) {
				$params["number"] = $this->generateNumber();
			}

			$add_object_query = "
				INSERT
				INTO s4engine_clients
				(number,serial_number,model_number)
				VALUES (?,?,?)
			";

			$database->AddParam($params["number"]);
			$database->AddParam($params["serial_number"]);
			$database->AddParam($params["model_number"]);

			$database->Execute($add_object_query);
			if ($database->error()) {
				$this->error($database->error());
				return false;
			}
			$this->id = $database->Insert_ID();
			app_log("Added client ".$this->id.": ".$params["number"],'info');
			return true;
		}

		/**
		 * Update the client in the database
		 * @param array $params Parameters for the client
		 * @return bool
		 */
		public function update($params = []): bool {
			$this->clearError();

			$database = new \Database\Service();

			$update_object_query = "
				UPDATE	s4engine_clients
				SET		id = id
			";

			$update_object_query .= "
				WHERE	id = ?
			";
			$database->AddParam($this->id);

			$database->Execute($update_object_query);
			if ($database->error()) {
				$this->error($database->error());
				return false;
			}
			return $this->details();
		}

		/**
		 * Get the client details from the database
		 * @return bool
		 */
		public function details(): bool {
			$this->clearError();

			$database = new \Database\Service();

			$get_object_query = "
				SELECT	*
				FROM	s4engine_clients
				WHERE	id = ?
			";
			$database->AddParam($this->id);
			$rs = $database->Execute($get_object_query);
			if (! $rs) {
				$this->error("Error getting client: ".$database->error());
				return false;
			}
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
			}
			$object = $rs->FetchNextObject(false);
			if (!empty($object->id)) {
				$this->_number = $object->number;
				$this->_serialNumber = $object->serial_number;
				$this->_modelNumber = $object->model_number;
				return true;
			}
			else {
				$this->error("Client not found");
				return false;
			}
		}

		/**
		 * Get/Set the Client Id
		 * @param int|null $id
		 * @return int
		 */
		public function id(int $id = null): int {
			if (!is_null($id)) {
				$this->id = $id;
			}
			return $this->id;
		}

		/**
		 * Get the client by the client number
		 * @param int $number
		 * @return bool
		 */
		public function get(int $number): bool {
			$this->clearError();

			$database = new \Database\Service();

			$get_object_query = "
				SELECT	id
				FROM	s4engine_clients
				WHERE	number = ?
			";
			$database->AddParam($number);
			$rs = $database->Execute($get_object_query);
			if (! $rs) {
				$this->error("Error getting client: ".$database->error());
				return false;
			}
			if ($rs->Rows() > 0) {
				$object = $rs->FetchNextObject();
				$this->id = $object->id;
				return $this->details();
			}
			else {
				$this->error("Client not found");
				return false;
			}
		}

		/**
		 * Get/Set the Client Number
		 * @param int|null $number
		 * @return int
		 */
		public function number(int $number = null): int {
			if (!is_null($number)) {
				$this->update(array("number" => $number));
			}
			return $this->_number;
		}

		/**
		 * Accept/Return 2 byte Client Code
		 * @return array 2 Byte Client Code
		 */
		public function codeArray(array $code = null): array {
			if (!is_null($code)) {
				$this->_number = $code[0] * 256 + $code[1];
			}
			$return = array(floor($this->_number / 256),$this->_number % 256);
			return $return;
		}

		/**
		 * Accept/Return 2 Char Client Code
		 * @return string 2 Char Client Code
		 */
		public function codeString(string $code = null): string {
			if (!is_null($code)) {
				$this->_number = ord($code[0]) * 256 + ord($code[1]);
			}
			$return = chr(floor($this->_number / 256)).chr($this->_number % 256);
			return $return;
		}

		/**
		 * Code as a string of hex values
		 * @return string 
		 */
		public function codeHex(): string {
			$code = $this->codeArray();
			$return = "";
			for ($i = 0; $i < 2; $i ++) {
				$return .= dechex($code[$i]);
			}
			return $return;
		}

		/**
		 * Code as a string of bracketed ord values for debugging
		 * @return string 
		 */
		public function codeDebug(): string {
			$code = $this->codeArray();
			$return = "";
			for ($i = 0; $i < 2; $i ++) {
				$return .= "[".$code[$i]."]";
			}
			return $return;
		}

		/**
		 * Generate a Unique Client Number
		 * @return int 
		 */
		public function generateNumber(): int {
			$database = new \Database\Service();
			$number = 0;
			while (true) {
				$number = rand(0,65535);
				$check_query = "
					SELECT	count(*)
					FROM	s4engine_clients
					WHERE	number = ?
				";
				$database->Prepare($check_query);
				$database->AddParam($number);
				$rs = $database->Execute($check_query);
				if ($database->error()) {
					$this->error($database->error());
					return -1;
				}
				list($found) = $rs->FetchRow();
				app_log("Generated Client Number: ".$number." Rows: ".$found,'info');
				if (! $found) break;
			}
			return $number;
		}
	}