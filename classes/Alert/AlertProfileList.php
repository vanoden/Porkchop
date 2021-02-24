<?php
	namespace Alert;

	class AlertProfileList {
	
		private $_count = 0;
		private $_error;

		public function find($parameters = array()) {
		
			app_log("Alert::AlertProfileList::find()",'trace',__FILE__,__LINE__);
			
			$this->error = null;
			$get_alert_profiles_query = "
				SELECT	id
				FROM	alert_profiles
				WHERE	id = id
			";

			$bind_params = array();
			if (isset($parameters['organization_id']) && is_int($parameters['organization_id'])) {
				$get_alert_profiles_query .= "
				AND     organization_id = ?";
				array_push($bind_params,$parameters['organization_id']);
			}

			
			query_log($get_alert_profiles_query,$bind_params);
			$rs = $GLOBALS['_database']->Execute($get_alert_profiles_query,$bind_params);
			if (! $rs) {
				$this->error = "SQL Error in Alert::AlertProfileList::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			
			$alertProfiles = array();
			while (list($id) = $rs->FetchRow()) {
			    $alertThreshold = new \Alert\AlertProfile();
			    $this->count ++;
			    array_push($alertProfiles,$alertThreshold);
			}
			
			return $alertProfiles;
		}
		
		/**
		 * get default profiles for all customers (NULL organization_id)
		 */
		public function findDefault() {
		
			app_log("Alert::AlertProfileList::findDefault()",'trace',__FILE__,__LINE__);
			
			$this->error = null;
			$get_alert_profiles_query = "
				SELECT	id
				FROM	alert_profiles
				WHERE	id = id
			";


            $bind_params = array();
			$get_alert_profiles_query .= "
				AND     organization_id = ?";
				array_push($bind_params, NULL);
			
			query_log($get_alert_profiles_query,$bind_params);
			$rs = $GLOBALS['_database']->Execute($get_alert_profiles_query,$bind_params);
			if (! $rs) {
				$this->error = "SQL Error in Alert::AlertProfileList::findDefault: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			
			$alertProfiles = array();
			while (list($id) = $rs->FetchRow()) {
			    $alertThreshold = new \Alert\AlertProfile();
			    $this->count ++;
			    array_push($alertProfiles,$alertThreshold);
			}
			
			return $alertProfiles;
		}
		
        public function error() {
            return $this->_error;
        }

        public function count() {
            return $this->_count;
        }
	}
