<?php
	namespace Register;
	class PersonList {

		public $count = 0;

		public function find($parameters = array()) {
			$bind_params = array();

			$find_person_query = "
				SELECT	id
				FROM	register_users
				WHERE	id = id";

			if (isset($parameters['id']) && preg_match('/^\d+$/',$parameters['id'])) {
				$find_person_query .= "
				AND		id = ?";
				array_push($bind_params,$parameters['id']);
			}
			elseif (isset($parameters['id'])) {
				$this->error = "Invalid id in Register::PersonList::find()";
				return null;
			}
			if (isset($parameters['code'])) {
				$find_person_query .= "
				AND		login = ?";
				array_push($bind_params,$parameters['code']);
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

			$rs = $GLOBALS['_database']->Execute($find_person_query,$bind_params);
			if (! $rs) {
				$this->error = "SQL Error in Register::PersonList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$people = array();
			while (list($id) = $rs->FetchRow()) {
				$person = new Person($id);
				$this->count ++;
				array_push($people,$person);
			}
			return $people;
		}
	}
