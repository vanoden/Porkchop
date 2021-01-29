<?php
	namespace Alert;

	class AlertThresholdList {
		private $_count = 0;
		private $_error;

		public function find($parameters = array()) {
		
			app_log("Alert::AlertThresholdList::find()",'trace',__FILE__,__LINE__);
			
			$this->error = null;
			$get_alert_thresholds_query = "
				SELECT	id
				FROM	alert_threshold
				WHERE	id = id
			";

			$bind_params = array();
			if (isset($parameters['sensor_id']) && is_int($parameters['sensor_id'])) {
				$get_alert_thresholds_query .= "
				AND     sensor_id = ?";
				array_push($bind_params,$parameters['sensor_id']);
			}

			
			query_log($get_alert_thresholds_query,$bind_params);
			$rs = $GLOBALS['_database']->Execute($get_alert_thresholds_query,$bind_params);
			if (! $rs) {
				$this->error = "SQL Error in Alert::AlertThresholdList::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			
			$alertThresholds = array();
			while (list($id) = $rs->FetchRow()) {
			    $alertThreshold = new \Alert\AlertThreshold();
			    $this->count ++;
			    array_push($alertThresholds,$alertThreshold);
			}
			
			return $alertThresholds;
		}
        
        public function error() {
            return $this->_error;
        }

        public function count() {
            return $this->_count;
        }
	}
