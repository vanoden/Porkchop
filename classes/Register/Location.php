<?php
	namespace Register;

	class Location extends \ORM\BaseModel {
	
		public $id;
		public $name;
		public $address_1;
		public $address_2;
		public $city;
		public $province_id;
		public $zip_code;
		public $notes;
		public $tableName = 'register_locations';
        public $fields = array('id','name','address_1','address_2','city','province_id','zip_code', 'notes');

		public function __construct($id = 0,$parameters = array()) {
			parent::__construct($id);
			if ($parameters['recursive']) {
				$this->province = new \Geography\Province($this->province_id);
				$this->country = new \Geography\Country($this->province->country_id);
			}
		}
        /**
         * find existing entry by user provided address info
         *
         * @param array, $parameters
         */
        public function findExistingByAddress($parameters = array()) {
            $getObjectQuery = "SELECT id FROM `$this->tableName` WHERE
                LOWER(`address_1`) LIKE '%".strtolower($parameters['address_1'])."%'
                AND LOWER(`address_2`) LIKE '%".strtolower($parameters['address_2'])."%'
                AND LOWER(`city`) LIKE '%".strtolower($parameters['city'])."%'
                AND LOWER(`zip_code`) LIKE '%".strtolower($parameters['zip_code'])."%'";   
			$rs = $this->execute($getObjectQuery, array());
            if ($rs) {
                list($id) = $rs->FetchRow();
                if ($id) {
                    $this->id = $id;
                    $this->details();
                    return true;
                }
            }
            $this->_error = "ERROR: no records found for these values.";
            return false;
        }

		public function associateUser($user_id) {
			$add_record_query = "
				INSERT
				INTO	register_user_locations
				(user_id,location_id)
				VALUES	(?,?)
				ON DUPLICATE KEY UPDATE
				location_id = location_id
			";
			$bind_params = array($user_id,$this->id);
			query_log($add_record_query,$bind_params,true);
			$GLOBALS['_database']->Execute($add_record_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Register::Location::associateUser(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			return true;
		}

		public function associateOrganization($organization_id, $location_name = '') {
			$add_record_query = "
				INSERT
				INTO	register_organization_locations
				(organization_id,location_id,name)
				VALUES	(?,?,?)
				ON DUPLICATE KEY UPDATE
				location_id = location_id
			";
			$bind_params = array($organization_id,$this->id,$location_name);
			query_log($add_record_query,$bind_params,true);
			$GLOBALS['_database']->Execute($add_record_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Register::Location::associateOrganization(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			return true;
		}

		public function province() {
			return new \Geography\Province($this->province_id);
		}
	}
