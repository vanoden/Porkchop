<?php
	namespace Register;
	
	class ContactList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Register\Contact';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$get_contacts_query = "
				SELECT	id
				FROM	register_contacts
				WHERE	id = id
			";

			// Add Parameters
			$validationClass = new $this->_modelName;
			if (!empty($parameters['type'])) {
				if ($validationClass->validType($parameters['type'])) {
					$get_contacts_query .= "
					AND		type = ?";
					$database->AddParam($parameters['type']);
				}
				else {
					$this->error("Invalid type");
					return [];
				}
			}			

			// check if we are looking for a specific person by user_id or person_id
			if (array_key_exists('person_id', $parameters) || array_key_exists('user_id', $parameters)) {
				if (isset($parameters['person_id'])) $person_id = $parameters['person_id'];
				else $person_id = $parameters['user_id'];

				if (is_numeric($person_id)) {
					$person = new \Register\Person($person_id);
					if ($person->exists()) {
						$get_contacts_query .= "
						AND	person_id = ?";
						$database->AddParam($person_id);
					}
					else {
						$this->error("Invalid user id");
						return [];
					}
				}
				else {
					$this->error("Invalid user id");
					return [];
				}
			}

			if (isset($parameters['notify'])) {
				if ((is_bool($parameters['notify']) && $parameters['notify'] == true) || $parameters['notify'] == 1) {
					$get_contacts_query .= "
						AND	notify = 1";
				}
				elseif ((is_bool($parameters['notify']) && $parameters['notify'] == false) || $parameters['notify'] == 0) {
					$get_contacts_query .= "
						AND	notify = 0";
				}
				else {
					$this->error("Invalid value for notify");
					return [];
				}
			}

			// search for requestList looks like only by value would be meaningful
			if (isset($parameters['searchTerm'])) {
				if ($this->validSearchString($parameters['searchTerm'])) $get_contacts_query .= " AND value LIKE '%" . $parameters['searchTerm'] . "%'";
				else {
					$this->error("Invalid search term");
					return [];
				}
			}

			// Order Clause
			$get_contacts_query .= "
				ORDER BY notify DESC";

			// Limit Clause
			$get_contacts_query .= $this->limitClause($parameters);

			// Execute Query
			$rs = $database->Execute($get_contacts_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}
			$contacts = array();
			while (list($id) = $rs->FetchRow()) {
				$contact = new $this->_modelName($id);
				array_push($contacts,$contact);
				$this->incrementCount();
			}
			return $contacts;
		}
	}
