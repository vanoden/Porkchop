<?php
	namespace Register;

	class UserAuditEvent extends \BaseModel {
		public $id;
		public $user_id;
		public $admin_id;
		public $event_date;
		public $event_class;
		public $event_notes;

	    public function __construct(int $id = null) {
			$this->_tableName = "register_user_audit";
			$this->_tableUKColumn = null;
			$this->_addFields(array("user_id", "admin_id", "event_date", "event_class", "event_notes"));
            parent::__construct($id);
		}

		public function user() {
			return new \Register\Customer($this->user_id);
		}

		public function admin() {
			return new \Register\Admin($this->admin_id);
		}

		public function validClass($string) {
			
			if (preg_match('/^(REGISTRATION_SUBMITTED|REGISTRATION_APPROVED|REGISTRATION_DISCARDED|AUTHENTICATION_SUCCESS|AUTHENTICATION_FAILURE|PASSWORD_CHANGED|PASSWORD_RECOVERY_REQUESTED|ORGANIZATION_CHANGED|ROLE_ADDED|ROLE_REMOVED|STATUS_CHANGED|RESET_KEY_GENERATED|USER_UPDATED)$/',$string)) return true;
			return false;
		}
	}
