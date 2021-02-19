<?php
	namespace Alert;

	class AlertTriggerEscalation extends \ORM\BaseModel {
		public $id;
		public $trigger_id;
		public $type;
		public $parameters;
		public $tableName = 'alert_trigger_escalation';
        public $fields = array('id','trigger_id','type','parameters');
	}
