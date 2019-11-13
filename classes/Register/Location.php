<?php
	namespace Register;

	class Location extends \ORM\BaseModel {
		public $id;
		public $name;
		public $address_1;
		public $address_2;
		public $city;
		public $region_id;
		public $country_id;
		public $zip_code;
		public $notes;
		public $tableName = 'register_locations';
        public $fields = array('id','name','address_1','address_2','city','region_id','country_id','zip_code', 'notes');

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
	}
