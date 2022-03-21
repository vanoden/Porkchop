<?php
	namespace Engineering;

	/**
	 * list of engineering tasks 
	 */
	class TaskList {
	
		private $_error;
		private $count = 0;

		/**
		 * get current tasks based on params criteria array
		 * 
		 * @param array $parameters
		 */
		public function find($parameters = array()) {
			if (isset($parameters['searchTerm'])) return $this->search($parameters);

			$find_objects_query = "
				SELECT	id
				FROM	engineering_tasks
				WHERE	id = id
			";

			$bind_params = array();
			if (isset($parameters['project_id']) && is_numeric($parameters['project_id'])) {
				$find_objects_query .= "
				AND		project_id = ?";
				array_push($bind_params,$parameters['project_id']);
			}

			if (isset($parameters['product_id']) && is_numeric($parameters['product_id'])) {
				$find_objects_query .= "
				AND		product_id = ?";
				array_push($bind_params,$parameters['product_id']);
			}

			if (isset($parameters['assigned_id']) && is_numeric($parameters['assigned_id'])) {
				$find_objects_query .= "
				AND		assigned_id = ?";
				array_push($bind_params,$parameters['assigned_id']);
			}

            // allow for "unassigned" searches of tasks = assigned_id default '0'
			if (isset($parameters['assigned_id']) && $parameters['assigned_id'] == 'Unassigned') {
				$find_objects_query .= "
				AND		assigned_id = ?";
				array_push($bind_params, 0);
			}
			
			if (isset($parameters['duplicate_task_id']) && is_numeric($parameters['duplicate_task_id'])) {
				$find_objects_query .= "
				AND		duplicate_task_id = ?";
				array_push($bind_params, 0);
			}
			
			if (isset($parameters['role_id']) && is_numeric($parameters['role_id'])) {
				$find_objects_query .= "
				AND		role_id = ?";
				array_push($bind_params, 0);
			}
			
			if (isset($parameters['release_id']) && is_numeric($parameters['release_id'])) {
				$find_objects_query .= "
				AND		release_id = ?";
				array_push($bind_params,$parameters['release_id']);
			}

			if (isset($parameters['status']) && !empty($parameters['status'])) {
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
						else {
							$this->_error = "Invalid status";
							return null;
						}
					}
					$find_objects_query .= ")";
				}
				else {
					$find_objects_query .= "
				AND		status = ?";
				array_push($bind_params,$parameters['status']);
				}
			}
			else {
				$find_objects_query .= "
				AND		status NOT IN ('CANCELLED')";
			}

			$find_objects_query .= "
				ORDER BY FIELD(status,'ACTIVE','BROKEN','TESTING','NEW','HOLD','CANCELLED'),
						FIELD(priority,'CRITICAL','URGENT','IMPORTANT','NORMAL'),
						FIELD(difficulty,'PROJECT','HARD','NORMAL','EASY'),
						date_added DESC
			";

			if (isset($parameters['_limit']) && is_numeric($parameters['_limit'])) {
				$find_objects_query .= "
				LIMIT ".$parameters['_limit'];
			}

            $rs = executeSQLByParams($find_objects_query,$bind_params);
			if (! $rs) {
				$this->_error = "SQL Error in Engineering::TaskList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$tasks = array();

			while (list($id) = $rs->FetchRow()) {
				$task = new Task($id);
				array_push($tasks,$task);
				$this->count ++;
			}

			return $tasks;
		}

		public function search($parameters) {
			$search_term = null;
			if (preg_match('/^[\w\-\.\_\s\*]+$/',$parameters['searchTerm'])) $search_term = $parameters['searchTerm'];
			else {
				$this->_error = "Invalid search term";
				return null;
			}

			$find_objects_query = "
				SELECT	`id`
				FROM	`engineering_tasks`
				WHERE	(`code` LIKE '%".$search_term."%' 
				OR		`title` LIKE '%".$search_term."%' 
				OR		`description` LIKE '%".$search_term."%'
				OR		`location` LIKE '%".$search_term."%')";

			$bind_params = array();
			if (isset($parameters['project_id']) && is_numeric($parameters['project_id'])) {
				$find_objects_query .= "
				AND		project_id = ?";
				array_push($bind_params,$parameters['project_id']);
			}

			if (isset($parameters['assigned_id']) && is_numeric($parameters['assigned_id'])) {
				$find_objects_query .= "
				AND		assigned_id = ?";
				array_push($bind_params,$parameters['assigned_id']);
			}

			if (isset($parameters['release_id']) && is_numeric($parameters['release_id'])) {
				$find_objects_query .= "
				AND		release_id = ".$parameters['release_id'];
				array_push($bind_params,$parameters['release_id']);
			}

			if (isset($parameters['status']) && !empty($parameters['status'])) {
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
						else {
							$this->_error = "Invalid status";
							return null;
						}
					}
					$find_objects_query .= ")";
				}
				else {
					$find_objects_query .= "
				AND		status = ?";
				array_push($bind_params,$parameters['status']);
				}
			}
			else {
				$find_objects_query .= "
				AND		status NOT IN ('CANCELLED')";
			}

			$find_objects_query .= "
				ORDER BY FIELD(status,'ACTIVE','BROKEN','TESTING','NEW','HOLD','CANCELLED'),
						FIELD(priority,'CRITICAL','URGENT','IMPORTANT','NORMAL'),
						FIELD(difficulty,'PROJECT','HARD','NORMAL','EASY'),
						date_added DESC
			";

			if (isset($parameters['_limit']) && is_numeric($parameters['_limit'])) {
				$find_objects_query .= "
				LIMIT ".$parameters['_limit'];
			}

            $rs = executeSQLByParams($find_objects_query,$bind_params);
			if (! $rs) {
				$this->_error = "SQL Error in Engineering::TaskList::search(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$tasks = array();
			while (list($id) = $rs->FetchRow()) {
				$task = new Task($id);
				array_push($tasks,$task);
				$this->count ++;
			}

			return $tasks;
		}
		public function count() {
			return $this->count();
		}
		public function error() {
			return $this->_error;
		}
	}
