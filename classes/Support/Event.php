<?
	namespace Support;

	class Event {
		public $error;
		public $id;

		public function __construct($id = 0) {
		}

		public function add($parameters = array()) {
			if (! $parameters['status']) $parameters['status'] = 'NEW';
			if (! role('support admin'))
				$parameters['customer_id'] = $GLOBALS['_SESSION_']->customer->id;

			$add_object_query = "
				INSERT
				INTO	support_events
				(		request_id,
						tech_id,
						date_event,
						comment
				)
				VALUES
				(		?,?,sysdate(),?)
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$parameters['request_id'],
					$parameters['tech_id'],
					$parameters['comment']
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in SupportEvent::add: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($this->id);
		}
		public function update($id,$parameters = array()) {
			return $this->details($id);
		}
		public function find($parameters = array()) {
			# Get Requests for Admin
			$find_requests_query = "
				SELECT	se.id,
				FROM	support_events se
				WHERE	se.id = se.id
			";
			if (role("support manager"))
			{
				# No Special Limits
			}
			# Get Requests for Organization Member
			elseif ($GLOBALS['_SESSION_']->customer->organization->id)
			{
				# Get Organization Members
				$_customer = new RegisterCustomer();
				$customers = $_customer->find(array("organization_id" => $GLOBALS['_SESSION_']->customer->organization->id));
				$array = array();
				
				$find_requests_query .= "
				AND		se.customer_id in (".join(",",$customers).")";
			}
			# Get Requests for Individual
			else
				$find_requests_query = "";
	
			if (preg_match('/^\d+$/',$parameters['id']))
			{
				$find_requests_query .= "\tAND	id = ".$parameters['id']."\n";
			}
			if (preg_match('/^[\w\s]+$/',$parameters['status']))
			{
				$find_requests_query .= "\tAND	status = ".$parameters['status']."\n";
			}
			if (role("support manager"))
			{
				if (preg_match('/^\d+$/',$parameters['organization_id']))
				{
					$find_requests_query .= "\tAND	organization_id = ".$parameters['organization_id']."\n";
				}
			}
			else
			{
				$find_requests .= '';
			}

			$rs = $GLOBALS['_database']->Execute($find_requests_query);
			if (! $rs)
			{
				$this->error = "SQL Error in SupportRequest::find: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			else
			{
				$requests = array();
			}
		}

		private function details($id)
		{
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
				array($id)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in SupportRequest::details: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $rs->FetchObject();
		}
	}
?>
