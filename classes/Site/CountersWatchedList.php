<?php
	namespace Site;

	class CountersWatchedList {
	
		private $_count = 0;
		private $_error;

		public function find($parameters = array()) {
	
			app_log("Site::CountersWatchedList::find()",'trace',__FILE__,__LINE__);
			
			$this->error = null;
			$get_objects_query = "
				SELECT	*
				FROM	counters_watched 
				WHERE	1
			";			
			
			$bind_params = array();
			if (isset($parameters['key'])) {
				$get_objects_query .= "
				AND key = ?";
				array_push($bind_params,$parameters['key']);
			}
			if (isset($parameters['notes'])) {
				$get_objects_query .= "
				AND notes = ?";
				array_push($bind_params,$parameters['notes']);
			}
			
			query_log($get_objects_query,$bind_params,true);
			$rs = $GLOBALS['_database']->Execute($get_objects_query,$bind_params);
			if (! $rs) {
				$this->error = "SQL Error in Site::CountersWatchedList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			
			$counters = array();
			while (list($id) = $rs->FetchRow()) {
			    $delivery = new \Site\CounterWatched($id);
			    $this->_count ++;
			    array_push($counters,$delivery);
			}
			return $counters;
		}
        
        public function error() {
            return $this->_error;
        }

        public function count() {
            return $this->_count;
        }
	}
