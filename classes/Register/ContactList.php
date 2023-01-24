<?php
	namespace Register;
	
	class ContactList Extends \BaseListClass {
		public function find($parameters = array()) {
			$this->clearError();
			$this->resetCount();

			$get_contacts_query = "
				SELECT	id
				FROM	register_contacts
				WHERE	id = id
			";
			$bind_params = array();
			
			if (isset($parameters['type'])) {
				if (preg_match('/^(email|sms|phone|facebook)$/',$parameters['type'])) {
					$get_contacts_query .= "
					AND	`type` = ?";
					array_push($bind_params,$parameters['type']);
				} else {
					$this->error("Invalid contact type");
					return null;
				}
			}
			
			if (isset($parameters['user_id'])) {
				if (preg_match('/^\d+$/',$parameters['user_id'])) {
					$get_contacts_query .= "
						AND	person_id = ?";
					array_push($bind_params,$parameters['user_id']);
				} else {
					$this->error("Invalid user id");
					return undef;
				}
			} elseif (isset($parameters['person_id'])) {
				if (preg_match('/^\d+$/',$parameters['person_id'])) {
					$get_contacts_query .= "
						AND	person_id = ?";
					array_push($bind_params,$parameters['person_id']);
				} else {
					$this->error("Invalid user id");
					return undef;
				}
			}
			
			if (isset($parameters['notify'])) {
				if ($parameters['notify'] == 1 || $parameters['notify'] == true) {
					$get_contacts_query .= "
						AND	notify = 1";
				} elseif ($parameters['notify'] == 0 || $parameters['notify'] == false) {
					$get_contacts_query .= "
						AND	notify = 0";
				} else {
					$this->error("Invalid value for notify");
					return undef;
				}
			}

			// search for requestList looks like only by value would be meaningful
            if (isset($parameters['searchTerm'])) $get_contacts_query .= " AND value LIKE '%" . $parameters['searchTerm'] . "%'";

			$get_contacts_query .= "
				ORDER BY notify DESC";

			$rs = $GLOBALS['_database']->Execute($get_contacts_query,$bind_params);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			$contacts = array();
			while (list($id) = $rs->FetchRow()) {
				$contact = new \Register\Contact($id);
				array_push($contacts,$contact);
				$this->incrementCount();
			}
			return $contacts;
		}
	}
