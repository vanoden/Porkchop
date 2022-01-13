<?php
	namespace Engineering;

	class ProjectList {
		private $_error;
		private $_count;

		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	engineering_projects
				WHERE	id = id
			";

			$bind_params = array();

			if (isset($parameters['status'])) {
				if (is_array($parameters['status'])) {
					$find_objects_query .= "
					AND	status in (";
					$first = true;
					foreach ($parameters['status'] as $status) {
						if (! preg_match('/^[\w\s\_\-\.]+$/',$status)) continue;
						if (! $first) $find_objects_query .= ",";
						$first = false;
						$find_objects_query .= "'$status'";
					}
					$find_objects_query .= ")";
				}
				else {
					$find_objects_query .= "
					AND		status = ?";
					array_push($bind_params,$parameters['status']);
				}
			}

			$find_objects_query .= "
				ORDER BY title ASC";

            $rs = executeSQLByParams($find_objects_query,$bind_params);
			if (! $rs) {
				$this->_error = "SQL Error in Engineering::ProjectList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$projects = array();
			while (list($id) = $rs->FetchRow()) {
				$this->_count ++;
				$project = new Project($id);
				array_push($projects,$project);
			}

			return $projects;
		}

		public function search($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	engineering_projects
				WHERE	id = id
                ORDER BY title ASC
			";

			if (! preg_match('/^[\w\-\.\_\s]+$/',$parameters['searchTerm']) {
				$this->_error = "Invalid characters in search term";
				return null;
			}

			// if search term, then constrain by that
			if ($parameters['searchTerm']) {
                $find_objects_query = "
                  SELECT	`id`
                  FROM	`engineering_projects`
                  WHERE	`code` LIKE '%".$parameters['searchTerm']."%' 
                        OR `title` LIKE '%".$parameters['searchTerm']."%' 
                        OR `description` LIKE '%".$parameters['searchTerm']."%'
                  ORDER BY title ASC";
            }

            $rs = executeSQLByParams($find_objects_query, array());
			if (! $rs) {
				$this->_error = "SQL Error in Engineering::ProjectList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$projects = array();
			while (list($id) = $rs->FetchRow()) {
				$this->_count ++;
				$project = new Project($id);
				array_push($projects,$project);
			}
			
			return $projects;
		}

		public function error() {
			return $this->_error;
		}
	}
