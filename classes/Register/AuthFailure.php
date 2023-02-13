<?php
	namespace Register;

	class AuthFailure Extends \BaseClass {

		public $ip_address;
		public $login;
		public $reason;
		public $endpoint;
		public $date;

		public function __construct($id = 0) {
			$this->_tableName = 'register_auth_failures';

			if (is_numeric($id)) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($ip_address, $login, $reason, $endpoint) {
			$add_object_query = "
				INSERT
				INTO	register_auth_failures
				VALUES	(null,?,?,sysdate(),?,?)
			";

			if (! $this->validReason($reason)) {
				app_log("Invalid auth failure reason '".$reason."'",'warn');
				$reason = 'UNKNOWN';
			}
			$bind_params = array(ip2long($ip_address),$login,$reason,$endpoint);

			$GLOBALS['_database']->Execute($add_object_query,$bind_params);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}

			$this->id = $GLOBALS['_database']->Insert_ID();

			return $this->details();
		}

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

		public function validReason($string): bool {
			if (in_array($string,array('NOACCOUNT','PASSEXPIRED','WRONGPASS','INACTIVE','INVALIDPASS','CSRFTOKEN','UNKNOWN'))) return true;
			else {
				$this->error("Invalid Failure reason: '".$string."'");
				error_log($this->error());
			}
		}
	}

