<?php
	namespace Support\Request\Item\Action;

	class Event {
		private $_error;
		public $id;
		public $date_event;
		public $user;
		public $description;

		public function __construct($id = 0) {
			if (is_numeric($id) && $id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters) {
			if (isset($parameters['action_id'])) {
				$action =  new \Support\Request\Item\Action($parameters['action_id']);
				if ($action->error()) {
					$this->_error = $action->error;
					return false;
				}
				if (! $action->id) {
					$this->_error = "Action not found";
					return false;
				}
			}
			else {
				$this->_error = "Action required";
				return false;
			}
			if (isset($parameters['user_id'])) {
				$user = new \Register\Customer($parameters['user_id']);
				if ($user->error) {
					$this->_error = $user->error;
					return false;
				}
				if (! $user->id) {
					$this->_error = "User not found";
					return false;
				}
			}
			else {
				$this->_error = "User Required";
				return false;
			}

			if (isset($parameters['date_event'])) {
				if (get_mysql_date($parameters['date_event'])) {
					$date_event = get_mysql_date($parameters['date_event']);
				}
				else {
					$this->_error = "Invalid date";
					return false;
				}
			}
			else $date_event = date('Y-m-d H:i:s');

			$add_object_query = "
				INSERT
				INTO	support_action_events
				(		action_id,
						date_event,
						user_id,
						description
				)
				VALUES
				(		?,?,?,?)
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$action->id,
					$date_event,
					$user->id,
					$parameters['description']
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Support::Request::Event::add(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($this->id) = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}

		public function update($parameters) {
			return $this->details();
		}

		public function details() {
			$get_object_query = "
				SELECT	*
				FROM	support_action_events
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_object_query,array($this->id));
			if (! $rs) {
				$this->_error = "SQL Error in Support::Request::Event::details(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$object = $rs->FetchNextObject(false);
			$this->id = $object->id;
			$this->action = new \Support\Request\Item\Action($object->action_id);
			$this->user = new \Register\Customer($object->user_id);
			$this->date_event = $object->date_event;
			$this->description = $object->description;
			return true;
		}

		public function error() {
			return $this->_error;
		}
	}
