<?php
	namespace Register;

	class OrganizationAuditEvent extends \BaseModel {

		public $organization_id;
		public $admin_id;
		public $event_date;
		public $event_class;
		public $event_notes;

	    public function __construct(?int $id = null) {
			$this->_tableName = "register_organization_audit";
			$this->_tableUKColumn = null;
			$this->_addFields(array("organization_id", "admin_id", "event_date", "event_class", "event_notes"));
			$this->_auditEvents = false;
            parent::__construct($id);
		}

		public function user() {
			return new \Register\Customer($this->admin_id);
		}

		public function organization() {
			return new \Register\Organization($this->organization_id);
		}

		public function validClass($string) {
			if (preg_match('/^(ORGANIZATION_CREATED|ORGANIZATION_UPDATED|STATUS_CHANGED|NAME_CHANGED|RESELLER_CHANGED)$/',$string)) return true;
			return false;
		}
	}