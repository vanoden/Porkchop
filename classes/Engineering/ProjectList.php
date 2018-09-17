<?php
	namespace Engineering;

	class ProjectList {
		private $_error;

		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	engineering_projects
				WHERE	id = id
			";

			$rs = $GLOBALS['_database']->Execute(
				$find_objects_query
			);

			if (! $rs) {
				$this->_error = "SQL Error in Engineering::ProjectList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$projects = array();

			while (list($id) = $rs->FetchRow()) {
				$project = new Project($id);
				array_push($projects,$project);
			}

			return $projects;
		}

		public function error() {
			return $this->_error;
		}
	}
?>
