<?php
	namespace Engineering;

	class ReleaseList {
		private $_error;

		public function find($parameters = array()) {
			$bind_params = array();
			$find_objects_query = "
				SELECT	id
				FROM	engineering_releases
				WHERE	id = id
			";

			if (isset($parameters['!status'])) {
				$find_objects_query .= "
				AND		status != ?";
				array_push($bind_params,$parameters['!status']);
			}
			$find_objects_query .= "
				ORDER BY FIELD(status,'NEW','TESTING','RELEASED'),date_released desc,date_scheduled desc";
			
			if (isset($parameters['_limit']) && is_numeric($parameters['_limit'])) {
				$find_objects_query .= "
				LIMIT ".$parameters['_limit'];
			}
			query_log($find_objects_query);
			$rs = $GLOBALS['_database']->Execute(
				$find_objects_query,$bind_params
			);

			if (! $rs) {
				$this->_error = "SQL Error in Engineering::ReleaseList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$tasks = array();

			while (list($id) = $rs->FetchRow()) {
				$task = new Release($id);
				array_push($tasks,$task);
			}

			return $tasks;
		}

		public function error() {
			return $this->_error;
		}
	}
