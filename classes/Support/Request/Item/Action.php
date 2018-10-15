<?
	namespace Support\Request\Item;

	class Action {
		private $_error;
		public $id;
		public $item;
		public $request;
		public $type;
		public $requestedBy;
		public $assignedTo;
		public $date_action;
		public $status;
		public $description;
		public $event_type = 'action';

		public function __construct($id = 0) {
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters = array()) {
			$item = new \Support\Request\Item($parameters['item_id']);
			if ($item->error()) {
				$this->_error = $item->error();
				return false;
			}
			if (! $item->id) {
				$this->_error = "Request item not found";
				return false;
			}
			$requestedBy = new \Register\Customer($parameters['requested_id']);
			if (! $requestedBy->id) {
				$this->_error = "Customer required";
				return false;
			}
			if (isset($parameters['assigned_id']) && $parameters['assigned_id'] > 0) {
				$assignedTo = new \Register\Customer($parameters['assigned_id']);
				if (! $assignedTo->id) {
					$this->_error = "Assigned Person not found";
					return false;
				}
			}
			if ($this->valid_status($parameters['status'])) {
				$status = $parameters['status'];
			}
			elseif($parameters['status']) {
				$this->_error = "Invalid status";
				return false;
			}
			else {
				$status = 'NEW';
			}

			if (get_mysql_date($parameters['date_requested'])) {
				$datetime = get_mysql_date($parameters['date_requested']);
			}
			elseif ($parameters['date_requested']) {
				$this->_error = "Invalid request date";
				return false;
			}
			else {
				$datetime = date('Y-m-d H:i:s');
			}

			$add_object_query = "
				INSERT
				INTO	support_item_actions
				(		item_id,
						date_entered,
						date_requested,
						requested_id,
						type,
						status,
						description,
						assigned_id
				)
				VALUES
				(		?,sysdate(),?,?,?,?,?,?)
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$item->id,
					$datetime,
					$requestedBy->id,
					$parameters['type'],
					$status,
					$parameters['description'],
					$assignedTo->id
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Support::Request::Action::add(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			$this->status = $status;
			$this->description = $parameters['description'];
			$this->type = $parameters['type'];
			return $this->update($parameters);
		}
		
		public function update($parameters) {
			$update_action_query = "
				UPDATE	support_item_actions
				SET		id = id
			";
			
			$bind_params = array();
			if (isset($parameters['status']) && $parameters['status'] != $this->status) {
				$update_action_query .= ",
				status = ?";
				array_push($bind_params,$parameters['status']);
				if ($parameters['status'] == 'COMPLETED') {
					$update_action_query .= ",
					date_completed = sysdate()";
				}
			}
			
			if (isset($parameters['assigned_id']) && $parameters['assigned_id'] > 0) {
				$admin = new \Register\Customer($parameters['assigned_id']);
				if ($admin->error) {
					$this->_error = $admin->error;
					return false;
				}
				if (! $admin->id) {
					$this->_error = "Admin not found";
					return false;
				}
				if ($admin->id != $this->assignedTo->id) {
					$update_action_query .= ",
						assigned_id = ?,
						date_assigned = sysdate()";
					array_push($bind_params,$parameters['assigned_id']);
				}
			}

			if (isset($parameters['description']) && $parameters['description'] != $this->description) {
				$update_action_query .= ",
					description	= ?";
				array_push($bind_params,$parameters['description']);
			}

			$update_action_query .= "
				WHERE	id = ?
			";
			array_push($bind_params,$this->id);

			$GLOBALS['_database']->Execute($update_action_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Support::Request::Action::update(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			return $this->details();
		}

		private function details() {
			# Get Request Details
			$get_action_query = "
				SELECT	*
				FROM	support_item_actions
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_action_query,
				array($this->id)
			);
			query_log($get_action_query);
			if (! $rs) {
				$this->_error = "SQL Error in SupportRequest::details: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$record = $rs->FetchNextObject(false);
			$this->requestedBy = new \Register\Customer($record->requested_id);
			$this->assignedTo = new \Register\Customer($record->assigned_id);
			$this->date_requested = $record->date_requested;
			$this->type = $record->type;
			$this->status = $record->status;
			$this->description = $record->description;
			$this->item = new \Support\Request\Item($record->item_id);

			return true;
		}

		public function error() {
			return $this->_error;
		}

		public function addEvent($parameters) {
			$parameters['action_id'] = $this->id;
			$event = new \Support\Request\Item\Action\Event();
			$event->add($parameters);
			if ($event->error()) {
				$this->_error = $event->error();
				return false;
			}
			return true;
		}
		public function valid_status($status) {
			if (in_array($status,array("NEW","ASSIGNED","ACTIVE","PENDING CUSTOMER","PENDING VENDOR","CANCELLED","COMPLETED","CLOSED"))) return true;
			return false;
		}
	}
?>
