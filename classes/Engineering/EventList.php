<?php
	namespace Engineering;

	class EventList {
		private $_error;

		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	ee.id
				FROM	engineering_events ee
				JOIN	engineering_tasks et
				ON		ee.task_id = et.id
				JOIN	engineering_products ep
				ON		ep.id = et.product_id
				LEFT OUTER JOIN
						engineering_projects epr
				ON		epr.id = et.project_id
				WHERE	ee.id = ee.id
			";

			$bind_params = array();
			if (isset($parameters['task_id']) && is_numeric($parameters['task_id'])) {
				$task = new \Engineering\Task($parameters['task_id']);
				if ($task->error()) {
					$this->_error = $task->error();
					return null;
				}
				if (! $task->id) {
					$this->_error = "Task not found";
					return null;
				}
				$find_objects_query .= "
				AND		et.id = ?";
				array_push($bind_params,$task->id);
			}

			if (isset($parameters['user_id']) && is_numeric($parameters['user_id'])) {
				$user = new \Register\Customer($parameters['user_id']);
				if ($user->error()) {
					$this->_error = $user->error();
					return null;
				}
				if (! $user->id) {
					$this->_error = "User not found";
					return null;
				}
				$find_objects_query .= "
				AND		ee.person_id = ?";
				array_push($bind_params,$user->id);
			}

			if (isset($parameters['project_id']) && is_numeric($parameters['project_id'])) {
				$project = new \Engineering\Project($parameters['project_id']);
				if ($project->error()) {
					$this->_error = $project->error();
					return null;
				}
				if (! $project->id) {
					$this->_error = "Project not found";
					return null;
				}
				$find_objects_query .= "
				AND		epr.id = ?";
				array_push($bind_params,$project->id);
			}

			if (isset($parameters['product_id']) && is_numeric($parameters['project_id'])) {
				$product = new \Engineering\Product($parameters['project_id']);
				if ($product->error()) {
					$this->_error = $product->error();
					return null;
				}
				if (! $product->id) {
					$this->_error = "Product not found";
					return null;
				}
				$find_objects_query .= "
				AND		ep.id = ?";
				array_push($bind_params,$product->id);
			}

			if (isset($parameters['date_start']) && strlen($parameters['date_start'])) {
				$date_start = get_mysql_date($parameters['date_start']);
				if (! $date_start) {
					$this->_error = "Invalid start date";
					return null;
				}
				$find_objects_query .= "
				AND		ee.date_event >= ?
				";
				array_push($bind_params,$date_start);
			}

			if (isset($parameters['date_end']) && strlen($parameters['date_end'])) {
				$date_end = get_mysql_date($parameters['date_end']);
				if (! $date_end) {
					$this->_error = "Invalid end date";
					return null;
				}
				$find_objects_query .= "
				AND		ee.date_event < ?
				";
				array_push($bind_params,$date_end);
			}

			$find_objects_query .= "
				ORDER BY date_event
			";

			if (isset($parameters['_limit']) && is_numeric($parameters['_limit'])) {
				$find_objects_query .= "
				LIMIT ".$parameters['_limit'];
			}

			$rs = $GLOBALS['_database']->Execute($find_objects_query,$bind_params);

			if (! $rs) {
				$this->_error = "SQL Error in Engineering::EventList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$events = array();

			while (list($id) = $rs->FetchRow()) {
				$event = new Event($id);
				array_push($events,$event);
			}

			return $events;
		}

		public function error() {
			return $this->_error;
		}
	}
?>
