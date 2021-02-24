<?php
	namespace Alert;

	class AlertProfile extends \ORM\BaseModel {
        public $id;
        public $organization_id;
        public $profile_settings_data;
        public $tableName = 'alert_profiles';
        public $fields = array('id','organization_id','profile_settings_data');	
	}
