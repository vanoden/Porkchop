<?php
	namespace Engineering;

	class TaskList {
		private $_error;

		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	engineering_tasks
				WHERE	id = id
			";

			if (isset($parameters['project_id']) && is_numeric($parameters['project_id'])) {
				$find_objects_query .= "
				AND		project_id = ".$parameters['project_id'];
			}

			if (isset($parameters['assigned_id']) && is_numeric($parameters['assigned_id'])) {
				$find_objects_query .= "
				AND		assigned_id = ".$parameters['assigned_id'];
			}

			if (isset($parameters['status'])) {
				if (is_array($parameters['status'])) {
					$icount = 0;
					$find_objects_query .= "
				AND		status IN (";
					foreach ($parameters['status'] as $status) {
						if (preg_match('/^[\w\-\_\.\s]+$/',$status)) {
							if ($icount > 0) $find_objects_query .= ",";
							$icount ++;
							$find_objects_query .= "'".$status."'";
						}
					}
					$find_objects_query .= ")";
				}
				else {
					$find_objects_query .= "
				AND		status = ".$GLOBALS['_database']->qstr($parameters['status'],get_magic_quotes_gpc());
				}
			}
			else {
				$find_objects_query .= "
				AND		status NOT IN ('CANCELLED')";
			}

			if (isset($parameters['_limit']) && is_numeric($parameters['_limit'])) {
				$find_objects_query .= "
				LIMIT ".$parameters['_limit'];
			}

			$rs = $GLOBALS['_database']->Execute(
				$find_objects_query
			);

			if (! $rs) {
				$this->_error = "SQL Error in Engineering::TaskList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$tasks = array();

			while (list($id) = $rs->FetchRow()) {
				$task = new Task($id);
				array_push($tasks,$task);
			}

			return $tasks;
		}

		public function error() {
			return $this->_error;
		}
	}
?>
