<?php
	namespace Alert;

	class AlertAction extends \ORM\BaseModel {
		public $id;
		public $escalation_id;
		public $status;
		public $tableName = 'alert_actions';
        public $fields = array('id','escalation_id','status');	
	}
