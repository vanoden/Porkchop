<?php
	namespace Site;

	class SiteMessageDeliveries extends \ORM\BaseModel {
	
        public $id;
        public $message_id;
        public $user_id;
        public $date_viewed;
        public $date_acknowledged;
        public $tableName = 'site_message_deliveries';
        public $fields = array('id','message_id','user_id','date_viewed','date_acknowledged');
        
	}
