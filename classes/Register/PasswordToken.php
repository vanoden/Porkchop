<?php
	namespace Register;

	class PasswordToken Extends \BaseModel {
		public $person_id;
		public $expiration;
		public $code;

		public function __construct($id = null) {
			$this->_tableName = "register_password_tokens";
			$this->_addFields(array('code','person_id','date_expires'));
		}

		public function add($person_id = []) {
			
			# Get Large Random value
			$randval = mt_rand();		

			# Use hash to further bury session id
			$code = hash('sha256',$randval);

			# Add recovery record to database
			$add_object_query = "
				REPLACE
				INTO	register_password_tokens
				VALUES	(?,?,date_add(sysdate(),INTERVAL 1 day),?)
			";

			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$person_id,
					$code,
					$GLOBALS['_REQUEST_']->client_ip
				)
			);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}

			// Audit Record for RESET_KEY_GENERATED
			$customer = new \Register\Customer($person_id);
			if ($customer->error()) {
				app_log("Error finding customer: ".$person_id,'error',__FILE__,__LINE__);
			} else {
				$customer->auditRecord('RESET_KEY_GENERATED',$code);
			}
			$this->code = $code;
			return $code;
		}
		
		public function consume($code) {
			# Get Code from Database
			$get_record_query = "
				SELECT	person_id
				FROM	register_password_tokens
				WHERE	code = ?
				AND		date_expires > sysdate()
			";

			$rs = $GLOBALS['_database']->Execute(
				$get_record_query,
				array($code)
			);

			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}

			if ($rs->RecordCount()) {
				list($person_id) = $rs->FetchRow();
				
				$delete_record_query = "
					DELETE
					FROM	register_password_tokens
					WHERE	person_id = ?
				";
				$GLOBALS['_database']->Execute(
					$delete_record_query,
					array($person_id)
				);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->SQLError($GLOBALS['_database']->ErrorMsg());
				}
				return $person_id;
			}
			else return null;
		}

		public function getKey($person_id) {
			$database = new \Database\Service();
			$get_object_query = "
				SELECT	code
				FROM	register_password_tokens
				WHERE	person_id = ?
				AND		date_expires > sysdate()
			";
			$database->addParam($person_id);
			$rs = $database->Execute($get_object_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}
			list($code) = $rs->FetchRow();
			app_log("ResetKey: $code");
			return $code;
		}

		public function validCode($string): bool {
			if (preg_match('/^[a-f0-9]{64}$/',$string)) return true;
			else return false;
		}
	}
