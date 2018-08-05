<?php
	namespace Issue;

	class Issue {
		public $id;
		public $title;
		public $description;
		public $date_reported;
		private $_user_reported_id;
		public $date_assigned;
		private $_user_assigned_id;
		private $_product_id;
		public $status;
		public $priority;
		private $_error;

		public function __construct($id = null) {
			if (isset($id)) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add ($parameters = array()) {
			if (! isset($parameters['title'])) {
				$this->_error = "title required";
				return false;
			}

			if (isset($parameters['status'])) {
				$parameters['status'] = strtoupper($parameters['status']);
				if (! in_array($parameters['status'],array('NEW','HOLD','OPEN','CANCELLED','COMPLETE','APPROVED'))) {
					$this->_error = "Invalid status";
					return false;
				}
			}
			else {
				$parameters['status'] = 'NEW';
			}

			if (isset($parameters['priority'])) {
				$parameters['priority'] = strtoupper($parameters['priority']);
				if (! in_array($parameters['priority'],array('NORMAL','IMPORTANT','CRITICAL','EMERGENCY'))) {
					$this->_error = "Invalid priority";
					return false;
				}
			}
			else {
				$parameters['priority'] = 'NORMAL';
			}

			if (isset($parameters['user_reported_id'])) {
				$user = new \Register\Customer($parameters['user_reported_id']);
				if (! $user->id) {
					$this->_error = "Reporting User Not Found";
					return false;
				}
			}
			elseif (isset($GLOBALS['_SESSION_']->customer->id)) {
				$user = new \Register\Customer($GLOBALS['_SESSION_']->customer->id);
			}
			else {
				$this->_error = "Reporting User Required";
				return false;
			}
			
			if (isset($parameters['internal'])) {
				if (is_bool($parameters['internal'])) {
					if ($parameters['internal'])
						$internal = 1;
					else
						$internal = 0;
				}
				else {
					$this->_error = "Invalid value for internal";
					return null;
				}
			}
			else {
				$internal = 0;
			}

			$this->code = uniqid();

			$add_object_query = "
				INSERT
				INTO	issue_issues
				(		code,
						title,
						description,
						date_reported,
						user_reported_id,
						date_assigned,
						user_assigned_id,
						date_completed,
						date_approved,
						product_id,
						status,
						internal,
						priority
				)
				VALUES
				(		?,?,?,sysdate(),?,null,null,null,null,?,?,?,?)
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$this->code,
					$parameters['title'],
					$parameters['description'],
					$user->id,
					$parameters['product_id'],
					$parameters['status'],
					$internal,
					$parameters['priority']
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Issue::Issue::add(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}
		
		public function update($parameters = array()) {
			$update_object_query  =	"
				UPDATE	issue_issues
				SET		id = id
			";
			if (isset($parameters['user_assigned_id'])) {
				$update_object_query = ",
					user_assigned_id = ".$GLOBALS['_database']->qstr($parameters['user_assigned_id']).",
					date_assigned = sysdate()
				";
			}
			if (isset($parameters['status'])) {
				if ($parameters['status'] == 'COMPLETE') {
					$update_object_query .= ",
						status = 'COMPLETE',
						date_completed = sysdate()
					";
				}
				elseif ($parameters['status'] == 'APPROVED') {
					$user = new \Register\Customer($parameters['user_approved_id']);
					if (! is_numeric($user->id)) {
						$this->_error = "Must specify approving user";
						return null;
					}
					$update_object_query .= ",
						status = 'APPROVED',
						date_approved = sysdate(),
						user_approved_id = ".$user->id;
				}
				elseif ($parameters['status'] == 'REOPENED') {
					$update_object_query = ",
						status = 'REPOPENED',
						date_completed = null
					";
				}
			}

			$update_object_query .= "
				WHERE	id = ?
			";
			$GLOBALS['_database']->Execute(
				$update_object_query,
				array($this->id)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Issue::Issue::update(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $this->details();
		}

		public function details() {
			$get_object_query = "
				SELECT	*
				FROM	issue_issues
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($this->id)
			);
			if (! $rs) {
				$this->_error = "SQL Error in Issue::Issue::details(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$object = $rs->FetchNextObject(false);
			$this->id = $object->id;
			$this->code = $object->code;
			$this->title = $object->title;
			$this->description = $object->description;
			$this->status = $object->status;
			$this->priority = $object->priority;
			$this->date_reported = $object->date_reported;
			$this->_user_reported_id = $object->user_reported_id;
			$this->user_reported = new \Register\Customer($object->user_reported_id);
			$this->date_assigned = $object->date_assigned;
			$this->_user_assigned_id = $object->user_assigned_id;
			$this->user_assigned = new \Register\Customer($object->user_assigned_id);
			$this->date_completed = $object->date_assigned;
			$this->date_approved = $object->date_approved;
			$this->_user_approved_id = $object->user_approved_id;
			$this->user_approved = new \Register\Customer($object->user_approved_id);
			$this->internal = $object->internal;
			
			return true;
		}

		public function get($code) {
			$get_object_query = "
				SELECT	id
				FROM	issue_issues
				WHERE	code = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($code)
			);
			if (! $rs) {
				$this->_error = "SQL Error in Issue::Issue::get(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
		}
		public function error() {
			return $this->_error;
		}
	}
?>
