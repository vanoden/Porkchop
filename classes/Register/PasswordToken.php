<?php
	namespace Register;

	class PasswordToken {
		public $error;
		public $person_id;
		public $expiration;
		public $code;
		
		public function add($person_id)
		{
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
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in RegisterPasswordToken::add: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $code;
		}
		
		public function consume($code)
		{
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

			if (! $rs)
			{
				$this->error = "SQL Error in RegisterRecovery::consume: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			if ($rs->RecordCount())
			{
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
				return $person_id;
			}
			else return 0;
		}
	}
?>