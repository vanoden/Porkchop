<?php
	namespace Register;

	class AuthFailure Extends \BaseModel {

		public $ip_address;			// IP Address of the client that failed login attempt
		public $login;				// Login that failed
		public $reason;				// Reason for failure
		public $endpoint;			// Endpoint that failure occurred at
		public $date;				// Date of failure

		/**
		 * Constructor for AuthFailure
		 * @param int $id 
		 */
		public function __construct($id = 0) {
			$this->_tableName = 'register_auth_failures';
    		parent::__construct($id);
		}

		/**
		 * Record an authentication failure
		 * @param array $parameters 
		 * @return bool 
		 */
		public function add($parameters = []) {
			$this->clearError();

			$database = new \Database\Service;

			// Dereference parameters array
		    list ($ip_address, $login, $reason, $endpoint) = $parameters;

			// Convert IP Address to Integer
			if (preg_match('/^(\d{1,3}\.){3}\d{1,3}$/',$ip_address)) {
				// Convert Address to Integer
				$ip_address = ip2long($ip_address);
			}
			// Cannot insert nulls
			elseif (empty($ip_address)) $ip_address = 0;
			if (empty($login)) $login = '';
		    if (empty($reason)) $reason = '';
		    if (empty($endpoint)) $endpoint = '';

			// Validate Reason
			if (! $this->validReason($reason)) {
				app_log("WHATS WRONG!",'warn');
				app_log(debug_backtrace()[1]['function'],'warn');
				app_log("Invalid auth failure reason '".$reason."'",'warn');
				$reason = 'UNKNOWN';
			}

			// Prepare Query
			$add_object_query = "
				INSERT
				INTO	register_auth_failures
				VALUES	(null,?,?,sysdate(),?,?)
			";

			// Bind Parameters
			$database->AddParams(array($ip_address,$login,$reason,$endpoint));

			// Execute Query
			$database->Execute($add_object_query);

			// Check for Errors
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// Fetch New ID
			$this->id = $database->Insert_ID();

			return $this->details();
		}

		/**
		 * Get details of an authentication failure
		 * @return bool 
		 */
		public function details(): bool {
			$get_details_query = "
				SELECT	*
				FROM	register_auth_failures
				WHERE	id = ?
			";

			$rs = $GLOBALS['_database']->Execute($get_details_query,array($this->id));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if (isset($object->id)) {
				$this->id = $object->id;
				$this->ip_address = long2ip($object->ip_address);
				$this->login = $object->login;
				$this->reason = $object->reason;
				$this->date = $object->date_fail;
				$this->endpoint = $object->endpoint;
			}
			else {
				$this->id = null;
				$this->ip_address = null;
				$this->login = null;
				$this->reason = null;
				$this->date = null;
				$this->endpoint = null;
			}
			return true;
		}

		/**
		 * Validate the reason for the failure
		 * @param string $string
		 * @return bool
		 */
		public function validReason($string): bool {
			if (in_array($string,array('NOACCOUNT','PASSEXPIRED','WRONGPASS','INACTIVE','INVALIDPASS','CSRFTOKEN','UNKNOWN'))) return true;
			else {
				$this->error("Invalid Failure reason: '".$string."'");
				error_log($this->error());
				return false;
			}
		}
	}

