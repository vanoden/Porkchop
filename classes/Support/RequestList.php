<?php
	namespace Support;

	class RequestList {
		private $_error;
		public $count;
	
		public function find($parameters = array()) {
		
			// Get Requests for Admin
			$find_requests_query = "
				SELECT	sr.id
				FROM	support_requests sr
				WHERE	sr.id = sr.id
			";
			
			if ($GLOBALS['_SESSION_']->customer->has_role("support manager")) {
				// No Special Limits
			}
			
			// Get Requests for Organization Member
			elseif ($GLOBALS['_SESSION_']->customer->organization->id > 0) {
				$find_requests_query .= "
				    AND sr.organization_id = ".$GLOBALS['_SESSION_']->customer->organization->id;
			}
			
			// Get Requests for Individual
			elseif ($GLOBALS['_SESSION_']->customer->id)
				$find_requests_query .= "
				    AND sr.customer_id = ".$GLOBALS['_SESSION_']->customer->id;
			else {
				$this->_error = "Authentication required";
				return null;
			}

			// Get Requests for Organization
			if (isset($parameters['organization_id'])) {
				$organization = new \Register\Organization($parameters['organization_id']);
				if (! $organization->exists()) {
					$this->_error = "Organization not found";
					return null;
				}
				$members = $organization->members();
				$memberlist = array();
				foreach ($members as $member) {
					array_push($memberlist,$member->id);
				}
				$find_requests_query .= "
					AND	customer_id IN (".join(',',$memberlist).")";
			}

			if (isset($parameters['status'])) {
				if (is_array($parameters['status'])) {
					$find_requests_query .= "
					    AND	status IN (";
					$started = 0;
					foreach ($parameters['status'] as $status) {
						if (! in_array($status,array('NEW','OPEN','CANCELLED','CLOSED'))) {
							$this->_error = "Invalid status '$status'";
							return false;
						}
						if ($started) $find_requests_query .= ",";
						$find_requests_query .= "'$status'";
						$started = 1;
					}
					$find_requests_query .= ")";
				}
				
                if (!is_array($parameters['status'])) {
				    if (preg_match('/^[\w\s]+$/', $parameters['status'])) $find_requests_query .= "\tAND	status = ".$parameters['status']."\n";
				}
			}

            // search for requestList looks like only by code would be meaningful
            if (isset($parameters['searchTerm'])) {
                $find_requests_query .= "
				                AND sr.code LIKE '%" . $parameters['searchTerm'] . "%'";
            }

			$find_requests_query .= "
				ORDER BY date_request DESC";
				
			$rs = $GLOBALS['_database']->Execute($find_requests_query);
			if (! $rs) {
				$this->_error = "SQL Error in SupportRequest::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$requests = array();
			while (list($id) = $rs->FetchRow()) {
				$request = new Request($id);
				array_push($requests,$request);
			}
			return $requests;
		}
		public function error() {
			return $this->_error;
		}
	}
