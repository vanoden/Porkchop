<?php
	namespace Site;

	class SiteMessage extends \ORM\BaseModel {
	
        public $id;
        public $user_created;
        public $recipient_id;
        public $date_created;
        public $important;
        public $subject;
        public $content;
        public $parent_id;
        public $tableName = 'site_messages';
        public $fields = array('id','user_created','recipient_id','date_created','important','subject','content','parent_id');
        
	}
