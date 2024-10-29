<?php
	namespace Register;
	class PersonList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Register\Person';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$find_person_query = "
				SELECT	id
				FROM	register_users
				WHERE	id = id";

			// Add Parameters
			$validationClass = new $this->_modelName;
			if (!empty($parameters['id']) && is_numeric($parameters['id'])) {
				$person = new \Register\Person($parameters['id']);
				if ($person->exists()) {
					$find_person_query .= "
					AND		id = ?";
					$database->AddParam($parameters['id']);
				}
				else {
					$this->error("Invalid user id");
					return [];
				}
			}
			if (!empty($parameters['code'])) {
				if ($validationClass->validCode($parameters['code'])) {
					$find_person_query .= "
					AND		login = ?";
					$database->AddParam($parameters['code']);
				}
				else {
					$this->error("Invalid code");
					return [];
				}
			}
			if (!empty($parameters['status'])) {
				if (is_array($parameters['status'])) {
					$count = 0;
					if ($validationClass->validStatus($parameters['status'])) {
						$find_person_query .= "
						AND		status IN (";
						$find_person_query .= implode(',', array_fill(0, count($parameters['status']), '?'));
						$find_person_query .= ")";
						foreach ($parameters['status'] as $status) {
							$database->AddParam($status);
						}
						$count ++;
					}
					else {
						$this->error("Invalid status");
						return [];
					}
				}
				else {
					if ($validationClass->validStatus($parameters['status'])) {
						$find_person_query .= "
						AND		status = ?";
						$database->AddParam($parameters['status']);
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
	
			if (!empty($parameters['first_name'])) {
				if ($validationClass->validFirstName($parameters['first_name'])) {
					$find_person_query .= "
					AND		first_name = ?";
					$database->AddParam($parameters['first_name']);
				}
				else {
					$this->error("Invalid first name");
					return [];
				}
			}
	
			if (!empty($parameters['last_name'])) {
				if ($validationClass->validLastName($parameters['last_name'])) {
					$find_person_query .= "
					AND		last_name = ?";
					$database->AddParam($parameters['last_name']);
				}
				else {
					$this->error("Invalid last name");
					return [];
				}
			}

			if (!empty($parameters['department_id']) && is_numeric($parameters['department_id'])) {
				$department = new \Register\Department($parameters['department_id']);
				if ($department->exists()) {
					$find_person_query .= "
					AND		department_id = ?";
					$database->AddParam($parameters['department_id']);
				}
				else {
					$this->error("Invalid department id");
					return [];
				}
			}

			if (!empty($parameters['organization_id']) && is_numeric($parameters['organization_id'])) {
				$organization = new \Register\Organization($parameters['organization_id']);
				if ($organization->exists()) {
					$find_person_query .= "
					AND		organization_id = ?";
					$database->AddParam($parameters['organization_id']);
				}
				else {
					$this->error("Invalid organization id");
					return [];
				}
			}

			// Order Clause
			if (preg_match('/^(login|first_name|last_name|organization_id)$/',$controls['sort'])) {
				$find_person_query .= " ORDER BY ".$controls['sort'];
			}
			else
				$find_person_query .= " ORDER BY login";

			// Limit Clause
			$find_person_query .= $this->limitClause($controls);

			// Execute Query
			$rs = $database->Execute($find_person_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			$people = array();
			while (list($id) = $rs->FetchRow()) {
				$person = new $this->_modelName($id);
				$this->incrementCount();
				array_push($people,$person);
			}
			return $people;
		}
	}
