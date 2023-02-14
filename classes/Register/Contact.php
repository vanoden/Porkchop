<?php
	namespace Register;

	class Contact Extends \BaseModel {

		public $person;
		public $type;
		public $value;
		public $notes;
		public $description;
		public $notify;

		public $types = array(
			'phone'		=> "Phone Number",
			'email'		=> "Email Address",
			'sms'		=> "SMS-Text",
			'facebook'	=> "FaceBook Account",
			'insite'	=> "Website Message"			
		);

		public function __construct(int $id = 0) {
			$this->_database = new \Database\Service();		
			$this->_tableName = 'register_contacts';
			$this->_tableUKColumn = null;
    		parent::__construct($id);
		}

		public function __call($name, $parameters) {
			if ($name == "get") return $this->getContact($parameters[0],$parameters[1]);
			else {
				error_log("Method $name not found for ".get_class($this));
			}
		}

		public function getContact($type,$value): bool {
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
				$this->SQLError($GLOBALS['_database_']->ErrorMsg());
				return null;
			}
			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
		}
		
		public function add($parameters = array()) {
			if (! preg_match('/^\d+$/',$parameters['person_id'])) {
				$this->error("Valid person_id required for addContact method");
				return null;
			}
			if (! $this->validType($parameters['type'])) {
				$this->error("Valid type required for addContact method");
				return null;
			}

			if (! isset($parameters['notify'])) $parameters['notify'] = 0;
			$add_contact_query = "
				INSERT
				INTO	register_contacts
				(		person_id,
						type,
						value,
						notify
				)
				VALUES
				(		
				    ?,?,?,?
				)
			";
			
			$GLOBALS['_database']->Execute(
				$add_contact_query,
				array(
					$parameters['person_id'],
					$parameters['type'],
					$parameters['value'],
					$parameters['notify']
				)
			);
			
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMSg());
				return null;
			}
					
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}
		
		public function update($parameters = []): bool {
			if (! preg_match('/^[0-9]+$/',$this->id)) {
				$this->error("ID Required for update method.");
				return false;
			}
			
			$bind_params = array();
			$update_contact_query = " UPDATE register_contacts SET id = id";
				
			if ($parameters['type']) {
				if (! $this->validType($parameters['type'])) {
					$this->error("Invalid contact type");
					return false;
				}
				$update_contact_query .= ",
						type = ?";
				array_push($bind_params,$parameters['type']);
			}
			if (isset($parameters['description'])) {
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
				if ($this->validValue($parameters['type'],$parameters['value'])) {
					$update_contact_query .= ",
							value = ?";
					array_push($bind_params,$parameters['value']);
				}
				else {
					$this->error("Invalid contact value");
					return false;
				}
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			return $this->details();
		}
		
		public function detailsByUserByTypeByDesc($person_id, $type='email', $desc='Work Email') {
            $get_object_query = "
				SELECT	id
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			list($this->id) = $rs->FetchRow();
			return $this->details();
		}
		
		public function details(): bool {
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
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
				$this->type = null;
				$this->value = null;
				$this->notes = null;
				$this->description = null;
				$this->person = null;
			}
			return true;
		}

		public function validType($string): bool {
			if (array_key_exists($string,$this->types)) return true;
			else return false;
		}

		public function validValue($type,$string) {
			if ($type == 'phone') {
				preg_replace('/ext(ension)\.?\s?/i','ext. ',$string);
				if (preg_match('/^\+?\d[\d\.\-\s\#\(\)]+$/',$string)) return true;
			} elseif ($type == 'email') {
				if (valid_email($string)) return true;
			} elseif ($type == 'sms-text') {
				if (preg_match('/^[\d\-\.\(\)\#]+$/',$string)) return true;
			} else {
    			if (!empty($string)) return true;
			}
			return false;
		}
	}
