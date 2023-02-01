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
        public $fields = array('id','name','address_1','address_2','city','province_id','zip_code', 'notes','country_id');

		public function __construct($id = 0,$parameters = array()) {
			parent::__construct($id);
			if (isset($parameters['recursive']) && $parameters['recursive']) {
				$this->province = new \Geography\Province($this->province_id);
				$this->country = new \Geography\Country($this->province->country_id);
			}
		}

		public function add($parameters = array()) {
			$province = new \Geography\Province($parameters['province_id']);
			$parameters['country_id'] = $province->country_id;
			return parent::add($parameters);
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
            $this->error("No records found for these values.");
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			return true;
		}

		public function associateOrganization($organization_id, $location_name = '') {
			$add_record_query = "
				INSERT
				INTO	register_organization_locations
				(organization_id, location_id)
				VALUES	(?,?)
				ON DUPLICATE KEY UPDATE
				location_id = location_id
			";
			$bind_params = array($organization_id,$this->id);
			query_log($add_record_query,$bind_params,true);
			$GLOBALS['_database']->Execute($add_record_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			return true;
		}

        public function applyDefaultBillingAndShippingAddresses($organizationId, $locationId, $isDefaultBilling=false, $isDefaultShipping=false) {
            if (!empty($isDefaultBilling)|| !empty($isDefaultShipping)) {
            
			    // bust register_organizations cache
			    $cache_key = "organization[".$organizationId."]";
			    $cache_item = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
			    $cache_item->delete();
            
			    $update_record_query = "
				    UPDATE `register_organizations` SET default_billing_location_id = NULL AND default_shipping_location_id = NULL WHERE id = ?;
			    ";
	            $bind_params = array($organizationId);
	            query_log($update_record_query,$bind_params,true);
	            $GLOBALS['_database']->Execute($update_record_query,$bind_params);
	            if ($GLOBALS['_database']->ErrorMsg()) {
		            $this->SQLError($GLOBALS['_database']->ErrorMsg());
		            return false;
	            }

	            if (!empty($isDefaultBilling)) {
			        $update_record_query = "
				        UPDATE `register_organizations` SET `default_billing_location_id` = ? WHERE id = ?;
			        ";
	                $bind_params = array($locationId, $organizationId);	                
	                query_log($update_record_query,$bind_params,true);
	                $GLOBALS['_database']->Execute($update_record_query,$bind_params);
	                if ($GLOBALS['_database']->ErrorMsg()) {
		                $this->SQLError($GLOBALS['_database']->ErrorMsg());
		                return false;
	                }
	            }
	            
	            if (!empty($isDefaultShipping)) {
			        $update_record_query = "
				        UPDATE `register_organizations` SET `default_shipping_location_id` = ? WHERE id = ?;
			        ";
	                $bind_params = array($locationId, $organizationId);
	                query_log($update_record_query,$bind_params,true);
	                $GLOBALS['_database']->Execute($update_record_query,$bind_params);
	                if ($GLOBALS['_database']->ErrorMsg()) {
		                $this->SQLError($GLOBALS['_database']->ErrorMsg());
		                return false;
	                }
	            }
            }
            
            return true;
        }
        
		public function province() {
			return new \Geography\Province($this->province_id);
		}

		public function organization() {
			$get_org_query = "
				SELECT	organization_id
				FROM	register_organization_locations
				WHERE	location_id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_org_query,array($this->id));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			list($org_id) = $rs->FetchRow();
			return new \Register\Organization($org_id);
		}

		public function validAddress($string) {	    	
			if (empty($string)) return true;
			if (preg_match('/^[\w? :.-|\'\)]+$/',urldecode($string))) return true;
			else return false;
		}

		public function validCity($string) {
			if (preg_match('/^[\w? :.-|\'\)]+$/',urldecode($string))) return true;
			else return false;
		}
	}
