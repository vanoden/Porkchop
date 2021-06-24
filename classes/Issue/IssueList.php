<?php
	namespace Issue;
	
	class IssueList {
		private $_count;
		private $_error;

		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	issue_issues
				WHERE	id = id
			";

			if (isset($parameters['priority']) && is_array($parameters['priority'])) {
				$find_objects_query .= "
					AND priority IN (";
				$first = true;
				foreach ($parameters['priority'] as $priority) {
					if (in_array($priority,array('NORMAL','IMPORTANT','CRITICAL','EMERGENCY'))) {
						if (! $first) $find_objects_query .= ",";
						$find_objects_query .= "'".$priority."'";
						$first = false;
					}
				}
				$find_objects_query .= ")";
			}

			if (isset($parameters['status']) && is_array($parameters['status'])) {
				$find_objects_query .= "
					AND status IN (";
				$first = true;
				foreach ($parameters['status'] as $status) {
					if (in_array($status,array('NEW','HOLD','OPEN','CANCELLED','COMPLETE','APPROVED'))) {
						if (! $first) $find_objects_query .= ",";
						$find_objects_query .= "'".$status."'";
						$first = false;
					}
				}
				$find_objects_query .= ")";
			}
			
			if (isset($parameters['user_requested_id'])) {
				$user = new \Register\Customer($parameters['user_requested_id']);
				if ($user->id)
					$find_objects_query .= "
					AND	user_requested_id = ".$user->id;
				else {
					$this->_error = "User not found";
					return null;
				}
			}

			if (isset($parameters['user_assigned_id'])) {
				$user = new \Register\Customer($parameters['user_assigned_id']);
				if ($user->id) {
					$find_objects_query .= "
					AND	user_assigned_id = ".$user->id;
				}
				else {
					$this->_error = "User not found";
					return null;
				}
			}

			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if (! $rs) {
				$this->_error = "SQL Error in Issue::ProductList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$objects = array();
			while(list($id) = $rs->FetchRow()) {
				$object = new Issue($id);
				array_push($objects,$object);
			}
			return $objects;
		}

		public function error() {
			return $this->_error;
		}
	}
