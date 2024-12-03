<?php
	namespace Register;

	class CustomerList Extends \BaseListClass {

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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
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
					$this->SQLError($GLOBALS['_database']->ErrorMsg());
					return null;
				}
			}
			app_log("Activated ".$counter." customers",'info',__FILE__,__LINE__);
			return $counter;
		}
		
		public function expireInactive($age = 14) {
			
			if (! is_numeric($age)) {
				$this->error("Age must be a number");
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			return true;
		}
		
		public function expire($date_threshold) {
			if (get_mysql_date($date_threshold))
				$date = get_mysql_date($date_threshold);
			else {
				$this->error("Invalid date: '$date_threshold'");
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}

			$count = 0;
			while($record = $people->FetchNextObject(false)) {
				app_log("Expiring ".$record->code."' [".$record->id."]",'notice');
				$customer = new Customer($record->id);
				$customer->update(array("status" => "EXPIRED"));
				$count ++;
			}
			return $count;
		}
		
		public function count($parameters = []) {
			if (isset($this->_count)) return $this->_count;
			$this->find($parameters,["count" => true]);
			return $this->_count();
		}
	
		public function findAdvanced(array $parameters, array $advanced, array $controls): array {
			$this->clearError();
			$this->resetCount();

			if (isset($parameters['role'])) app_log("Don't use role as a filter for customers, use Register::Role::Members",'warning');

			$validationclass = new \Register\Customer();

			$database = new \Database\Service();

			$find_person_query = "
				SELECT	id
				FROM	register_users
				WHERE	id = id";

			if (!empty($parameters['_search'])) $searchTerm = $parameters['_search'];
			elseif (!empty($parameters['searchTerm'])) $searchTerm = $parameters['searchTerm'];

			if (isset($searchTerm)) {
				if (! $validationclass->validSearch($searchTerm)) {
					$this->error("Invalid search string");
					return null;
				}
				if (preg_match('/^\*/',$searchTerm) || preg_match('/\*$/',$searchTerm)) {
					$searchTerm = preg_replace('/^\*/','%',$searchTerm);
					$searchTerm = preg_replace('/\*/','%',$searchTerm);
				}
				else
					$searchTerm = '%'.$searchTerm.'%';

				$find_person_query .= "
				AND		(	login LIKE '$searchTerm'
					OR		first_name LIKE '$searchTerm'
					OR		last_name LIKE '$searchTerm'
					OR		middle_name LIKE '$searchTerm'
					OR		last_name LIKE '$searchTerm'
				)
				";
			}
			if (isset($parameters['id']) && preg_match('/^\d+$/',$parameters['id'])) {
				$find_person_query .= "
				AND		id = ?";
				$database->AddParam($parameters['id']);
			}
			elseif (isset($parameters['id'])) {
				$this->error("Invalid id");
				return [];
			}
			if (!empty($parameters['code']) && empty($parameters['login'])) $parameters['login'] = $parameters['code'];

			if (!empty($parameters['login'])) {
				if ($validationclass->validCode($parameters['login'])) {
					$find_person_query .= "
					AND		login = ?";
					$database->AddParam($parameters['login']);
				}
				else {
					$this->error("Invalid login");
					return [];
				}
			}
			if (!empty($parameters['status'])) {
				if (is_array($parameters['status'])) {
					$icount = 0;
					$find_person_query .= "
					AND	status IN (";
					foreach ($parameters['status'] as $status) {
						if ($validationclass->validStatus($status)) {
							if ($icount > 0) $find_person_query .= ","; 
							$icount ++;
							if (preg_match('/^[\w\-\_\.]+$/',$status))
							$find_person_query .= "'".$status."'";
						}
						else {
							$this->error("Invalid status");
							return [];
						}
					}
					$find_person_query .= ")";
				}
				else {
					if ($validationclass->validStatus($parameters['status'])) {
						$find_person_query .= "
						AND		status = ?";
						array_push($bind_params,$parameters['status']);
					}
					else {
						$this->error("Invalid status");
						return [];
					}
				}
			}
			else {
				$find_person_query .= "
				AND		status not in ('EXPIRED','HIDDEN','DELETED')";
			}
	
			if (isset($parameters['first_name'])) {
				if ($validationclass->validName($parameters['first_name'])) {
					$find_person_query .= "
					AND		first_name = ?";
					$database->AddParam($parameters['first_name']);
				}
				else {
					$this->error("Invalid first name");
					return [];
				}
			}
	
			if (isset($parameters['last_name'])) {
				if ($validationclass->validName($parameters['last_name'])) {
					$find_person_query .= "
					AND		last_name = ?";
					$database->AddParam($parameters['last_name']);
				}
				else {
					$this->error("Invalid last name");
					return [];
				}
			}
	
			if (isset($parameters['email_address'])) {
				if ($validationclass->validEmail($parameters['email_address'])) {
					$find_person_query .= "
					AND		email_address = ?";
					$database->AddParam($parameters['email_address']);
				}
				else {
					$this->error("Invalid email address");
					return [];
				}
			}

			if (!empty($parameters['department_id']) && is_numeric($parameters['department_id'])) {

				$find_person_query .= "
				AND		department_id = ?";
				$database->AddParam($parameters['department_id']);
			}

			if (!empty($parameters['organization_id']) && is_numeric($parameters['organization_id'])) {

				$organization = new \Register\Organization($parameters['organization_id']);
				if (!$organization->exists()) {
					$this->error("Invalid organization");
					return [];
				}
				$find_person_query .= "
				AND		organization_id = ?";
				$database->AddParam($organization->id);
			}
			if (isset($parameters['automation'])) {
				if (is_bool($parameters['automation'])) {
					if ($parameters['automation']) $find_person_query .= "
						AND		automation = 1";
					else $find_person_query .= "
						AND		automation = 0";
				}
				elseif (is_numeric($parameters['automation'])) {
					$find_person_query .= "
					AND		automation = ?";
					$database->AddParam($parameters['automation']);
				}
				elseif (!empty($parameters['automation'])) {
					if ($parameters['automation'] == 'true') {
						$find_person_query .= "
						AND		automation = 1";
					}
					elseif ($parameters['automation'] == 'false') {
						$find_person_query .= "
						AND		automation = 0";
					}
					else {
						$this->error("Invalid automation");
						return [];
					}
				}
				else {
					$this->error("Invalid automation");
					return [];
				}
			}

			if (isset($controls['sort']) && $controls['sort'] == 'full_name') {
				$find_person_query .= " ORDER BY first_name,last_name";
			}
			elseif (isset($controls['sort']) && $validationclass->hasField($controls['sort'])) {
				$find_person_query .= " ORDER BY ".$controls['sort'];
			}
			else
				$find_person_query .= " ORDER BY login";
			if (preg_match('/^d/',$controls['order'])) $find_person_query .= " DESC";
			else $find_person_query .= " ASC";

			if (!empty($controls['limit']) && is_numeric($controls['limit'])) {
				if (is_numeric($controls['offset']))
					$find_person_query .= "
					LIMIT ".$controls['offset'].",".$controls['limit'];
				if (! is_numeric($controls['offset']))
					$find_person_query .= "
					OFFSET ".$controls['limit'];
			}

			$rs = $database->Execute($find_person_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			$people = array();
			while (list($id) = $rs->FetchRow()) {
				if (isset($parameters['role']) || ! array_key_exists('ids',$controls) || ! $controls['ids']) {
					$customer = new Customer($id);
				}
				if (isset($parameters['role']) && ! $customer->has_role($parameters['role'])) continue;
				if (array_key_exists('count',$controls) && $controls['count']) {
					// Don't build array
				}
				else array_push($people,$customer);
				$this->incrementCount();
			}

			return $people;
		}
		
		public function searchAdvanced($search_string, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			app_log("Customer Search Requested",'debug',__FILE__,__LINE__);

			if (empty($search_string)) {
				$this->error("Search string required");
				return null;
			}

			if (! $this->validSearchString($search_string)) {
				$this->error("Invalid search string");
				return null;
			}
			$parameters['string'] = $search_string;

			// Search for customers based on basic information
			$find_person_query = "
				SELECT	id
				FROM	register_users";

			if (isset($advanced['tags'])) {
				$tagList = new \Search\TagList();
				$tagIds = $tagList->find(['tags' => $advanced['tags']]);
				$find_person_query .= "
					WHERE	id IN (
						SELECT	object_id
						FROM	search_tags_xref
						WHERE	tag_id IN (".implode(',',$tagIds).")
					)";
			}
			else {
				$find_person_query .= "
				WHERE	id = id";
			}

			$find_person_query .= "
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

			if ($controls['ids'] == false && $controls['limit'] > 0 && preg_match('/^\d+$/',$controls['limit'])) {
				if (preg_match('/^\d+$/',$controls['offset']))
					$find_person_query .= "
					LIMIT	".$controls['offset'].",".$controls['limit'];
				else
					$find_person_query .= "
					LIMIT	".$controls['limit'];
			}
			app_log("Search query: ".$find_person_query,'trace',__FILE__,__LINE__);
			$rs = $GLOBALS['_database']->Execute($find_person_query,$bind_params);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}

			$people = array();
			while (list($id) = $rs->FetchRow()) {
				if ($controls['ids'] == false) {
					$customer = new Customer($id);
					array_push($people,$customer);
				}
				$this->incrementCount();
			}

			return $people;
		}
	}
