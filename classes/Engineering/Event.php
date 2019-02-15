<?php
	namespace Engineering;

	class Event {
		private $_error;
		public $id;
		public $task_id;
		public $description;
		private $person_id;

		public function __construct($id = 0) {
			if (is_numeric($id) && $id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters = array()) {
			if (isset($parameters['task_id'])) {
				$task = new Task($parameters['task_id']);
				if ($task->error()) {
					$this->_error = "Error finding task: ".$task->error();
					return false;
				}
				if (! $task->id) {
					$this->_error = "Task not found";
					return false;
				}
			}
			else {
				$this->_error = "task id required";
				return false;
			}

			if (get_mysql_date($parameters['date_added'])) {
				$date_added = get_mysql_date($parameters['date_added']);
			}
			else {
				$date_added = date('Y-m-d H:i:s');
			}

			if (isset($parameters['person_id'])) {
				$person = new \Register\Person($parameters['person_id']);
				if (! $person->id) {
					$this->_error = "Person not found";
					return false;
				}
			}
			elseif (isset($parameters['person_code'])) {
				$person = new \Register\Person();
				$person->get($parameters['person_code']);
				if (! $person->id) {
					$this->_error = "Person not found";
					return false;
			}
			else {
				$person = new \Register\Person($GLOBALS['_SESSION_']->customer->id);
			}

			$add_object_query = "
				INSERT
				INTO	engineering_events
				(		task_id,person_id,date_event,description)
				VALUES
				(		?,?,?,?)
			";

			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$task->id,
					$person_id,
					$date_added,
					$parameters['description']
				)
			);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Engineering::Event::add(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}

			return $this->update($parameters);
		}

		public function update($parameters = array()) {
			$update_object_query = "
				UPDATE	engineering_events
				SET		id = id
			";

			$bind_params = array();

			if (isset($parameters['description'])) {
				$update_object_query .= ",
						description = ?";
				array_push($bind_params,$parameters['description']);
			}

			if (isset($parameters['person_id']) && is_numeric($parameters['person_id'])) {
				$update_object_query .= ",
						person_id = ?";
				array_push($bind_params,$parameters['person_id']);
			}

			if (isset($parameters['date_event']) && get_mysql_date($parameters['date_event'])) {
				$update_object_query .= ",
						date_event = ?";
				array_push($bind_params,$parameters['date_event']);
			}

			$update_object_query .= "
				WHERE	id = ?
			";
			array_push($bind_params,$this->id);

			$GLOBALS['_database']->Execute($update_object_query,$bind_params);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Engineering::Events::update(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}

			return $this->details();
		}

		public function details() {
			$get_object_query = "
				SELECT	*
				FROM	engineering_events
				WHERE	id = ?
			";

			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($this->id)
			);

			if (! $rs) {
				$this->_error = "SQL Error in Engineering::Event::details(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			};

			$object = $rs->FetchNextObject(false);

			$this->date_event = $object->date_event;
			$this->person_id = $object->person_id;
			$this->task_id = $object->task_id;
			$this->description = $object->description;

			return true;
		}

		public function person() {
			return new \Register\Person($this->person_id);
		}

		public function task() {
			return new \Engineering\Task($this->task_id);
		}

		public function error() {
			return $this->_error;
		}
	}
?>
