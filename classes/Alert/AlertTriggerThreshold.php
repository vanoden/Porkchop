<?php
	namespace Monitor;

	class AlertTriggerThreshold extends \ORM\BaseModel {
		public $trigger_id;
		public $threshold_id;
		public $group_id;
		public $tableName = 'alert_trigger_threshold';
        public $fields = array('trigger_id','threshold_id','group_id');
	}
