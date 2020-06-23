<?php
	namespace Support\Request\Item\Action;

	class EventList {
		private $_error;
		public $count;
		
		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	support_action_events
				WHERE	id = id
			";
			$bind_params = array();
			
			if (isset($parameters['action_id'])) {
				$action = new \Support\Request\Item\Action($parameters['action_id']);
				if ($action->error()) {
					$this->_error = $action->error;
					return false;
				}
				if (! $action->id) {
					$this->_error = "Action not found";
					return false;
				}
				$find_objects_query .= "
				AND		action_id = ?
				";
				array_push($bind_params,$action->id);
			}
            $rs = executeSQLByParams($find_objects_query, $bind_params);
			if (! $rs) {
				$this->_error = "SQL Error in Support::Request::EventList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$object = new Event($id);
				array_push($objects,$object);
			}
			return $objects;
		}
	}
