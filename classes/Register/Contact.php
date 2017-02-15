<?php
	namespace Register;

	class Contact {
		public $error;
		public $types = array(
			'phone'		=> "Phone Number",
			'email'		=> "Email Address",
			'sms'		=> "SMS-Text",
			'facebook'	=> "FaceBook Account",
			'twitter'	=> "Twitter Account",
			'aim'		=> "AOL Instant Messenger"
		);

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
		public function update($id,$parameters = array()) {
			if (! preg_match('/^\d+$/',$id)) {
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
				array($id)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in RegisterContact::update: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $this->details($id);
		}
		public function delete($id) {
			$delete_contact_query = "
				DELETE
				FROM	register_contacts
				WHERE	id = ?";

			$GLOBALS['_database']->Execute(
				$delete_contact_query,
				array($id)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in RegisterContact::update: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return 1;
		}
		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	register_contacts
				WHERE	id = id";
			if (isset($parameters['person_id']))
				$find_objects_query .= "
				AND		person_id = ".$GLOBALS['_database']->qstr($parameters['person_id'],get_magic_quotes_gpc());
			if (isset($parameters['value']))
				$find_objects_query .= "
				AND		value = ".$GLOBALS['_database']->qstr($parameters['value'],get_magic_quotes_gpc());
			if (isset($parameters['type'])) {
				if (! array_key_exists($parameters['type'],$this->types)) {
					$this->error = "Invalid contact type";
					return null;
				}
				$find_objects_query .= "
				AND		type = ".$GLOBALS['_database']->qstr($parameters['type'],get_magic_quotes_gpc());
			}
			if (isset($parameters['notify']) and preg_match('/^(0|1)$/',$parameters['notify'])) {
				$find_objects_query .= "
				AND		 notify = ".$parameters['notify'];
			}

			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if (! $rs) {
				$this->error = "SQL Error in RegisterContact::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$object = $this->details($id);
				array_push($objects,$object);
			}
			return $objects;
		}
		public function details($id) {
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
				array($id)
			);
			if (! $rs) {
				$this->error = "SQL Error in regiser::person::contactDetails: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$contact = $rs->FetchNextObject(false);
			return $contact;
		}
	}
?>
