<?php
	namespace Register;
	
	class ContactList {
		public $error;
		public $count;
		
		public function find($parameters = array()) {
			$get_contacts_query = "
				SELECT	id
				FROM	register_contacts
				WHERE	id = id
			";
			
			if (isset($parameters['type'])) {
				if (preg_match('/^(email|sms|phone|facebook)$/',$parameters['type'])) {
					$get_contacts_query .= "
					AND	`type` = ".$GLOBALS['_database']->qstr($parameters['type'],get_magic_quotes_gpc());
				}
				else {
					$this->error = "Invalid contact type";
					return undef;
				}
			}
			if (isset($parameters['user_id'])) {
				if (preg_match('/^\d+$/',$parameters['user_id'])) {
					$get_contacts_query .= "
						AND	person_id = ".$GLOBALS['_database']->qstr($parameters['user_id'],get_magic_quotes_gpc());
				}
				else {
					$this->error = "Invalid user id";
					return undef;
				}
			}
			elseif (isset($parameters['person_id'])) {
				if (preg_match('/^\d+$/',$parameters['person_id'])) {
					$get_contacts_query .= "
						AND	person_id = ".$GLOBALS['_database']->qstr($parameters['person_id'],get_magic_quotes_gpc());
				}
				else {
					$this->error = "Invalid user id";
					return undef;
				}
			}
			if (isset($parameters['notify'])) {
				if ($parameters['notify'] == 1 || $parameters['notify'] == true) {
					$get_contacts_query .= "
						AND	notify = 1";
				}
				elseif ($parameters['notify'] == 0 || $parameters['notify'] == false) {
					$get_contacts_query .= "
						AND	notify = 0";
				}
				else {
					$this->error = "Invalid value for notify";
					return undef;
				}
			}
			
			$rs = $GLOBALS['_database']->Execute(
				$get_contacts_query
			);
			if (! $rs) {
				$this->error = "SQL Error in Register::ContactList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$contacts = array();
			while (list($id) = $rs->FetchRow()) {
				$contact = new \Register\Contact($id);
				array_push($contacts,$contact);
			}
			return $contacts;
		}
	}