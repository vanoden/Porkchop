<?php
	namespace Register;

	class CustomerList {
		public $error;
		public $count = 0;

		public function flagActive() {
			$find_session_query = "
				SELECT 	MAX(user_id)
				FROM	session_sessions
				WHERE	user_id > 0
				AND		session.last_hit > date_sub(sysdate(),interval 3 month)
				GROUP BY user_id
			";
			$rs = $GLOBALS['_database']->Execute($find_session_query);
			if (! $rs) {
				$this->error = "SQL Error in Register::CustomerList::flagActive(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$counter = 0;
			while (list($id) = $rs->FetchRow()) {
				$counter ++;
				$update_customer_query = "
					UPDATE	register_users
					SET		status = 'ACTIVE'
					WHERE	id = ?
				";
				$GLOBALS['_database']->Execute(
					$update_customer_query,
					array($id)
				);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Register::CustomerList::flagActive(): ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
			}
			app_log("Activated ".$counter." customers",'info',__FILE__,__LINE__);
			return $counter;
		}
		public function expireInactive($age = 14) {
			if (! is_numeric($age)) {
				$this->error = "Age must be a number";
				return null;
			}

			$update_people_query = "
				UPDATE	register_users
				SET		status = 'EXPIRED'
				WHERE	status = 'NEW'
				AND		date_created < date_sub(sysdate(),INTERVAL ? day)
			";

			$GLOBALS['_database']->Execute(
				$update_people_query,
				array($age)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Register::CustomerList::expireInactive(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			return true;
		}
		public function expire($date_threshold) {
			if (get_mysql_date($date_threshold))
				$date = get_mysql_date($date_threshold);
			else {
				$this->error = "Invalid date: '$date_threshold'";
				return null;
			}

			$bind_params = array();
			$find_people_query = "
				SELECT	u.id,
						u.login,
						u.date_created,
						IFNULL(max(s.last_hit_date),'0000-00-00 00:00:00') last_login
				FROM	register_users u
				LEFT OUTER JOIN session_sessions s
				ON		s.user_id = u.id
				AND		s.company_id = ?
				WHERE	u.status in ('ACTIVE','NEW')
				GROUP BY u.id
				HAVING	last_login < ?
				AND		u.date_created < ?
			";
			array_push($bind_params,$GLOBALS['_SESSION_']->company->id,$date,$date);

			$people = $GLOBALS['_database']->Execute($find_people_query,$bind_params);
			if (! $people) {
				$this->error = "SQL Error in Register::Customers::expire(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$count = 0;
			while($record = $people->FetchNextObject(false)) {
				app_log("Expiring ".$record->login."' [".$record->id."]",'notice');
				$customer = new Customer($record->id);
				$customer->update(array("status" => "EXPIRED"));
				$count ++;
			}
			return $count;
		}
		public function find($parameters = array(),$count = false) {
			if ($count == true) $ADODB_COUNTRECS = true;

			$bind_params = array();

			$find_person_query = "
				SELECT	id
				FROM	register_users
				WHERE	id = id";

			if (!empty($parameters['_search'])) $searchTerm = $parameters['_search'];
			elseif (!empty($parameters['searchTerm'])) $searchTerm = $parameters['searchTerm'];

			if (isset($searchTerm)) {
				if (! preg_match('/^[\w\-\.\_\s\*]{3,64}$/',$searchTerm)) {
					$this->error = "Invalid search string";
					return null;
				}
				if (preg_match('/\*/',$searchTerm))
					$string = preg_replace('/\*/','%',$searchTerm);
				else
					$string = '%'.$searchTerm.'%';

				$find_person_query .= "
				AND		(	login LIKE '$string'
					OR		first_name LIKE '$string'
					OR		last_name LIKE '$string'
					OR		middle_name LIKE '$string'
					OR		last_name LIKE '$string'
				)
				";
			}
			if (isset($parameters['id']) && preg_match('/^\d+$/',$parameters['id'])) {
				$find_person_query .= "
				AND		id = ?";
				array_push($bind_params,$parameters['id']);
			}
			elseif (isset($parameters['id'])) {
				$this->error = "Invalid id in Person::find";
				return null;
			}
			if (isset($parameters['code'])) {
				$find_person_query .= "
				AND		login = ?";
				array_push($bind_params,$parameters['code']);
			}
			if (isset($parameters['status'])) {
				if (is_array($parameters['status'])) {
					$icount = 0;
					$find_person_query .= "
					AND	status IN (";
					foreach ($parameters['status'] as $status) {
						if ($icount > 0) $find_person_query .= ","; 
						$icount ++;
						if (preg_match('/^[\w\-\_\.]+$/',$status))
						$find_person_query .= "'".$status."'";
					}
					$find_person_query .= ")";
				}
				else {
					$find_person_query .= "
						AND		status = ?";
					array_push($bind_params,$parameters['status']);
				}
			}
			else {
				$find_person_query .= "
				AND		status not in ('EXPIRED','HIDDEN','DELETED')";
			}
	
			if (isset($parameters['first_name'])) {
				$find_person_query .= "
				AND		first_name = ?";
				array_push($bind_params,$parameters['first_name']);
			}
	
			if (isset($parameters['last_name'])) {
				$find_person_query .= "
				AND		last_name = ?";
				array_push($bind_params,$parameters['last_name']);
			}
	
			if (isset($parameters['email_address'])) {
				$find_person_query .= "
				AND		email_address = ?";
				array_push($bind_params,$parameters['email_address']);
			}

			if (isset($parameters['department_id'])) {
				$find_person_query .= "
				AND		department_id = ?";
				array_push($bind_params,$parameters['department_id']);
			}
			if (isset($parameters['organization_id'])) {
				$find_person_query .= "
				AND		organization_id = ?";
				array_push($bind_params,$parameters['organization_id']);
			}
			if (isset($parameters['automation'])) {
				if ($parameters['automation']) $find_person_query .= "
					AND		automation = 1";
				else $find_person_query .= "
					AND		automation = 0";
			}

			if (isset($parameters['_sort']) && preg_match('/^(login|first_name|last_name|organization_id)$/',$parameters['_sort'])) {
				$find_person_query .= " ORDER BY ".$parameters['_sort'];
			}
			elseif (isset($parameters['sort']) && $parameters['_sort'] == 'full_name') {
				$find_person_query .= " ORDER BY first_name,last_name";
			}
			else
				$find_person_query .= " ORDER BY login";

			if (isset($parameters['_limit']) && preg_match('/^\d+$/',$parameters['_limit'])) {
				if (preg_match('/^\d+$/',$parameters['_offset']))
					$find_person_query .= "
					LIMIT	".$parameters['_offset'].",".$parameters['_limit'];
				else
					$find_person_query .= "
					LIMIT	".$parameters['_limit'];
			}
			query_log($find_person_query,$bind_params,true);
			$rs = $GLOBALS['_database']->Execute($find_person_query,$bind_params);
			if (! $rs) {
				$this->error = "SQL Error in Register::Person::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$people = array();
			while (list($id) = $rs->FetchRow()) {
				if (isset($parameters['role']) || ! $count) {
					$customer = new Customer($id);
				}
				if (isset($parameters['role']) && ! $customer->has_role($parameters['role'])) continue;
				if (! $count) array_push($people,$customer);
				$this->count ++;
			}
			if ($count) return $this->count;
			return $people;
		}
		public function search($search_string,$limit = 0,$offset = 0) {
			if (is_bool($limit) && $limit == true) $count = true;
			else $count = false;

			$bind_params = array();

			app_log("Customer Search Requested",'debug',__FILE__,__LINE__);
			$this->count = 0;

			if (! preg_match('/^[\w\-\.\_\s\*]{3,64}$/',$search_string)) {
				$this->error = "Invalid search string";
				return null;
			}
			if (preg_match('/\*/',$search_string))
				$search_string = preg_replace('/\*/','%',$search_string);
			else
				$search_string = '%'.$search_string.'%';

			if (empty($search_string)) {
				$this->error = "Search string required";
				return null;
			}
			$find_person_query = "
				SELECT	id
				FROM	register_users
				WHERE	id = id
				AND		(	login LIKE '$search_string'
					OR		first_name LIKE '$search_string'
					OR		last_name LIKE '$search_string'
					OR		middle_name LIKE '$search_string'
					OR		last_name LIKE '$search_string'
				)
			";
			if (isset($parameters['status'])) {
				if (is_array($parameters['status'])) {
					$icount = 0;
					$find_person_query .= "
					AND	status IN (";
					foreach ($parameters['status'] as $status) {
						if ($icount > 0) $find_person_query .= ","; 
						$icount ++;
						if (preg_match('/^[\w\-\_\.]+$/',$status))
						$find_person_query .= "'".$status."'";
					}
					$find_person_query .= ")";
				}
				else {
					$find_person_query .= "
						AND		status = ?";
					array_push($bind_params,$parameters['status']);
				}
			}
			else {
				$find_person_query .= "
				AND		status not in ('EXPIRED','HIDDEN','DELETED')";
			}

			if ($count == false && $limit > 0 && preg_match('/^\d+$/',$limit)) {
				if (preg_match('/^\d+$/',$offset))
					$find_person_query .= "
					LIMIT	$offset,$limit";
				else
					$find_person_query .= "
					LIMIT	$limit";
			}
			app_log("Search query: ".$find_person_query,'trace',__FILE__,__LINE__);
			$rs = $GLOBALS['_database']->Execute($find_person_query,$bind_params);
			if (! $rs) {
				$this->error = "SQL Error in \Register\Customer::search(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$people = array();
			while (list($id) = $rs->FetchRow()) {
				if ($count == false) {
					$customer = new Customer($id);
					array_push($people,$customer);
				}
				$this->count ++;
			}
			app_log($this->count." records found",'debug',__FILE__,__LINE__);
			if ($count == true) return $this->count;
			return $people;
		}

		public function error() {
			return $this->error;
		}

		public function count() {
			return $this->count;
		}
	}
