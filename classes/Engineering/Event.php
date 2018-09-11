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

			$add_object_query = "
				INSERT
				INTO	engineering_events
				(		task_id,person_id,date_event,description)
				VALUES
				(		?,?,sysdate(),?)
			";

			$GLOBALS['_database']->Execute(
				$add_object_query,
				array($task->id,$parameters['person_id'],$parameters['description'])
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

			if (isset($parameters['description']))
				$update_object_query .= ",
						description = ".$GLOBALS['_database']->qstr($parameters['description'],get_magic_quotes_gpc());

			if (isset($parameters['person_id']) && is_numeric($parameters['person_id'])) {
				$update_object_query .= ",
						person_id = ".$parameters['person_id'];
			}

			if (isset($parameters['date_event']) && get_mysql_date($parameters['date_event'])) {
				$update_object_query .= ",
						date_event = ".$GLOBALS['_database']->qstr($parameters['date_event'],get_magic_quotes_gpc());
			}

			$update_object_query .= "
				WHERE	id = ?
			";

			$GLOBALS['_database']->Execute(
				$update_object_query,
				array($this->id)
			);

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
			$this->description = $object->description;

			return true;
		}

		public function person() {
			return new \Register\Person($this->person_id);
		}

		public function error() {
			return $this->_error;
		}
	}
?>
