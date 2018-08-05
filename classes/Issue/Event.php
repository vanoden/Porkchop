<?php
	namespace Issue;
	
	class Event {
		public $id;
		private $_error;
		private $_user_id;
		private $_issue_id;
		public $status_previous;
		public $status_new;
		public $date_event;
		public $description;

		public function __construct($id = 0) {
			if (is_numeric($id) && $id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters = array()) {
			$add_object_query = "
				INSERT
				INTO	issue_events
			";
		}

		public function update($parameters = array()) {
			$update_object_query = "
				UPDATE	issue_events
				SET		id = id
			";
		}

		public function details() {
			$get_object_query = "
				SELECT	*
				FROM	issue_events
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($this->id)
			);
			
		}
	}
?>