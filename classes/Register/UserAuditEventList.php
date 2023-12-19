<?php
	namespace Register;

	class UserAuditEventList extends \BaseListClass {
		public function __construct() {
            $this->_modelName = '\Register\UserAuditEvent';
        }
	}
