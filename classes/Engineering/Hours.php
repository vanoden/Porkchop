<?php
	namespace Engineering;
	
	class Hours extends \ORM\BaseModel {
		public $id;
        public $date_worked;
        public $number_of_hours;
        public $code;
        public $user_id;
		public $tableName = 'engineering_task_hours';
        public $fields = array('id','date_worked','number_of_hours','code','user_id');
    }
