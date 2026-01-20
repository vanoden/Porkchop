<?php
	namespace Register;

	class UserAuditEvent extends \BaseModel {
		public $user_id;
		public $admin_id;
		public $event_date;
		public $event_class;
		public $event_notes;

	    public function __construct(?int $id = null) {
			$this->_tableName = "register_user_audit";
			$this->_tableUKColumn = null;
			$this->_addFields(array("user_id", "admin_id", "event_date", "event_class", "event_notes"));
			$this->_auditEvents = false;
            parent::__construct($id);
		}

		public function add($parameters = []) {
			// Validate required fields are not NULL
			if (empty($parameters['user_id']) || $parameters['user_id'] === null) {
				$this->error("User ID is required and cannot be NULL");
				return false;
			}
			if (empty($parameters['admin_id']) || $parameters['admin_id'] === null) {
				$this->error("Admin ID is required and cannot be NULL");
				return false;
			}
			if (empty($parameters['event_date']) || $parameters['event_date'] === null) {
				$this->error("Event date is required and cannot be NULL");
				return false;
			}
			if (empty($parameters['event_class']) || $parameters['event_class'] === null) {
				$this->error("Event class is required and cannot be NULL");
				return false;
			}
			// event_notes can be NULL, but if it is, set a message
			if (empty($parameters['event_notes']) || $parameters['event_notes'] === null) {
				$parameters['event_notes'] = 'Value not found';
			}
			
			return parent::add($parameters);
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
