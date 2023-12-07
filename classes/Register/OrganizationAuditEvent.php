<?php
	namespace Register;

	class OrganizationAuditEvent extends \BaseModel {
		public $id;
		public $user_id;
		public $admin_id;
		public $event_date;
		public $event_class;
		public $event_notes;

	    public function __construct(int $id = null) {
			$this->_tableName = "register_organization_audit";
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
			if (preg_match('/^(ORGANIZATION_CREATED|STATUS_CHANGED|NAME_CHANGED|RESELLER_CHANGED)$/',$string)) return true;
			return false;
		}
	}