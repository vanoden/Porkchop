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
			'facebook'	=> "FaceBook Account"
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

			if (! isset($parameters['notify'])) $parameters['notify'] = 0;
			$add_contact_query = "
				INSERT
				INTO	
				    register_contacts
				(		
				        person_id,
				        description,
						type,
						value,
						notify,
						notes
				)
				VALUES
				(		
				    ?,?,?,?,?,?
				)
			";
			
			$GLOBALS['_database']->Execute(
				$add_contact_query,
				array(
					$parameters['person_id'],
					$parameters['description'],
					$parameters['type'],
					$parameters['value'],
					$parameters['notify'],
					$parameters['notes']
				)
			);
			
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Register::Contact::add(): ".$GLOBALS['_database']->ErrorMSg();
				return null;
			}
					
			return $GLOBALS['_database']->Insert_ID();
		}
		
		public function update($parameters = array(), $id = null) {
		
			if (! preg_match('/^[0-9]+$/',$this->id)) {
				$this->error = "ID Required for update method.";
				return 0;
			}
			
			$bind_params = array();
			$update_contact_query = " UPDATE register_contacts SET id = id";
				
			if ($parameters['type']) {
				if (! array_key_exists($parameters['type'],$this->types)) {
					$this->error = "Invalid contact type";
					return null;
				}
				$update_contact_query .= ",
						type = ?";
				array_push($bind_params,$parameters['type']);
			}
			if ($parameters['description']) {
				$update_contact_query .= ",
						description = ?";
				array_push($bind_params,$parameters['description']);
			if (isset($parameters['notify'])){
				$update_contact_query .= ",
						notify = ?";
				if ($parameters['notify']) array_push($bind_params,1);
				else array_push($bind_params,0);
			}
			if (isset($parameters['value'])) {
				$update_contact_query .= ",
						value = ?";
				array_push($bind_params,$parameters['value']);
			}
			if (isset($parameters['notes']))
				$update_contact_query .= ",
						notes = ?";
				array_push($bind_params,$parameters['notes']);
			}

			$update_contact_query .= "
				WHERE	id = ?";
			array_push($bind_params,$this->id);

			query_log($update_contact_query);
			$GLOBALS['_database']->Execute($update_contact_query,$bind_params);
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
		
		public function detailsByUserByTypeByDesc($person_id, $type='email', $desc='Work Email') {
            $get_object_query = "
				SELECT	id,
						type,
						value,
						notes,
						description,
						notify,
						person_id
				FROM	register_contacts
				WHERE 	person_id = ?
				AND		type = ?
				AND		description = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($person_id, $type, $desc)
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
				if ($contact->notify == 1) $this->notify = true;
				else $this->notify = false;
				$this->person = new \Register\Person($contact->person_id);
			}
			else {
				$this->id = null;
			}
			return $contact;
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
				if ($contact->notify == 1) $this->notify = true;
				else $this->notify = false;
				$this->person = new \Register\Person($contact->person_id);
			}
			else {
				$this->id = null;
			}
			return $contact;
		}
	}
