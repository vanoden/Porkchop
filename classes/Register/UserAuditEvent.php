<?php
	namespace Register;

	class UserAuditEvent extends \BaseModel {
		public $id;
		public $user_id;
		public $admin_id;
		public $event_date;
		public $event_type;
		public $event_notes;

	    public function __construct(int $id = null) {
			$this->_tableName = "register_user_audit";
			$this->_tableUKColumn = null;
			$this->_addFields("user_id", "admin_id", "event_date", "event_type", "event_notes");
            parent::__construct($id);
		}
	}