<?php
	namespace Engineering;

	class ReleaseList {
		private $_error;

		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	engineering_releases
				WHERE	id = id
			";

			$find_objects_query .= "
				ORDER BY status,date_scheduled desc";
			
			if (isset($parameters['_limit']) && is_numeric($parameters['_limit'])) {
				$find_objects_query .= "
				LIMIT ".$parameters['_limit'];
			}

			$rs = $GLOBALS['_database']->Execute(
				$find_objects_query
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
?>
