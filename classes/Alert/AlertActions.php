<?php
	namespace Monitor;

	class AlertActions extends \ORM\BaseModel {
		public $id;
		public $escalation_id;
		public $status;
		public $tableName = 'alert_actions';
        public $fields = array('id','escalation_id','status');	
	}
