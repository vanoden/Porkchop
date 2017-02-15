<?php
	namespace Register;

	class CustomerList {
		public $count = 0;

		public function expire($date_threshold) {
			if (get_mysql_date($date_threshold))
				$date = get_mysql_date($date_threshold);
			else {
				$this->error = "Invalid date: '$date_threshold'";
				return null;
			}

			$find_people_query = "
				SELECT	u.id,
						u.login,
						u.date_created,
						IFNULL(max(s.last_hit_date),'0000-00-00 00:00:00') last_login
				FROM	register_users u
				LEFT OUTER JOIN session_sessions s
				ON		s.user_id = u.id
				AND		s.company_id = ".$GLOBALS['_SESSION_']->company."
				WHERE	u.status in ('ACTIVE','NEW')
				GROUP BY u.id
				HAVING	last_login < '$date'
				AND		u.date_created < '$date'
			";

			$people = $GLOBALS['_database']->Execute($find_people_query);
			if (! $people) {
				$this->error = "SQL Error in RegisterCustomers::expire: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$count = 0;
			while($record = $people->FetchNextObject(false)) {
				app_log("Expiring ".$record->login."' [".$record->id."]",'notice');
				$customer = new RegisterCustomer($record->id);
				$customer->update($record->id,array("status" => "EXPIRED"));
				$count ++;
			}
			return $count;
		}
		public function find($parameters = array()) {
			$find_person_query = "
				SELECT	id
				FROM	register_users
				WHERE	id = id";
	
			if (isset($parameters['id']) && preg_match('/^\d+$/',$parameters['id'])) {
				$find_person_query .= "
				AND		id = ".$parameters['id'];
			}
			elseif (isset($parameters['id'])) {
				$this->error = "Invalid id in Person::find";
				return null;
			}
			if (isset($parameters['code'])) {
				$find_person_query .= "
				AND		login = ".$GLOBALS['_database']->qstr($parameters['code'],get_magic_quotes_gpc());
			}
			if (isset($parameters['status'])) {
				if (is_array($parameters['status'])) {
					$count = 0;
					$find_person_query .= "
					AND	status IN (";
					foreach ($parameters['status'] as $status) {
						if ($count > 0) $find_person_query .= ","; 
						$count ++;
						if (preg_match('/^[\w\-\_\.]+$/',$status))
						$find_person_query .= $status;
					}
				}
				else {
					$find_person_query .= "
						AND		status = ".$GLOBALS['_database']->qstr($parameters['status'],get_magic_quotes_gpc());
				}
			}
			else {
				$find_person_query .= "
				AND		status not in ('EXPIRED','HIDDEN','DELETED')";
			}
	
			if (isset($parameters['first_name'])) {
				$find_person_query .= "
				AND		first_name = ".$GLOBALS['_database']->qstr($parameters['first_name'],get_magic_quotes_gpc());
			}
	
			if (isset($parameters['last_name'])) {
				$find_person_query .= "
				AND		last_name = ".$GLOBALS['_database']->qstr($parameters['last_name'],get_magic_quotes_gpc());
			}
	
			if (isset($parameters['email_address'])) {
				$find_person_query .= "
				AND		email_address = ".$GLOBALS['_database']->qstr($parameters['email_address'],get_magic_quotes_gpc());
			}

			if (isset($parameters['department_id'])) {
				$find_person_query .= "
				AND		department_id = ".$GLOBALS['_database']->qstr($parameters['department_id'],get_magic_quotes_gpc());
			}
			if (isset($parameters['organization_id'])) {
				$find_person_query .= "
				AND		organization_id = ".$GLOBALS['_database']->qstr($parameters['organization_id'],get_magic_quotes_gpc());
			}

			if (preg_match('/^(login|first_name|last_name|organization_id)$/',$parameters['_sort'])) {
				$find_person_query .= " ORDER BY ".$parameters['_sort'];
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

			$rs = $GLOBALS['_database']->Execute($find_person_query);
			if (! $rs) {
				$this->error = "SQL Error in RegisterPerson::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$people = array();
			while (list($id) = $rs->FetchRow()) {
				$customer = new Customer($id);
				$this->count ++;
				array_push($people,$customer);
			}
			return $people;
		}
	}
?>
