<?
	namespace Support;

	class Request {
		public $error;
		public $id;
		public $customer_id;
		public $tech_id;
		public $status;
		public $date_created;

		public function __construct($id = 0) {
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters = array()) {
			if (! $parameters['code']) $parameters['code'] = uniqid();
			if (! $parameters['status']) $parameters['status'] = 'NEW';
			if (! $GLOBALS['_SESSION_']->customer->has_role('support manager')) {
				$parameters['customer_id'] = $GLOBALS['_SESSION_']->customer->id;
				$parameters['organization_id'] = $GLOBALS['_SESSION_']->customer->organization->id;
			}

			$add_object_query = "
				INSERT
				INTO	support_requests
				(		code,
						customer_id,
						organization_id,
						tech_id,
						date_request,
						status
				)
				VALUES
				(		?,?,?,?,sysdate(),?)
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$parameters['code'],
					$parameters['customer_id'],
					$parameters['organization_id'],
					$parameters['tech_id'],
					$parameters['status']
				)
			);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in Support::Request::add(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}

		public function update($parameters = array()) {
			$update_object_query = "
				UPDATE	support_requests
				SET		id = id";
			
			if ($parameters['status'])
				$update_object_query .= ",
				status	= ".$GLOBALS['_database']->qstr($parameters['status'],get_magic_quotes_gpc);
			if ($parameters['tech_id'] and role('support manager'))
				$update_object_query .= ",
				tech_id = ".$GLOBALS['_database']->qstr($parameters['tech_id'],get_magic_quotes_gpc);

			$update_object_query .= "
				WHERE	id = ?";
			$GLOBALS['_database']->Execute(
				$update_object_query,
				array($this->id)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Support::Request::update(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $this->details($id);
		}

		private function details() {
			# Get Request Details
			$get_request_query = "
				SELECT	id,
						code,
						status,
						tech_id,
						customer_id,
						date_request
				FROM	support_requests
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_request_query,
				array($this->id)
			);
			if (! $rs) {
				$this->error = "SQL Error in SupportRequest::details: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$record = $rs->FetchNextObject(false);
			$this->code = $record->code;
			$this->status = $record->status;
			$this->tech = new \Register\Customer($record->tech_id);
			$this->customer = new \Register\Customer($record->customer_id);
			$this->date_request = $record->date_request;

			return 1;
		}

		public function statuses() {
			$statuses = array(
					"NEW",
					"CANCELLED",
					"ASSIGNED",
					"OPEN",
					"PENDING CUSTOMER",
					"PENDING VENDOR",
					"COMPLETE",
					"CLOSED"
			);

			return $statuses;
		}
	}
?>