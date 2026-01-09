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

		public function add($parameters = []) {
			// Validate required fields are not NULL
			if (empty($parameters['organization_id']) || $parameters['organization_id'] === null) {
				$this->error("Organization ID is required and cannot be NULL");
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