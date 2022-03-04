<?php
	namespace Site;

	class SiteMessageDeliveryList {
	
		private $_count = 0;
		private $_error;

		public function find($parameters = array()) {
	
			app_log("Site::SiteMessageDeliveryList::find()",'trace',__FILE__,__LINE__);
			
			$this->error = null;
			$get_objects_query = "
				SELECT	smd.id
				FROM	site_message_deliveries smd,
						site_messages sm
				WHERE	smd.message_id = sm.id
			";			

			$bind_params = array();
			if (isset($parameters['user_id'])) {
				$get_objects_query .= "
				AND smd.user_id = ?";
				array_push($bind_params,$parameters['user_id']);
			}
			if (isset($parameters['user_created'])) {
				$get_objects_query .= "
				AND sm.user_created = ?";
				array_push($bind_params,$parameters['user_created']);
			}
			if (isset($parameters['viewed'])) {
				if ($parameters['viewed'] == false) {
					$get_objects_query .= "
					AND	date_viewed IS NULL
					";
				}
				else {
					$get_objects_query .= "
					AND	date_viewed IS NOT NULL
					";
				}
			}
			if (isset($parameters['acknowledged'])) {
				if ($parameters['acknowledged'] == false) {
					$get_objects_query .= "
					AND	date_acknowledged IS NULL
					";
				}
				else {
					$get_objects_query .= "
					AND	date_acknowledged IS NOT NULL
					";
				}
			}

			query_log($get_objects_query,$bind_params);
			$rs = $GLOBALS['_database']->Execute($get_objects_query,$bind_params);
			if (! $rs) {
				$this->error = "SQL Error in Site::SiteMessageDeliveryList::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			
			$deliveries = array();
			while (list($id) = $rs->FetchRow()) {
			    $delivery = new \Site\SiteMessageDelivery($id);
			    $this->_count ++;
			    array_push($deliveries,$delivery);
			}
			
			return $deliveries;
		}
        
        public function error() {
            return $this->_error;
        }

        public function count() {
            return $this->_count;
        }
	}
