<?php
	namespace S4Engine;

	/**
	 * Session Object
	 */
	class Session Extends \BaseModel {
		public int $_number = 0;						// Session Number
		public int $_startTime = 0;						// Start Time as Unix Timestamp
		public int $_endTime = 0;						// End Time as Unix Timestamp
		public int $_clientId = 0;						// Client ID
		public int $_portalSessionId = 0;				// Portal Session ID

		/**
		 * Constructor
		 * @param int $id 
		 * @return void 
		 */
		public function __construct(int $id = 0) {
			$this->id = $id;
			if ($id > 0) return $this->details();
			else {
				$client = new \S4Engine\Client();
				$this->_clientId = $client->id();
			}
		}

		/**
		 * Get/Set the Session Id
		 * @param int|null $id 
		 * @return int 
		 */
		public function id(int $id = null): int {
			if ($id) {
				$this->id = $id;
			}
			return $this->id;
		}

		/**
		 * Initialize the session
		 * @param int $clientId
		 * @return int New Session ID
		 */
		public function add($params = []): bool {
			$this->clearError();

			$database = new \Database\Service();

			// Initialize Client Object
			if (!empty($params['client_id'])) {
				$client = new \S4Engine\Client($params['client_id']);
			}
			elseif (!empty($params['client'])) {
				$client = $params['client'];
			}
			else {
				$this->error("Client ID required to create session");
				return false;
			}

			// Create Portal Session!
			$portalSession = new \Site\Session();
			$portalSession->start();
			if ($portalSession->error()) {
				$this->error("Error creating portal session: ".$portalSession->error());
				return false;
			}
			$this->_portalSessionId = $portalSession->id();

			// Generate a new Session Number
			while(true) {
				// Generate a Random Number
				$number = rand(1,4290000000);

				// Make sure ID not already used
				if (!$this->checkSession($client->id(),$number)) {
					$this->_number = $number;
					break;
				}
			}

			$add_object_query = "
				INSERT INTO s4engine_sessions
				(client_id,number,time_start,time_end,portal_id)
				VALUES (?,?,?,?,?)
			";
			$database->AddParam($client->id());
			$database->AddParam($this->_number);
			$database->AddParam(get_mysql_date('now'));
			$database->AddParam(get_mysql_date('+1 day'));
			$database->AddParam($this->_portalSessionId);

			if (! $database->Execute($add_object_query)) {
				$this->error("Error adding session: ".$database->error());
				return false;
			}
			$this->id = $database->Insert_ID();
			return $this->update($params);
		}

		/**
		 * Update the Session
		 * @params array $params
		 * @return bool
		 */
		public function update($params = []): bool {
			$this->clearError();

			$database = new \Database\Service();

			$update_object_query = "
				UPDATE	s4engine_sessions
				SET		id = id";

			if (!empty($params['time_end']) && get_mysql_date($params['time_end'])) {
				$update_object_query .= ",
				time_end = ?";
				$database->AddParam(get_mysql_date($params['time_end']));
			}
			elseif (!empty($params['time_end'])) {
				$this->error("Invalid time_end parameter");
				return false;
			}

			if (!empty($params['client_id']) && is_numeric($params['client_id'])) {
				$update_object_query .= ",
				client_id = ?";
				$database->AddParam($params['client_id']);
			}
			elseif (!empty($params['client_id'])) {
				$this->error("Invalid client_id parameter");
				return false;
			}

			if (!empty($params['client']) && $params['client'] instanceof \S4Engine\Client) {
				$client = new \S4Engine\Client();
				if (! $client->load($params['client']->codeArray())) {
					$this->error("Client not found");
					return false;
				}

				$update_object_query .= ",
				client_id = ?";
				$database->AddParam($params['client']->id());
			}
			elseif (!empty($params['client'])) {
				$this->error("Invalid client parameter");
				return false;
			}

			$update_object_query .= "
				WHERE	id = ?
			";
			$database->AddParam($this->id());

			$database->Execute($update_object_query);
			if ($database->error()) {
				$this->error("Error updating session: ".$database->error());
				return false;
			}
			return $this->details();
		}

		/**
		 * Get the Session Details
		 * @return bool
		 */
		public function details(): bool {
			$this->clearError();

			$database = new \Database\Service();

			$get_object_query = "
				SELECT	*
				FROM	s4engine_sessions
				WHERE	id = ?
			";
			$database->AddParam($this->id());

			$rs = $database->Execute($get_object_query);
			if (! $rs) {
				$this->error("Error getting session details: ".$database->error());
				return false;
			}

			$object = $rs->FetchNextObject();
			if ($object) {
				$this->_number = $object->number;
				$this->_startTime = strtotime($object->time_start);
				$this->_endTime = strtotime($object->time_end);
				$this->_clientId = $object->client_id;
				$this->_portalSessionId = $object->portal_id;
			}
			else {
				$this->_number = 0;
				$this->_startTime = 0;
				$this->_endTime = 0;
				$this->_clientId = 0;
				$this->_portalSessionId = 0;
			}
			return true;
		}

		/**
		 * Get the Session
		 * @param array $clientCode
		 * @param array $sessionCode
		 * @return bool
		 */
		public function getSession(int $clientId, array $sessionCode): bool {
			$this->clearError();

			$database = new \Database\Service();

			$get_object_query = "
				SELECT	id
				FROM	s4engine_sessions
				WHERE	client_id = ? AND number = ?
			";
			$database->AddParam($clientId);
			$database->AddParam($sessionCode[0]*256*256*256+$sessionCode[1]*256*256+$sessionCode[2]*256+$sessionCode[3]);
app_log("Getting session with client id: ".$clientId." and session code: ".$sessionCode[0]*256*256*256+$sessionCode[1]*256*256+$sessionCode[2]*256+$sessionCode[3],'info');
			$rs = $database->Execute($get_object_query);
			if (! $rs) {
				$this->error("Error getting session: ".$database->error());
				return false;
			}
			$object = $rs->FetchNextObject();
			if (!empty($object->id)) {
				$this->id = $object->id;
				return $this->details();
			}
			else {
				return false;
			}
		}

		/**
		 * Check if a session exists
		 * @param int $clientId
		 * @param int $sessionNumber
		 * @return bool
		 */
		public function checkSession(int $clientId, int $sessionNumber): bool {
			$this->clearError();

			$database = new \Database\Service();

			$get_object_query = "
				SELECT	id
				FROM	s4engine_sessions
				WHERE	client_id = ? AND number = ?
			";
			$database->AddParam($clientId);
			$database->AddParam($sessionNumber);
			$rs = $database->Execute($get_object_query);
			if (! $rs) {
				$this->error("Error checking session: ".$database->error());
				return false;
			}
			$object = $rs->FetchNextObject();
			if (!empty($object->id)) {
				return true;
			}
			else {
				return false;
			}
		}

		/**
		 * Get the Client Object for the session
		 * @return \S4Engine\Client
		 */
		public function client(\S4Engine\Client $client = null): ?\S4Engine\Client {
			if (!is_null($client)) {
				app_log("Setting client for session ".$this->id().": ".$client->id(),'info');
				$this->_clientId = $client->id();
			}
			$client = new \S4Engine\Client($this->_clientId);
			return $client;
		}

		/**
		 * Get Portal Session Details
		 * @return \Site\Session
		 */
		public function portalSession(): \Site\Session {
			$session = new \Site\Session($this->_portalSessionId);
			return $session;
		}

		/**
		 * Authenticate the session using provided login and password
		 * @param mixed $login 
		 * @param mixed $password 
		 * @return bool 
		 */
		public function authenticate($login,$password): bool {
			$customer = new \Register\Customer();
			if ($customer->authenticate($login,$password)) {
				app_log("Assigning customer id ".$customer->id()." to portal session ".$this->session()->id(),'info');
				$this->session()->assign($customer->id());
				return true;
			}
			return false;
		}

		/**
		 * Get/Set the client for the session
		 * @param \S4Engine\Client $client
		 */
		public function clientId(int $clientId): int {
			app_log("Setting client for session ".$this->id().": ".$clientId,'info');
			$this->_clientId = $clientId;
			return $this->_clientId;
		}

		/**
		 * Get/Set The User Id
		 * @param int $userId
		 * @return int
		 */
		public function userId(int $userId = null): ?int {
			//if ($userId) {
			//	$this->client()->update(array('user_id' => $userId));
			//}
			return $this->portalSession()->customer()->id();
		}

		/**
		 * Return a summary of the session details
		 * @return string
		 */
		public function summary(): string {
			$return  = "---Session Summary---\n\tID: ".$this->id()."\n";
			$return .= "\tCode: ".$this->codeDebug()."\n";
			$return .= "\tClient: ".$this->client()->codeDebug()."\n";
			$return .= "\tStarted: ".date("Y-m-d H:i:s",$this->_startTime)."\n";
			return $return;
		}

		/**
		 * Code as String
		 * @param string $code
		 * @return string
		 */
		public function codeString(string $code = null): string {
			if (!is_null($code)) {
				$this->_number = ord(substr($code,0,1)) * 256 + ord(substr($code,1,1));
			}
			for ($i = 0; $i < 4; $i ++) {
				$code  = chr(floor($this->_number / (256*256*256)));
				$code .= chr(floor($this->_number / (256*256)));
				$code .= chr(floor($this->_number / 256));
				$code .= chr($this->_number % 256);
			}
			return $code;
		}

		/**
		 * Code as an Array
		 * @param array $code
		 * @return array
		 */
		public function codeArray(array $code = null): array {
			if (!is_null($code)) {
				$this->_number = ($code[0] * 256 * 256 * 256) + ($code[1] * 256 * 256) + ($code[2] * 256) + ($code[3]);
			}
			if ($this->_number == 0) {
				return array(0,0,0,0);
			}
			else {
				$id = $this->_number;
				$arr[0] = floor($id / (256*256*256));
				$id -= $arr[0] * 256 * 256 * 256;
				$arr[1] = floor($id / (256*256));
				$id -= $arr[1] * 256 * 256;
				$arr[2] = floor($id / 256);
				$arr[3] = $id % 256;
				return $arr;
			}
		}

		/**
		 * Code as string of hex values
		 * @return string
		 */
		public function codeHex(): string {
			$code = $this->codeArray();
			$return = "";
			for ($i = 0; $i < 4; $i ++) {
				$return .= dechex($code[$i]);
			}
			return $return;
		}

		/**
		 * Code as string of bracketed ord values for debugging
		 * @return string
		 */
		public function codeDebug(): string {
			$code = $this->codeArray();
			$return = "";
			for ($i = 0; $i < count($code); $i ++) {
				$return .= "[".$code[$i]."]";
			}
			return $return;
		}

		/**
		 * Key as String of byte values
		 * @return string
		 */
		public function keyString(): string {
			$key = $this->client()->codeString().$this->codeString();
			return $key;
		}

		/**
		 * Key as Array of byte values
		 * @return array
		 */
		public function keyArray(): array {
			$key = array_merge($this->client()->codeArray(),$this->codeArray());
			return $key;
		}

		/**
		 * Key as string of hex values
		 * @return string
		 */
		public function keyHex(): string {
			$key = $this->client()->codeHex().$this->codeHex();
			return $key;
		}

		/**
		 * Key as string of bracketed ord values for debugging
		 * @return string
		 */
		public function keyDebug(): string {
			$key = $this->client()->codeDebug().$this->codeDebug();
			return $key;
		}

		/**
		 * Get/Set Start Time
		 * @param int $time
		 * @return int Start Time
		 */
		public function startTime(int $time = null): int {
			if ($time) {
				$this->_startTime = $time;
			}
			return $this->_startTime;
		}

		/**
		 * Get/Set End Time
		 * @param int $time
		 * @return int End Time
		 */
		public function endTime(int $time = null): int {
			if ($time) {
				$this->_endTime = $time;
			}
			return $this->_endTime;
		}
	}