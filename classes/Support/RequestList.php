<?
	namespace Support;

	class RequestList {
		public $error;
		public $count;
	
		public function find($parameters = array()) {
			# Get Requests for Admin
			$find_requests_query = "
				SELECT	sr.id
				FROM	support_requests sr
				WHERE	id = id
			";
			if ($GLOBALS['_SESSION_']->customer->has_role("support manager")) {
				# No Special Limits
			}
			# Get Requests for Organization Member
			elseif ($GLOBALS['_SESSION_']->customer->organization->id > 0) {
				$find_requests_query .= "
				AND		sr.organization_id = ".$GLOBALS['_SESSION_']->customer->organization->id;
			}
			# Get Requests for Individual
			elseif ($GLOBALS['_SESSION_']->customer->id)
				$find_requests_query .= "
				AND		sr.customer_id = ".$GLOBALS['_SESSION_']->customer->id;
			else {
				$this->error = "Authentication required";
				return null;
			}
			
			if (preg_match('/^[\w\s]+$/',$parameters['status'])) {
				$find_requests_query .= "\tAND	status = ".$parameters['status']."\n";
			}

			$rs = $GLOBALS['_database']->Execute($find_requests_query);
			if (! $rs) {
				$this->error = "SQL Error in SupportRequest::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$requests = array();
			while (list($id) = $rs->FetchRow()) {
				$request = new Request($id);
				array_push($requests,$request);
			}
			return $requests;
		}
	}
?>