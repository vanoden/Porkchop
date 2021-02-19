<?php
	namespace Alert;

	class AlertTrigger extends \ORM\BaseModel {
		public $id;
		public $name;
		public $enabled;
		public $tableName = 'alert_trigger';
        public $fields = array('id','name','enabled');
	}
