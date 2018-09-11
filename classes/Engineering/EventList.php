<?php
	namespace Engineering;

	class EventList {
		private $_error;

		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	engineering_events
				WHERE	id = id
			";

			if (isset($parameters['task_id']) && is_numeric($parameters['task_id'])) {
				$find_objects_query .= "
				AND		task_id = ".$parameters['task_id'];
			}

			if (isset($parameters['_limit']) && is_numeric($parameters['_limit'])) {
				$find_objects_query .= "
				LIMIT ".$parameters['_limit'];
			}

			$rs = $GLOBALS['_database']->Execute(
				$find_objects_query
			);

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
