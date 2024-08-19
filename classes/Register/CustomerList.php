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
				app_log("Expiring ".$record->login."' [".$record->id."]",'notice');
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
    
		public function find($parameters = [], $controls = []) {

			$this->clearError();
			$this->resetCount();

			// Backward compatibility
			if (is_bool($controls)) $controls = array('count' => $controls);
			if (isset($controls['count'])) $controls['count'] = false;
			if ($controls['count']) $ADODB_COUNTRECS = true;
			if (isset($parameters['role'])) app_log("Don't use role as a filter for customers, use Register::Role::Members",'warning');

            $validationclass = new \Register\Customer();
    
			$bind_params = array();

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
				array_push($bind_params,$parameters['id']);
			}
			elseif (isset($parameters['id'])) {
				$this->error("Invalid id");
				return null;
			}
            if (!empty($parameters['code']) && empty($parameters['login'])) $parameters['login'] = $parameters['code'];

			if (!empty($parameters['login'])) {
                if ($validationclass->validCode($parameters['login'])) {
    				$find_person_query .= "
	    			AND		login = ?";
		    		array_push($bind_params,$parameters['login']);
                }
                else {
                    $this->error("Invalid login");
                    return null;
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
                            return null;
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
                        return null;
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
                    array_push($bind_params,$parameters['first_name']);
                }
                else {
                    $this->error("Invalid first name");
                    return null;
                }
			}
	
			if (isset($parameters['last_name'])) {
                if ($validationclass->validName($parameters['last_name'])) {
                    $find_person_query .= "
                    AND		last_name = ?";
                    array_push($bind_params,$parameters['last_name']);
                }
                else {
                    $this->error("Invalid last name");
                    return null;
                }
			}
	
			if (isset($parameters['email_address'])) {
                if ($validationclass->validEmail($parameters['email_address'])) {
                    $find_person_query .= "
                    AND		email_address = ?";
                    array_push($bind_params,$parameters['email_address']);
                }
                else {
                    $this->error("Invalid email address");
                    return null;
                }
			}

			if (!empty($parameters['department_id']) && is_numeric($parameters['department_id'])) {

				$find_person_query .= "
				AND		department_id = ?";
				array_push($bind_params,$parameters['department_id']);
			}

			if (!empty($parameters['organization_id']) && is_numeric($parameters['organization_id'])) {

                $organization = new \Register\Organization($parameters['organization_id']);
                if (!$organization->exists()) {
                    $this->error("Invalid organization");
                    return null;
                }
				$find_person_query .= "
				AND		organization_id = ?";
				array_push($bind_params,$organization->id);
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
					array_push($bind_params,$parameters['automation']);
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
						return null;
					}
				}
				else {
					$this->error("Invalid automation");
					return null;
				}
			}

			// Add search_tags searching
			if (isset($parameters['search_tags']) && !empty($parameters['search_tags'])) {
				
				// Join to the existing query
				$find_person_query .= "
					LEFT JOIN (
						SELECT DISTINCT(stx.object_id)
						FROM search_tags_xref stx
						INNER JOIN search_tags st ON stx.tag_id = st.id
						WHERE st.class = 'Register::Customer'
						AND (
							st.category LIKE ?
							OR st.value LIKE ?
						)
					) AS search_tags_results ON p.id = search_tags_results.object_id
				";
				array_push($bind_params, '%'.$parameters['search'].'%','%'.$parameters['search'].'%');
			}  			

            if (!empty($parameters['_sort'])) $controls['sort'] = $parameters['_sort'];
            if (!empty($parameters['_limit']) && is_numeric($parameters['_limit'])) $controls['limit'] = $parameters['_limit'];
            if (!empty($parameters['_offset']) && is_numeric($parameters['_offset'])) $controls['offset'] = $parameters['_offset'];

			if (isset($controls['sort']) && $controls['_sort'] == 'full_name') {
				$find_person_query .= " ORDER BY first_name,last_name";
			}
            elseif (isset($controls['sort']) && $validationclass->hasField($controls['sort'])) {
				$find_person_query .= " ORDER BY ".$controls['_sort'];
			}
			else
				$find_person_query .= " ORDER BY login";

			if (!empty($controls['limit']) && is_numeric($controls['limit'])) {
				if (is_numeric($controls['offset']))
					$find_person_query .= "
					LIMIT ".$controls['offset'].",".$controls['limit'];
				if (! is_numeric($controls['offset']))
					$find_person_query .= "
					OFFSET ".$controls['limit'];
			}

			$rs = $GLOBALS['_database']->Execute($find_person_query,$bind_params);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}

			$people = array();
			while (list($id) = $rs->FetchRow()) {
				if (isset($parameters['role']) || ! $controls['count']) {
					$customer = new Customer($id);
				}
				if (isset($parameters['role']) && ! $customer->has_role($parameters['role'])) continue;
				if (! $controls['count']) array_push($people,$customer);
				$this->incrementCount();
			}

			return $people;
		}
		
		public function search($search_string,$limit = 0,$offset = 0) {
			$this->clearError();
			$this->resetCount();

			if (is_bool($limit) && $limit == true) $count = true;
			else $count = false;

			$bind_params = array();

			app_log("Customer Search Requested",'debug',__FILE__,__LINE__);

			if (! preg_match('/^[\w\-\.\_\s\*]{3,64}$/',$search_string)) {
				$this->error("Invalid search string");
				return null;
			}
			if (preg_match('/\*/',$search_string))
				$search_string = preg_replace('/\*/','%',$search_string);
			else
				$search_string = '%'.$search_string.'%';

			if (empty($search_string)) {
				$this->error("Search string required");
				return null;
			}

			// Search for customers based on basic information
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}

			$people = array();
			while (list($id) = $rs->FetchRow()) {
				if ($count == false) {
					$customer = new Customer($id);
					array_push($people,$customer);
				}
				$this->incrementCount();
			}
			return $people;
		}
	}
