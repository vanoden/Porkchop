<?php
	namespace Support;

	class HoursList {

		private $_error;

		public function find($parameters = array()) {
		
		    if (empty($parameters['code'])) {
				$this->_error = "task code is required to find logged hours";
				return null;
		    }
		
			$find_objects_query = "
				SELECT	id
				FROM	support_task_hours
				WHERE	id = id AND code = ?
				ORDER BY date_worked DESC
			";
			
            $rs = executeSQLByParams($find_objects_query,array('code'=> $parameters['code']));
			if (! $rs) {
				$this->_error = "SQL Error in Engineering::HoursList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$hours = array();
			while (list($id) = $rs->FetchRow()) {
				$hour = new Hours($id);
				array_push($hours,$hour);
			}

			return $hours;
		}

		public function error() {
			return $this->_error;
		}
	}
