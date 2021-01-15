<?php
	namespace Monitor;

	class AlertThreshold extends \ORM\BaseModel {
		public $id;
		public $sensor_id;
		public $operator;
		public $value;
		public $tableName = 'alert_threshold';
        public $fields = array('id','sensor_id','operator', 'value');
	}
