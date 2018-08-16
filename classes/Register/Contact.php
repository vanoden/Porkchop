<?php
	namespace Register;

	class Contact {
		public $error;
		public $id;
		public $person;
		public $types = array(
			'phone'		=> "Phone Number",
			'email'		=> "Email Address",
			'sms'		=> "SMS-Text",
			'facebook'	=> "FaceBook Account",
			'twitter'	=> "Twitter Account",
			'aim'		=> "AOL Instant Messenger"
		);

		public function __construct($id = 0) {
			if (is_numeric($id)) {
				$this->id = $id;
				$this->details();
			}
		}
		public function get($type,$value) {
			$get_object_query = "
				SELECT	id
				FROM	register_contacts
				WHERE	type = ?
				AND		value = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array(
					$type,
					$value
				)
			);
			if (! $rs) {
				$this->error = "SQL Error in Register::Contact::get: ".$GLOBALS['_database_']->ErrorMsg();
				return null;
			}
			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
		}
		public function add($parameters = array()) {
			if (! preg_match('/^\d+$/',$parameters['person_id'])) {
				$this->error = "Valid person_id required for addContact method";
				return null;
			}
			if (! array_key_exists($parameters['type'],$this->types)) {
				$this->error = "Valid type required for addContact method";
				return null;
			}

			$add_contact_query = "
				INSERT
				INTO	register_contacts
				(		person_id,
						type,
						value
				)
				VALUES
				(		?,?,?
				)
			";
			$GLOBALS['_database']->Execute(
				$add_contact_query,
				array(
					$parameters['person_id'],
					$parameters['type'],
					$parameters['value']
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in RegisterContact::add: ".$GLOBALS['_database']->ErrorMSg();
				return null;
			}
			return $this->update($GLOBALS['_database']->Insert_ID(),$parameters);
		}
		public function update($parameters = array()) {
			if (! preg_match('/^\d+$/',$this->id)) {
				$this->error = "ID Required for update method.";
				return 0;
			}
			$update_contact_query = "
				UPDATE	register_contacts
				SET		id = id";
				
			if ($parameters['type']) {
				if (! array_key_exists($parameters['type'],$this->types)) {
					$this->error = "Invalid contact type";
					return null;
				}
				$update_contact_query .= ",
						type = ".$GLOBALS['_database']->qstr($parameters['type'],get_magic_quotes_gpc());
			}
			if ($parameters['description'])
				$update_contact_query .= ",
						description = ".$GLOBALS['_database']->qstr($parameters['description'],get_magic_quotes_gpc());
			if (array_key_exists('notify',$parameters) and preg_match('/^(0|1)$/',$parameters['notify']))
				$update_contact_query .= ",
						notify = ".$parameters['notify'];
			if (isset($parameters['value']))
				$update_contact_query .= ",
						value = ".$GLOBALS['_database']->qstr($parameters['value'],get_magic_quotes_gpc());
			if (isset($parameters['notes']))
				$update_contact_query .= ",
						notes = ".$GLOBALS['_database']->qstr($parameters['notes'],get_magic_quotes_gpc());

			$update_contact_query .= "
				WHERE	id = ?";
			$GLOBALS['_database']->Execute(
				$update_contact_query,
				array($this->id)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in RegisterContact::update: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $this->details();
		}
		public function delete() {
			$delete_contact_query = "
				DELETE
				FROM	register_contacts
				WHERE	id = ?";

			$GLOBALS['_database']->Execute(
				$delete_contact_query,
				array($this->id)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in RegisterContact::update: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return 1;
		}
		public function details() {
			$get_object_query = "
				SELECT	id,
						type,
						value,
						notes,
						description,
						notify,
						person_id
				FROM	register_contacts
				WHERE 	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($this->id)
			);
			if (! $rs) {
				$this->error = "SQL Error in regiser::person::contactDetails: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$contact = $rs->FetchNextObject(false);
			if (isset($contact->id)) {
				$this->id = $contact->id;
				$this->type = $contact->type;
				$this->value = $contact->value;
				$this->notes = $contact->notes;
				$this->description = $contact->description;
				$this->notify = $contact->notify;
				$this->person = new \Register\Person($contact->person_id);
			}
			else {
				$this->id = null;
			}
			return $contact;
		}
	}
?>
