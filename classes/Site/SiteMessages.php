<?php
	namespace Site;

	class SiteMessages extends \ORM\BaseModel {
	
        public $id;
        public $user_created;
        public $date_created;
        public $important;
        public $content;
        public $parent_id;
        public $tableName = 'site_messages';
        public $fields = array('id','user_created','date_created','important','content parent_id');
        
	}
