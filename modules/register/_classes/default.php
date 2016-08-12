<?php
    class RegisterPerson {
        public $id;
		public $first_name;
		public $last_name;
		public $address;
		public $email_address;
		public $phone_number;
		public $organization;
		public $error;
		public $code;
		public $message;
		public $department_id;
		public $department;

		public function __construct($id = 0) {
			# Clear Error Info
			$this->error = '';

			# Database Initialization
			$schema = new RegisterSchema();
			if ($schema->error) {
				$this->error = "Failed to initialize schema: ".$schema->error;
			}
			else {
				# Get Configuration Information
				$this->config = $GLOBALS['_config'];
			
				# Find Person if id given
				if ($person_id) {
					$this->details($id);
				}
			}
    	}

		public function exists($login) {
			list($person) = $this->find(array("login" => $login));

			if ($person->id)
				return true;
			else
				return false;
		}
	
		public function details($id) {
			#app_log("Getting details for person '$person_id'",'debug',__FILE__,__LINE__);
			if (!$id) $id = $this->id;

			$cache_key = "customer[".$id."]";

			# Cached Customer Object, Yay!	
			if (($id) and ($customer = cache_get($cache_key))) {
				$this->id = $customer->id;
				$this->first_name = $customer->first_name;
				$this->last_name = $customer->last_name;
				$this->code = $customer->code;
				$this->login = $customer->code;
				$this->organization_id = $customer->organization_id;
				$this->department_id = $customer->department_id;
				$this->department = $customer->department;
				$_organization = new RegisterOrganization();
				$this->organization = $_organization->details($customer->organization_id);
				$this->status = $customer->status;
				$this->timezone = $customer->timezone;
				$this->auth_method = $customer->auth_method;
				$customer->_cached = 1;

				# In Case Cache Corrupted
				if ($customer->id) {
					app_log("Customer ".$this->login." [$id] found in cache",'debug',__FILE__,__LINE__);
					return $customer;
				}
				else {
					$this->error = "Customer $person_id returned unpopulated cache";
				}
			}

			#app_log("Querying details for person '$id'",'debug',__FILE__,__LINE__);
			# Get Persons Info From Database
			$get_person_query = "
				SELECT	id,
						login code,
						first_name,
						last_name,
						date_created,
						organization_id,
						department_id,
						auth_method,
						status,
						timezone
				FROM	register_users
				WHERE   id = ?
			";

			$rs = $GLOBALS['_database']->Execute(
				$get_person_query,
				array($id)
			);
			if (! $rs) {
				$this->error = "SQL Error in RegisterPerson::details: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$customer = $rs->FetchNextObject(false);

			# Fetch Organization Info
			#app_log("Getting organization for person '$id'",'debug',__FILE__,__LINE__);
			$_organization = new RegisterOrganization($customer->organization_id);
			if ($_organization->error) {
				$this->error = "Error initializing organization: ".$_organization->error;
				return null;
			}
			$customer->organization = $_organization->details();

			# Fetch Contact Info
			#app_log("Getting contact details for person '$person_id'",'debug',__FILE__,__LINE__);
			$_contact = new RegisterContact();
			if ($_contact->error) {
				$this->error = "Error initializing contact: ".$_contact->error;
				return null;
			}
			$contact_list = $_contact->find(array("person_id" => $customer->id));
			if ($_contact->error)
			{
				$this->error = "Error finding contacts: ".$_contact->error;
				return null;
			}
			foreach ($contact_list as $contact)
			{
				if ($contact->type == 'home phone')
					$customer->phone_number->home = $contact->value;
				elseif ($contact->type == 'work phone')
					$customer->phone_number->work = $contact->value;
				elseif ($contact->type == 'home email')
					$customer->email_address->home = $contact->value;
				elseif ($contact->type == 'work_email')
					$customer->email_address->work = $contact->value;
				elseif ($contact->type == 'home mobile')
					$customer->mobile_number->home = $contact->value;
				elseif ($contact->type == 'work_mobile')
					$customer->mobile_number->work = $contact->value;
			}
			
			if (preg_match('/^[\dabcdef]{32}$/',$customer->code))
				$customer->status = 'HIDDEN';
			elseif (preg_match('/^auto_\d{11,13}$/',$customer->code))
				$customer->status = 'HIDDEN';

			#app_log("Caching details for person '$id'",'debug',__FILE__,__LINE__);
			# Store Some Object Vars
			$this->id = $customer->id;
			$this->first_name = $customer->first_name;
			$this->last_name = $customer->last_name;
			$this->code = $customer->code;
			$this->login = $customer->code;
			$customer->login = $customer->code;
			$this->organization = $customer->organization;
			$this->department_id = $customer->department_id;
			if (isset($customer->department)) $this->department = $customer->department;
			$this->status = $customer->status;
			$this->timezone = $customer->timezone;
			$this->auth_method = $customer->auth_method;

			# Cache Customer Object
			if ($customer->id) cache_set($cache_key,$customer);

			# Return Object
			return $this;
		}

		public function find($parameters = array()) {
			$find_person_query = "
				SELECT	id
				FROM	register_users
				WHERE	id = id";
	
			if (isset($parameters['id']) && preg_match('/^\d+$/',$parameters['id'])) {
				$find_person_query .= "
				AND		id = ".$parameters['id'];
			}
			elseif (isset($parameters['id'])) {
				$this->error = "Invalid id in Person::find";
				return null;
			}
			if (isset($parameters['code'])) {
				$find_person_query .= "
				AND		login = ".$GLOBALS['_database']->qstr($parameters['code'],get_magic_quotes_gpc());
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
						AND		status = ".$GLOBALS['_database']->qstr($parameters['status'],get_magic_quotes_gpc());
				}
			}
			else {
				$find_person_query .= "
				AND		status not in ('EXPIRED','HIDDEN','DELETED')";
			}
	
			if (isset($parameters['first_name'])) {
				$find_person_query .= "
				AND		first_name = ".$GLOBALS['_database']->qstr($parameters['first_name'],get_magic_quotes_gpc());
			}
	
			if (isset($parameters['last_name'])) {
				$find_person_query .= "
				AND		last_name = ".$GLOBALS['_database']->qstr($parameters['last_name'],get_magic_quotes_gpc());
			}
	
			if (isset($parameters['email_address'])) {
				$find_person_query .= "
				AND		email_address = ".$GLOBALS['_database']->qstr($parameters['email_address'],get_magic_quotes_gpc());
			}

			if (isset($parameters['department_id'])) {
				$find_person_query .= "
				AND		department_id = ".$GLOBALS['_database']->qstr($parameters['department_id'],get_magic_quotes_gpc());
			}
			if (isset($parameters['organization_id'])) {
				$find_person_query .= "
				AND		organization_id = ".$GLOBALS['_database']->qstr($parameters['organization_id'],get_magic_quotes_gpc());
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

			$rs = $GLOBALS['_database']->Execute($find_person_query);
			if (! $rs) {
				$this->error = "SQL Error in RegisterPerson::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$people = array();
			while (list($id) = $rs->FetchRow()) {
				$person = new RegisterPerson();
				$person->details($id);
				array_push($people,$person);
			}
			return $people;
		}
		public function count($parameters = array()) {
			$find_person_query = "
				SELECT	count(*)
				FROM	register_users
				WHERE	id = id";
	
			if (preg_match('/^\d+$/',$parameters['id']))
			{
				$find_person_query .= "
				AND		id = ".$parameters['id'];
			}
			elseif ($parameters['id'])
			{
				$this->error = "Invalid id in Person::find";
				return 0;
			}
			if ($parameters['code'])
			{
				$find_person_query .= "
				AND		login = ".$GLOBALS['_database']->qstr($parameters['code'],get_magic_quotes_gpc());
			}
	
			if ($parameters['first_name'])
			{
				$find_person_query .= "
				AND		first_name = ".$GLOBALS['_database']->qstr($parameters['first_name'],get_magic_quotes_gpc());
			}
	
			if ($parameters['last_name'])
			{
				$find_person_query .= "
				AND		last_name = ".$GLOBALS['_database']->qstr($parameters['last_name'],get_magic_quotes_gpc());
			}
	
			if ($parameters['email_address'])
			{
				$find_person_query .= "
				AND		email_address = ".$GLOBALS['_database']->qstr($parameters['email_address'],get_magic_quotes_gpc());
			}

			if ($parameters['department_id'])
			{
				$find_person_query .= "
				AND		department_id = ".$GLOBALS['_database']->qstr($parameters['department_id'],get_magic_quotes_gpc());
			}
			if ($parameters['organization_id'])
			{
				$find_person_query .= "
				AND		organization_id = ".$GLOBALS['_database']->qstr($parameters['organization_id'],get_magic_quotes_gpc());
			}
			if (isset($parameters['status']))
			{
				if (is_array($parameters['status']))
				{
					$count = 0;
					$find_person_query .= "
					AND	status IN (";
					foreach ($parameters['status'] as $status)
					{
						if ($count > 0) $find_person_query .= ","; 
						$count ++;
						if (preg_match('/^[\w\-\_\.]+$/',$status))
						$find_person_query .= $status;
					}
				}
				else {
					$find_person_query .= "
						AND		status = ".$GLOBALS['_database']->qstr($parameters['status'],get_magic_quotes_gpc());
				}
			}
			else
			{
				$find_person_query .= "
				AND		status not in ('EXPIRED','HIDDEN','DELETED')";
			}
			
			$rs = $GLOBALS['_database']->Execute($find_person_query);
			if (! $rs)
			{
				$this->error = "SQL Error in RegisterPerson::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			list($count) = $rs->FetchRow();
			return $count;
		}
		public function password_strength($string) {
			$password_strength = strlen($string);
			if (preg_match('/[A-Z]/',$string)) $password_strength += 1;
			if (preg_match('/[\@\$\_\-\.\!\&]/',$string)) $password_strength += 1;
			if (preg_match('/\d/',$string)) $password_strength += 1;
			if (preg_match('/[a-z]/',$string)) $password_strength += 1;
			return $password_strength;
		}
		public function add($parameters) {
		    if (! preg_match("/^[\w\-\_@\.\+\s]{2,100}\$/", $parameters['login']))
		    {
				$this->error = "Invalid Login";
				return null;
			}
			if (! $GLOBALS['_config']->no_password)
			{
				$password_length = strlen($parameters['password']);

				if ($this->password_strength($parameters['password']) < $_GLOBALS['_config']->register->minimum_password_strength)
				{
					$this->error = "Password too weak.";
					return null;
				}
				if ($password_length > 100)
				{
				    $this->error = "Password too long.";
				    return null;
				}
		    }

			# Defaults
			if (! isset($parameters['timezone'])) $parameters['timezone'] = 'America/New_York';
			if (! isset($parameters['status'])) $parameters['status'] = 'NEW';
			if (! isset($parameters['date_expires'])) $parameters['date_expires'] = '2038-01-01 00:00:00';

			# Add to Database
			$add_user_query = "
				INSERT
				INTO	register_users
				(
					date_created,
					date_updated,
					date_expires,
					status,
					login,
					password,
					first_name,
					last_name,
					organization_id,
					timezone
				)
				VALUES
				(
					sysdate(),
					sysdate(),
					?,
					?,
					?,
					password(?),
					?,
					?,
					?,
					?
				)
			";

			$GLOBALS['_database']->Execute(
				$add_user_query,
				array(
					$parameters['date_expires'],
					$parameters['status'],
					$parameters['login'],
					$parameters['password'],
					$parameters['first_name'],
					$parameters['last_name'],
					$parameters['organization_id'],
					$parameters['timezone']
				)
			);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in Person::add: Error: ".$GLOBALS['_database']->ErrorMsg()." Query: ".preg_replace("/[\t\r\n]/"," ",$add_user_query);
				return null;
			}
			$id = $GLOBALS['_database']->Insert_ID();
			app_log("Added customer ".$parameters['login']." [$id]",'debug',__FILE__,__LINE__);
			return $this->details($id);
		}

		public function update($id,$parameters = array()) {
			if (! $id) $id = $this->id;

			if (! $id)
			{
				$this->error = "User ID Required for Update";
				return null;
			}

			if ((! role("register manager")) and ($GLOBALS['_SESSION_']->customer->id != $id))
			{
				$this->error = "Only Customer Managers can update accounts besides their own.";
				return null;
			}
			# Valid Parameters
			$valid_params = array(
				"first_name"    	=> 'first_name',
				"last_name"     	=> 'last_name',
				"login"         	=> 'login',
				"password"      	=> 'password',
				"organization_id"	=> 'organization_id',
				"status"			=> 'status',
				"timezone"			=> 'timezone'
			);

			# Loop through and apply changes
			$update_customer_query = "
				UPDATE	register_users
				SET		id = id
			";

			foreach (array_keys($valid_params) as $param)
			{
				if (array_key_exists($param,$parameters))
				{
					if ($param == "password")
					{
						app_log("Changing password",'notice',__FILE__,__LINE__);
						$update_customer_query .= ",
							`password` = password(".$GLOBALS['_database']->qstr($parameters[$param],get_magic_quotes_gpc()).")";
					}
					else
					{
						app_log("Updating $param to ".$parameters[$param],'notice',__FILE__,__LINE__);
						$update_customer_query .= ",
							".$valid_params[$param]." = ".$GLOBALS['_database']->qstr($parameters[$param],get_magic_quotes_gpc());
					}
				}
				else {
					if (isset($parameters[$param])) $this->setMeta($id,$param,$parameters[$param]);
				}
			}

			$update_customer_query .= "
				WHERE	id = ?
			";

			$GLOBALS['_database']->Execute(
				$update_customer_query,
				array($id)
			);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in RegisterPerson::update: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			# Bust Cache
			$cache_key = "customer[".$id."]";
			cache_unset($cache_key);

			# Get Updated Information
			return $this->details($id);
		}
		public function getMeta($id = 0) {
			if (! $id) $id = $this->id;
			$get_meta_query = "
				SELECT	`key`,value
				FROM	register_person_metadata
				WHERE	person_id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_meta_query,
				array($id)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in Product::getMeta: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$metadata = array();
			while (list($label,$value) = $rs->FetchRow())
			{
				$metadata[$label] = $value;
			}
			return $metadata;
		}
		public function setMeta($arg1,$arg2,$arg3 = 0) {
			if (func_num_args() == 3)
			{
				$id = $arg1;
				$key = $arg2;
				$value = $arg3;
			}
			else
			{
				$id = $this->id;
				$key = $arg1;
				$value = $arg2;
			}
			if (! $id)
			{
				$this->error = "No person_id for metadata";
				return null;
			}
			$add_meta_query = "
				REPLACE
				INTO	register_person_metadata
				(		person_id,`key`,value)
				VALUES
				(		?,?,?)
			";
			$GLOBALS['_database']->Execute(
				$add_meta_query,
				array(
					$id,$key,$value
				)
			);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in RegisterPerson::setMeta: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return 1;
		}
		public function searchMeta($key,$value = '') {
			$get_results_query = "
				SELECT	person_id
				FROM	register_person_metadata
				WHERE	`key` = ".$GLOBALS['_database']->qstr($key,get_magic_quotes_gpc);
			
			if ($value)
				$get_results_query .= "
				AND		value = ".$GLOBALS['_database']->qstr($value,get_magic_quotes_gpc);
			
			$rs = $GLOBALS['_database']->Execute($get_results_query);
			if (! $rs)
			{
				$this->error = "SQL Error in RegisterPerson::searchMeta: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$objects = array();
			while(list($id) = $rs->FetchRow())
			{
				$object = $this->details($id);
				if ($object->status == 'DELETED') continue;
				array_push($objects,$object);
			}
			return $objects;
		}
		# Process Email Verification Request
		function verify_email($login,$validation_key) {
			if (! $login) return 0;
			if (! $validation_key) return 0;
	
			$check_key_query = "
				SELECT	id,validation_key
				FROM	register_users
				WHERE	login = ".$GLOBALS['_database']->qstr($login,get_magic_quotes_gpc())."
				AND		company_id = '".$GLOBALS['_SESSION_']->company."'
			";
			$rs = $GLOBALS['_database']->Execute($check_key_query);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = $GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			list($id,$unverified_key) = $rs->fields;
			if (! $id)
			{
				$this->error = "Invalid Login or Validation Key";
				return 0;
			}
			if (! $unverified_key)
			{
				$this->error = "Email Address already verified for this account";
				return 0;
			}
			if ($unverified_key != $validation_key)
			{
				$this->error = "Invalid Login or Validation Key";
				return 0;
			}
			$validate_email_query = "
				UPDATE	register_users
				SET		validation_key = null
				WHERE	login = ".$GLOBALS['_database']->qstr($login,get_magic_quotes_gpc())."
				AND		company_id = '".$GLOBALS['_SESSION_']->company."'
			";
			$rs = $GLOBALS['_database']->Execute($validate_email_query);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = $GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			$this->id = $id;
			return 1;
		}
		public function addContact($parameters = array()) {
			$_contact = new RegisterContact();
			$contact = $_contact->add($parameters);
			if ($_contact->error)
			{
				$this->error = "Error adding contact: ".$_contact->error;
				return null;
			}
			return $contact;
		}
		public function updateContact($id,$parameters = array()) {
			$contact = new RegisterContact();
			return $contact->update($id,$parameters);
		}
		public function deleteContact($id) {
			$contact = new RegisterContact();
			return $contact->delete($id);
		}
		public function findContacts($parameters = array()) {
			$contact = new RegisterContact();
			return $contact->find($parameters);
		}
		public function contactDetails($id) {
			$contact = new RegisterContact();
			return $contact->details($id);
		}
		public function notifyContact($parameters = array()) {
			$contact = new RegisterContact();
			return $contact->notify($parameters);
		}
		public function notify($id,$message) {
			require_module('email');

			# Get Contact Info
			$_contact = new RegisterContact();
			$contacts = $_contact->find(array("person_id" => $id,"type" => "email","notify" => 1));
			foreach ($contacts as $contact)
			{
				app_log("Sending notification to ".$contact->value,'notice',__FILE__,__LINE__);
				$_email = new EmailMessage();
				$message['to'] = $contact->value;
				$_email->send($message);
				if ($_email->error)
				{
					$this->error = "Error sending notification: ".$_email->error;
					return null;
				}
			}
		}
		public function delete($id = 0) {
			if (! $id) $id = $this->id;
			app_log("Changing person $id to status DELETED",'debug',__FILE__,__LINE__);
			$this->update($id,array('status' => "DELETED"));
		}
		public function parents($id = 0) {
			if (! $id) $id = $this->id;
			$_relationship = new RegisterRelationship();
			return $_relationship->parents($id);
		}
		public function children($id = 0) {
			if (! $id) $id = $this->id;
			$_relationship = new RegisterRelationship();
			return $_relationship->children($id);
		}
    }
	class RegisterContact {
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
    class RegisterCustomer extends RegisterPerson {
		public $roles;

		public function __construct($person_id = 0) {
		    if ($person_id) {
				$this->details($person_id);
				$this->roles = $this->roles();
		    }
		}
		public function get($code = '') {
			$this->error = null;
			$get_object_query = "
				SELECT	id
				FROM	register_users
				WHERE	login = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($code)
			);
			if (! $rs) {
				$this->error = "SQL Error in RegisterPerson::get: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($id) = $rs->FetchRow();
			$this->id = $id;
			$object = $this->details($id);
			return $object;
		}
		public function details($person_id) {
		    if (! $person_id) $person_id = $this->id;
		    $details = parent::details($person_id);
			$this->id = $details->id;
		    if (isset($parent_id)) $details->roles = $this->roles($parent_id);
			else $details->roles = array();
		    return $details;
		}

		public function update($id,$parameters = array()) {
			parent::update($id,$parameters);

			# Roles
			$_role = new RegisterRole();
			if (in_array('register manager',$GLOBALS['_SESSION_']->customer->roles)) {
				$all_roles = $_role->find();
				foreach ($all_roles as $role) {
					if (array_key_exists('roles',$parameters) and is_array($parameters['roles'])) {
						if (array_key_exists($role['id'],$parameters['roles'])) {
							# Add Role
							$this->add_role($id,$role['id']);
						}
						else {
							# Drop Role
							$this->drop_role($id,$role['id']);
						}
					}
				}
			}
			return $this->details($id);
		}

		function add_role ($customer_id,$role_id = 0) {
			# Our own polymorphism
			if (! $role_id) {
				$role_id = $customer_id;
				$customer_id = $this->id;
			}
			if (! in_array("register manager",$GLOBALS['_SESSION_']->customer->roles)) {
				app_log("Non admin failed to update roles",'notice',__FILE__,__LINE__);
				$this->error = "Only Register Managers can update roles.";
				return 0;
			}
			app_log("Granting role '$role_id' to customer '$customer_id'",'info',__FILE__,__LINE__);
			$add_role_query = "
				INSERT
				INTO	register_users_roles
				(		user_id,role_id)
				VALUES
				(		".$GLOBALS['_database']->qstr($customer_id,get_magic_quotes_gpc()).",
						".$GLOBALS['_database']->qstr($role_id,get_magic_quotes_gpc())."
				)
				ON DUPLICATE KEY UPDATE user_id = user_id
			";

			$GLOBALS['_database']->Execute($add_role_query);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = $GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			
			# Bust Cache
			cache_unset("customer[".$customer_id."]");

			return 1;
		}

		function drop_role($customer_id,$role_id = 0) {
			# Our own polymorphism
			if (! $role_id) {
				$role_id = $customer_id;
				$customer_id = $this->id;
			}
			if (! in_array("register manager",$GLOBALS['_SESSION_']->customer->roles)) {
				$this->error = "Only Register Managers can update roles.";
				return 0;
			}
			$drop_role_query = "
				DELETE
				FROM	register_users_roles
				WHERE	user_id = ".$GLOBALS['_database']->qstr($customer_id,get_magic_quotes_gpc())."
				AND		role_id = ".$GLOBALS['_database']->qstr($role_id,get_magic_quotes_gpc());
			//error_log("Update Customer: $drop_role_query");
			$GLOBALS['_database']->Execute($drop_role_query);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = $GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			
			# Bust Cache
			cache_unset("customer[".$customer_id."]");

			return 1;
		}

		// Check login and password against configured authentication mechanism
		function authenticate ($login,$password) {
			if (! $login) return 0;

			# Get Authentication Method
			$get_user_query = "
				SELECT	id,auth_method
				FROM	register_users
				WHERE	login = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_user_query,
				array($login)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL error in register::customer::authenticate: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			list($id,$auth_method) = $rs->fields;
			if (! $id) {
				app_log("No account for '$login'",'notice',__FILE__,__LINE__);
				return 0;
			}

			if (preg_match('/^ldap\/(\w+)$/',$auth_method,$matches))
				$result = $this->LDAPauthenticate($matches[1],$login,$password);
			else
				$result = $this->LOCALauthenticate($login,$password);

			// Logging
			if ($result) app_log("'$login' authenticated successfully",'notice',__FILE__,__LINE__);
			else app_log("'$login' failed to authenticate",'notice',__FILE__,__LINE__);

			return $result;
		}

		# Authenticate using database for credentials
		function LOCALauthenticate ($login,$password) {
			if (! $login) {
				return 0;
			}

			# Check User Query
			$get_user_query = "
				SELECT	id
				FROM	register_users
				WHERE	login = ?
				AND		password = password(?)
			";

			$rs = $GLOBALS['_database']->Execute(
				$get_user_query,
				array(
					$login,
					$password
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = $GLOBALS['_database']->ErrorMsg();
				return null;
			}

			list($id) = $rs->FetchRow();

			if (! $id) {
				# Login Failed
				return 0;
			}
			$this->details($id);

			if ($this->id) return 1;
			else return 0;
		}

		# Authenticate using external LDAP service
		public function LDAPauthenticate($domain,$login,$password) {
			# Check User Query
			$get_user_query = "
				SELECT	id
				FROM	register_users
				WHERE	login = ".$GLOBALS['_database']->qstr($login,get_magic_quotes_gpc())."
			";

			$rs = $GLOBALS['_database']->Execute($get_user_query);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = $GLOBALS['_database']->ErrorMsg();
				return 0;
			}

			list($id) = $rs->fields;
			if (! $id) {
				error_log("No account for $login");
				$this->message = "Account not found";
			}

			$LDAPServerAddress1	= $GLOBALS['_config']->authentication->$domain->server1;
			$LDAPServerAddress2	= $GLOBALS['_config']->authentication->$domain->server2;
			$LDAPServerPort		= "389";
			$LDAPServerTimeOut	= "60";
			$LDAPContainer		= $GLOBALS['_config']->authentication->$domain->container;
			$BIND_username		= strtoupper($domain)."\\$login";
			$BIND_password		= $password;

			if(($ds=ldap_connect($LDAPServerAddress1)) || ($ds=ldap_connect($LDAPServerAddress2))) {
				ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
				ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);

				if($r=ldap_bind($ds,$BIND_username,$BIND_password)) {
					error_log("LDAP Authentication for $login successful");
					$this->details($id);
					return 1;
				}
				else {
					$this->message = "Auth Failed: ".ldap_error($ds);
					$GLOBALS['_page']->error = "Auth Failed: ".ldap_error($ds);
					if (ldap_get_option($ds, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error)) {
						error_log("Error Binding to LDAP: $extended_error");
					}
					else {
						error_log("LDAP Authentication for $login failed");
					}
					return 0;
				}
			}
		}

		# Personal Inventory (Online Products)
		public function products($product='') {
			###############################################
			### Get List of Purchased Products			###
			###############################################
			# Prepare Query
			$get_products_query = "
				SELECT	p.name,
						p.description,
						date_format(cp.expire_date,'%c/%e/%Y') expire_date,
						p.sku,
						p.sku code,
						p.data,
						cp.quantity,
						unix_timestamp(sysdate()) - unix_timestamp(cp.expire_date) expired,
						pt.group_flag,
						p.test_flag
				FROM	online_product.customer_products cp,
						product.products p,
						product.product_types pt
				WHERE	cp.customer_id = '".$this->id."'
				AND		p.product_id = cp.product_id
				AND		p.type_id = pt.type_id
				AND		cp.parent_id = 0
				AND		(cp.expire_date > sysdate() 
				OR		cp.quantity > 0
				OR		pt.group_flag = 1)
				AND		cp.void_flag = 0
			";
	
			# Conditional
			if ($product) $get_products_query .= "AND p.sku = '".mysql_escape_string($product)."'\n";
	
			# Execute Query
			$rs = $GLOBALS['_database']->Execute($get_products_query);
			if ($rs->ErrorMsg()) {
				$this->error = $GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			$products = array();
			while ($product = $rs->FetchRow()) {
				$_product = new Product($product['id']);
				array_push($products,$_product->details($product["code"]));
			}
			return $hubs;
		}

		# See If a User has been granted a Role
		public function has_role($role_name) {
			# Check Role Query
			$check_role_query = "
				SELECT	r.id
				FROM	register_roles r
				INNER JOIN 	register_users_roles rur
				ON		r.id = rur.role_id
				WHERE	rur.user_id = ".$GLOBALS['_database']->qstr($this->id, get_magic_quotes_gpc())."
				AND		r.name = ".$GLOBALS['_database']->qstr($role_name, get_magic_quotes_gpc())."
				;
			";
			
			$rs = $GLOBALS['_database']->Execute($check_role_query);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in RegisterCustomer::has_role: ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			
			list($has_it) = $rs->fields;
			
			if ($has_it) {
				return $has_it;
			}
			else {
				return false;
			}
		}
		# Get all users that have been granted a Role
		public function have_role($id) {
			# Check Role Query
			$check_role_query = "
				SELECT	user_id
				FROM	register_roles
				WHERE	role_id = ?
				;
			";

			$rs = $GLOBALS['_database']->Execute(
				$check_role_query,
				array($id)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = $GLOBALS['_database']->ErrorMsg();
				error_log($this->error);
				return false;
			}
			
			$customers = array();
			while(list($user_id) = $rs->FetchRow()) {
				$details = $this->details($user_id);
				array_push($customers,$details);
			}
			return $customers;
		}
		# Notify Members in a Role
		public function notify_role_members($role_id,$message) {
			$members = $this->have_role($role_id);
			foreach ($members as $member)
			{
				$this->notify($member->id,$message);
			}
		}

		# Get List of User Roles
		public function roles($person_id = 0,$return_id = false) {
			if (! $person_id) $person_id = $this->id;
	
			# Get Roles Query
			$get_roles_query = "
				SELECT	r.id,r.name
				FROM	register_roles r
						INNER JOIN 	register_users_roles rur
						ON r.id = rur.role_id
				WHERE	rur.user_id = ".$GLOBALS['_database']->qstr($person_id, get_magic_quotes_gpc())."
			";

			#error_log(preg_replace("/(\n|\r)/","",preg_replace("/\t/"," ",$get_roles_query)));
			$rs = $GLOBALS['_database']->Execute($get_roles_query);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = $GLOBALS['_database']->ErrorMsg();
				error_log($this->error);
				return 0;
			}

			$roles = array();
			while (list($id,$name) = $rs->FetchRow()) {
				if ($return_id) array_push($roles,$id);
				else array_push($roles,$name);
			}
			
			return $roles;
		}
		public function role_id($name) {
			# Get Role Query
			$get_role_query = "
				SELECT	id
				FROM	register_roles
				WHERE	name = ".$GLOBALS['_database']->qstr($name,get_magic_quotes_gpc());
	
			$rs = $GLOBALS['_database']->Execute($get_role_query);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = $GLOBALS['_database']->ErrorMsg();
				error_log($this->error);
				return 0;
			}

			list($id) = $rs->FetchRow();
			
			return $id;
		}
		public function expire() {
			$this->update($this->id,array("status" => 'EXPIRED'));
			return true;
		}
    }

    class RegisterOrganization {
		public $error;
		public $name;
		public $code;
		public $status;
		public $id;
		
		public function __construct($id = 0) {
			# Clear Error Info
			$this->error = '';

			# Database Initialization
			$schema = new RegisterSchema();
			if ($schema->error) {
				$this->error = "Failed to initialize schema: ".$schema->error;
			}
			elseif ($id) $this->details($id);
		}

		public function add($parameters) {
			$this->error = null;
			$add_object_query = "
				INSERT
				INTO	register_organizations
				(		id,code)
				VALUES
				(		null,?)
			";

			$rs = $GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$parameters['code']
				)
			);
			if (! $rs) {
				$this->error = "SQL Error in RegisterOrganization::add: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			$id = $GLOBALS['_database']->Insert_ID();
			return $this->update($id,$parameters);
		}

		public function update($id,$parameters = array()) {
			$this->error = null;
			
			# Bust Cache
			$cache_key = "organization[".$id."]";
			cache_unset($cache_key);

			$update_object_query = "
				UPDATE	register_organizations
				SET		id = id
			";

			if (isset($parameters['name']))
				$update_object_query .= ",
						name = ".$GLOBALS['_database']->qstr($parameters['name'],get_magic_quotes_gpc());

			if (isset($parameters['status']))
				$update_object_query .= ",
						status = ".$GLOBALS['_database']->qstr($parameters['status'],get_magic_quotes_gpc());

			$update_object_query .= "
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$update_object_query,
				array($id)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in RegisterOrganization::update: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $this->details($id);
		}
		public function get($code = '') {
			$this->error = null;
			$get_object_query .= "
				SELECT	id
				FROM	register_organizations
				WHERE	code = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($code)
			);
			if (! $rs) {
				$this->error = "SQL Error in RegisterOrganization::get: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details($id);
		}
		public function find($parameters = array()) {
			$this->error = null;
			$get_organizations_query = "
				SELECT	id
				FROM	register_organizations
				WHERE	id = id
			";

			if (isset($parameters['name'])) {
				if (in_array("name",$parameters['_like'])) {
					$get_organizations_query .= "
					AND		name like '%".preg_replace('/[^\w\-\.\_\s]/','',$parameters['name'])."%'";
				}
				else {
					$get_organizations_query .= "
					AND		name = ".$GLOBALS['_database']->qstr($parameters['name'],get_magic_quotes_gpc);
				}
			}
			if (isset($parameters['code'])) {
				$get_organizations_query .= "
				AND		code = ".$GLOBALS['_database']->qstr($parameters['code'],get_magic_quotes_gpc);
			}
			if (isset($parameters['status']))
				$get_organizations_query .= "
				AND		status = ".$GLOBALS['_database']->qstr($parameters['status'],get_magic_quotes_gpc);
			else
				$get_organizations_query .= "
				AND		status IN ('NEW','ACTIVE')";
			$get_organizations_query .= "
				ORDER BY name
			";
			
			if (isset($parameters['_limit']) and preg_match('/^\d+$/',$parameters['_limit'])) {
				if (preg_match('/^\d+$/',$parameters['_offset']))
					$get_organizations_query .= "
					LIMIT	".$parameters['_offset'].",".$parameters['_limit'];
				else
					$get_organizations_query .= "
					LIMIT	".$parameters['_limit'];
			}
			$rs = $GLOBALS['_database']->Execute($get_organizations_query);
			if (! $rs) {
				$this->error = "SQL Error in register::organization::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$organizations = array();
			while (list($id) = $rs->FetchRow()) {
				$organization = new RegisterOrganization();
				$organization->details($id);
				array_push($organizations,$organization);
			}
			return $organizations;
		}
		public function count($parameters = array()) {
			$this->error = null;
			$get_organizations_query .= "
				SELECT	count(*)
				FROM	register_organizations
				WHERE	id = id
			";

			if ($parameters['name']) {
				if (in_array("name",$parameters['_like'])) {
					$get_organizations_query .= "
					AND		name like '%".preg_replace('/[^\w\-\.\_\s]/','',$parameters['name'])."%'";
				}
				else {
					$get_organizations_query .= "
					AND		name = ".$GLOBALS['_database']->qstr($parameters['name'],get_magic_quotes_gpc);
				}
			}
			if ($parameters['code']) {
				$get_organizations_query .= "
				AND		code = ".$GLOBALS['_database']->qstr($parameters['code'],get_magic_quotes_gpc);
			}
			
			if (preg_match('/^\d+$/',$parameters['_limit'])) {
				if (preg_match('/^\d+$/',$parameters['_offset']))
					$get_organizations_query .= "
					LIMIT	".$parameters['_offset'].",".$parameters['_limit'];
				else
					$get_organizations_query .= "
					LIMIT	".$parameters['_limit'];
			}
			$rs = $GLOBALS['_database']->Execute($get_organizations_query);
			if (! $rs) {
				$this->error = "SQL Error in register::organization::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($count) = $rs->FetchRow();
			return $count;
		}
		public function details($id) {
			$this->error = null;
			if (! $id && isset($this->id)) $id = $this->id;

			$cache_key = "organization[".$id."]";

			# Cached Organization Object, Yay!
			if (($id) and ($organization = cache_get($cache_key))) {
				$organization->_cached = 1;
				$this->id = $organization->id;
				$this->name = $organization->name;
				$this->code = $organization->code;
				$this->status = $organization->status;
				$this->_cached = $organization->_cached;

				# In Case Cache Corrupted
				if ($organization->id) {
					return $organization;
				}
				else {
					$this->error = "Organization $id returned unpopulated cache";
				}
			}

			# Get Details for Organization
			$get_details_query = "
				SELECT	id,
						code,
						name,
						status
				FROM	register_organizations
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_details_query,
				array($id)
			);
			if (! $rs) {
				$this->error = "SQL Error in register::Organization::details: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$object = $rs->FetchNextObject(false);
			$this->id = $object->id;
			$this->name = $object->name;
			$this->code = $object->code;
			$this->status = $object->status;

			# Cache Customer Object
			app_log("Setting cache key ".$cache_key);
			if ($object->id) $result = cache_set($cache_key,$object);
			app_log("Cache result: ".$result);

			return $object;
		}
		public function members($id = 0) {
			if (! preg_match('/^\d+$/',$id)) $id = $this->id;
			$_customer = new RegisterCustomer();
			#print "Finding members of org $id<br>\n";
			return $_customer->find(array('organization_id' => $id));
		}
    }

    class RegisterAdmin extends RegisterCustomer {
		public function __construct() {
			# Database Initialization
			$schema = new RegisterSchema();
			if ($schema->error) {
				$this->error = "Failed to initialize schema: ".$schema->error;
			}
		}
	
		public function details($id) {
		    $details = parent::details($id);
		    $details->roles = $this->roles($id);
			$_department = new Department();
			$details->department = $_department->details($details->department_id);
		    return $details;
		}
		public function update($id,$parameters=array()) {
			parent::update($id,$parameters);
			
			if (isset($parameters['department_id'])) {
				$update_admin_query = "
					UPDATE	register_users
					SET		department_id = ".$GLOBALS['_database']->qstr($parameters['department_id'],get_magic_quotes_gpc())."
					WHERE	id = ".$GLOBALS['_database']->qstr($id,get_magic_quotes_gpc());
				$GLOBALS['_database']->Execute($update_admin_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in register::admin::update: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
			}
			return $this->details($id);
		}
    }

	class RegisterDepartment {
		public $id;
		public $name;
		public $error;

		public function __construct() {
			# Clear Error Info
			$this->error = '';
			# Database Initialization
			$schema = new RegisterSchema();
			if ($schema->error) {
				$this->error = "Failed to initialize schema: ".$schema->error;
			}
		}
		public function find($parameters = array()) {
			$get_department_query = "
				SELECT	id
				FROM	register_departments
				WHERE	id = id
			";
			$rs = $GLOBALS['_database']->Execute($get_department_query);
			if (! $rs)
			{
				$this->error = "SQL Error in register::department::find: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			$departments = array();
			while (list($id) = $rs->FetchRow())
			{
				$details = (object) $this->details($id);
				array_push($departments,$details);
			}
			return $departments;
		}
		public function members($id) {
			$_admin = new Admin();
			$admins = $_admin->find(array("department" => $id));
			if ($_admin->error)	{
				$this->error = $_admin->error;
				return 0;
			}
			return $admins;
		}
		
		public function details($id) {
			$get_object_query = "
				SELECT	id,
						name,
						parent_id,
						manager_id
				FROM	register_departments
				WHERE	id = ".$GLOBALS['_database']->qstr($id,get_magic_quotes_gpc());
			$rs = $GLOBALS['_database']->Execute($get_object_query);
			if (! $rs) {
				$this->error = $GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			return (object) $rs->FetchRow();
		}
	}
	class RegisterRole {
		public $id;
		public $name;
		public $error;

        public function add($parameters = array()) {
            if (! preg_match('/^[\w\-\_\s]+$/',$parameters['name'])) {
                $this->error = "Failed to add role, invalid name";
                return null;
            }

            $add_object_query = "
                INSERT
                INTO    register_roles
                (       name)
                VALUES
                (       ?)
				ON DUPLICATE KEY UPDATE
						name = name
            ";
            $GLOBALS['_database']->execute(
				$add_object_query,
				array(
					$parameters['name']
				)
			);
            if ($GLOBALS['_database']->ErrorMsg()) {
                $this->error = "SQL Error in Role::add: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($this->id,$parameters);
        }

		public function update($id,$parameters = array()) {
			if (! preg_match('/^\d+$/',$id)) {
				if ($this->id) $id = $this->id;
				else {
					$this->error = "Valid id required in Role::add";
					return null;
				}
			}

			$update_object_query = "
				UPDATE	register_roles
				SET		id = id";

			if ($parameters['description'])
				$update_object_query .= ",
						description = ".$GLOBALS['_database']->qstr($parameters['description'],get_magic_quotes_gpc());

			$GLOBALS['_database']->Execute($update_object_query);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Role::update: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $this->details($id);
		}

		public function find($parameters = array()) {
			$get_objects_query = "
				SELECT	id
				FROM	register_roles
				WHERE	id = id
			";
			$rs = $GLOBALS['_database']->Execute($get_objects_query);
			if (! $rs) {
				$this->error = "SQL Error in RegisterRole::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$roles = array();
			while (list($id) = $rs->FetchRow()) {
				$details = $this->details($id);
				array_push($roles,$details);
			}
			return $roles;
		}
		public function get($code) {
			$get_object_query = "
				SELECT	id
				FROM	register_roles
				WHERE	name = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($code)
			);
			if (! $rs) {
				$this->error = "SQL Error in RegisterRole::get: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			list($this->id) = $rs->FetchRow();
			return $this->details($this->id);
		}
		public function members($id) {
			$get_members_query = "
				SELECT	user_id
				FROM	register_users_roles
				WHERE	role_id = ".$GLOBALS['_database']->qstr($id,get_magic_quotes_gpc());
			$rs = $GLOBALS['_database']->Execute($get_members_query);
			if (! $rs)
			{
				$this->error = "SQL Error in register::role::members: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			$admins = array();
			while (list($admin_id) = $rs->FetchRow())
			{
				$_admin = new Admin();
				$admin = $_admin->details($admin_id);
				array_push($admins,$admin);
			}
			return $admins;
		}

		public function details($id) {
			$get_object_query = "
				SELECT	id,
						name,
						description
				FROM	register_roles
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($id)
			);
			if (! $rs) {
				$this->error = "SQL Error in RegisterRole::details: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $rs->FetchNextObject(false);
		}

		public function notify($code,$message) {
			require_once(MODULES."/email/_classes/default.php");
			$role = $this->get($code);
			if (! $role->id)
			{
				$this->error = "Role not found";
				return null;
			}
			$members = $this->members($role->id);
			foreach ($members as $member)
			{
				$_member = new RegisterPerson();
				$_member->notify($member->id,$message);
			}
		}
	}
    class RegisterRelationship {
		public $error;
		public $parent_id;
		public $person_id;

		public function add($parent_id,$person_id)
		{
			$add_relationship_query = "
				INSERT
				INTO	register_relations
				(		parent_id,
						person_id
				)
				VALUES
				(		?,?)
			";
			$GLOBALS['_database']->Execute(
				$add_relationship_query,
				array($parent_id,
					  $person_id
				)
			);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in RegisterRelationship::add: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $this;
		}
		public function delete($parent_id,$person_id)
		{
			$delete_relationship_query = "
				DELETE
				FROM	register_relations
				WHERE	parent_id = ?
				AND		person_id = ?
			";
			$GLOBALS['_database']->Execute(
				$delete_relationship_query,
				array($parent_id,
					  $person_id
				)
			);
			if (! $GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in RegisterRelationship::delete: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return 1;
		}
		public function exists($parent_id,$person_id)
		{
			$check_relationship_query = "
				SELECT	1
				FROM	register_relations
				WHERE	parent_id = ?
				AND		person_id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$check_relationship_query,
				array($parent_id,
					  $person_id
				)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in RegisterRelationship::exists: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($exists) = $rs->FetchRow();
			return $exists;
		}
		public function parents($person_id)
		{
			$get_parents_query = "
				SELECT	parent_id
				FROM	register_relations
				WHERE	person_id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_parents_query,
				array($person_id)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in RegisterRelationship::parents: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$parents = array();
			while (list($parent_id) = $rs->FetchRow())
			{
				$_person = new RegisterPerson();
				$parent = $_person->details($parent_id);
				array_push($parents,$parent);
			}
			return $parents;
		}
		public function children($parent_id)
		{
			$get_child_query = "
				SELECT	person_id
				FROM	register_relations
				WHERE	parent_id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_child_query,
				array($parent_id)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in RegisterRelationship::children: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$children = array();
			while (list($person_id) = $rs->FetchRow())
			{
				$_person = new RegisterPerson();
				$person = $_person->details($person_id);
				array_push($children,$person);
			}
			return $children;
		}
	}
	class RegisterPasswordToken {
		public $error;
		public $person_id;
		public $expiration;
		public $code;
		
		public function add($person_id)
		{
			# Get Large Random value
			$randval = mt_rand();		

			# Use hash to further bury session id
			$code = hash('sha256',$randval);

			# Add recovery record to database
			$add_object_query = "
				REPLACE
				INTO	register_password_tokens
				VALUES	(?,?,date_add(sysdate(),INTERVAL 1 day),?)
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$person_id,
					$code,
					$GLOBALS['_REQUEST_']->client_ip
				)
			);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in RegisterPasswordToken::add: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $code;
		}
		
		public function consume($code)
		{
			# Get Code from Database
			$get_record_query = "
				SELECT	person_id
				FROM	register_password_tokens
				WHERE	code = ?
				AND		date_expires > sysdate()
			";

			$rs = $GLOBALS['_database']->Execute(
				$get_record_query,
				array($code)
			);

			if (! $rs)
			{
				$this->error = "SQL Error in RegisterRecovery::consume: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			if ($rs->RecordCount())
			{
				list($person_id) = $rs->FetchRow();
				
				$delete_record_query = "
					DELETE
					FROM	register_password_tokens
					WHERE	person_id = ?
				";
				$GLOBALS['_database']->Execute(
					$delete_record_query,
					array($person_id)
				);
				return $person_id;
			}
			else return 0;
		}
	}
	class OrganizationOwnedProduct {
        public $error;

        public function add($organization_id,$product_id,$quantity,$parameters=array())
        {
            $add_product_query = "
                INSERT
                INTO    register_organization_products
                (       organization_id,
                        product_id,
                        quantity
                )
                VALUES
                (       ?,
                        ?,
                        ?
                )
                ON DUPLICATE KEY
                UPDATE
                        quantity = quantity + ?
            ";
            $GLOBALS['_database']->Execute(
				$add_product_query,
				array($organization_id,
					  $product_id,
					  $quantity,
					  $quantity
				)
			);
            if ($GLOBALS['_database']->ErrorMsg())
            {
                $this->error = "SQL Error in OrganizationProducts::add:".$GLOBALS['_database']->ErrorMsg();
                return 0;
            }
            return 1;
        }

        public function consume($organization_id,$product_id,$quantity = 1)
        {
            $use_product_query = "
                UPDATE  register_organization_products
                SET     quantity = quantity - ?
                WHERE   organization_id = ?
                AND     product_id = ?
            ";

            $GLOBALS['_database']->Execute(
				$use_product_query,
				array(
					$quantity,
					$organization_id,
					$product_id
				)
			);
            if ($GLOBALS['_database']->ErrorMsg())
            {
                $this->error = "SQL Error in OrganizationProducts::use:".$GLOBALS['_database']->ErrorMsg();
                return 0;
            }
            return $this->details($organization_id,$product_id);
        }

        public function get($organization_id,$product_id)
        {
            $get_object_query = "
                SELECT  organization_id,product_id
                FROM    register_organization_products
                WHERE   organization_id = ?
				AND		product_id = ?
            ";
            if (! role('register manager'))
            {
                $organization_id = $GLOBALS['_SESSION_']->customer->organization->id;
			}
            $rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array(
					$organization_id,
					$product_id
				)
			);
            if (! $rs)
            {
                $this->error = "SQL Error in OrganizationOwnedProduct::get: ".$GLOBALS['_database']->ErrorMsg();
                return null;
            }

            $objects = array();

            list($organization_id,$product_id) = $rs->FetchRow();

            $object = $this->details($organization_id,$product_id);
            if ($this->error)
            {
                $this->error = "Error getting details for OrganizationOwnedProduct: ".$this->error;
                return null;
			}
            return $object;
        }
        public function find($parameters = array())
        {
            $get_objects_query = "
                SELECT  organization_id,product_id
                FROM    register_organization_products
                WHERE   product_id = product_id
            ";
            if (preg_match('/^\d+$/',$parameters['product_id']))
                $get_objects_query .= "
                AND     product_id = ".$parameters['product_id'];

            if (! role('register manager'))
            {
                if (preg_match('/^\d+/',$GLOBALS['_customer']->organization->id))
                    $parameters['organization_id'] = $GLOBALS['_customer']->organization->id;
                else
                    $parameters['organization_id'] = 0;
            }
            if (preg_match('/^\d+$/',$parameters['organization_id']))
                $get_objects_query .= "
                AND     organization_id = ".$parameters['organization_id'];

            $rs = $GLOBALS['_database']->Execute($get_objects_query);
            if (! $rs)
            {
                $this->error = "SQL Error in OrganizationOwnedProduct::find: ".$GLOBALS['_database']->ErrorMsg();
                return null;
            }

            $objects = array();

            while (list($organization_id,$product_id) = $rs->FetchRow())
            {
                $object = $this->details($organization_id,$product_id);
                if ($this->error)
                {
                    $this->error = "Error getting details for OrganizationOwnedProduct: ".$this->error;
                    return null;
                }
                array_push($objects,$object);
            }

            return $objects;
        }

        private function details($organization_id,$product_id)
        {
            $get_details_query = "
                SELECT  organization_id,product_id,quantity
                FROM    register_organization_products
                WHERE   organization_id = ?
				AND		product_id = ?
            ";

            $rs = $GLOBALS['_database']->Execute(
				$get_details_query,
				array($organization_id,$product_id)
			);
            if (! $rs)
            {
                $this->error = "SQL Error in OrganizationOwnedProducts::details: ".$GLOBALS['_database']->ErrorMsg();
                return null;
            }
            return $rs->FetchNextObject(false);
        }
    }
	class RegisterSchema {
		public $error;
		public $errno;
		public $module = "register";

		public function __construct() {
			$this->upgrade();
		}
		
		public function version() {
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();
			$info_table = "register__info";

			if (! in_array($info_table,$schema_list)) {
				# Create __info table
				$create_table_query = "
					CREATE TABLE `$info_table` (
						label	varchar(100) not null primary key,
						value	varchar(255)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating info table in RegisterSchema::__construct: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
			}

			# Check Current Schema Version
			$get_version_query = "
				SELECT	value
				FROM	`$info_table`
				WHERE	label = 'schema_version'
			";

			$rs = $GLOBALS['_database']->Execute($get_version_query);
			if (! $rs) {
				$this->error = "SQL Error in RegisterInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			list($version) = $rs->FetchRow();
			if (! $version) $version = 0;
			return $version;
		}
		public function upgrade() {
			$current_schema_version = $this->version();

			if ($current_schema_version < 1) {
				app_log("Upgrading schema to version 1",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `register_organizations` (
						`id`			int(11) NOT NULL AUTO_INCREMENT,
						`name`			varchar(255) NOT NULL,
						`code`			varchar(100) NOT NULL,
						PRIMARY KEY (`id`),
						UNIQUE KEY `UK_CODE` (`code`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating organizations table in RegisterInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `register_departments` (
						`id`			int(11) NOT NULL AUTO_INCREMENT,
						`name`			varchar(255) NOT NULL,
						`description`	text,
						`manager_id`	int(11),
						`parent_id`		int(11),
						PRIMARY KEY (`id`),
						UNIQUE KEY `UK_CODE` (`name`),
						INDEX `IDX_PARENT` (`parent_id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating departments table in RegisterInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `register_users` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`status`	enum('NEW','ACTIVE','HIDDEN','DELETED') NOT NULL DEFAULT 'ACTIVE',
						`last_name` varchar(100) DEFAULT NULL,
						`middle_name` varchar(100) DEFAULT NULL,
						`first_name` varchar(100) DEFAULT NULL,
						`login` varchar(45) NOT NULL,
						`password` varchar(64) NOT NULL DEFAULT '',
						`title` varchar(100) DEFAULT '',
						`department_id` int(11) NOT NULL DEFAULT '0',
						`organization_id` int(11) DEFAULT '0',
						`opt_in` boolean NOT NULL DEFAULT '0',
						`date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
						`date_updated` timestamp NOT NULL,
						`date_expires` datetime NOT NULL,
						`auth_method` varchar(100) DEFAULT 'local',
						`unsubscribe_key` varchar(50) NOT NULL DEFAULT '',
						`validation_key` varchar(45) DEFAULT NULL,
						`custom_metadata` text,
						PRIMARY KEY (`id`),
						UNIQUE KEY `uk_login` (`login`),
						KEY `idx_organization` (`organization_id`),
						KEY `idx_unsubscribe_key` (`unsubscribe_key`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					app_log("SQL Error creating users table in RegisterInit::__construct: ".$GLOBALS['_database']->ErrorMsg(),'error',__FILE__,__LINE__);
					$this->error = "Error creating register_users table";
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `register_contacts` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`person_id` int(11) NOT NULL,
						`type` enum('phone','email','sms','facebook','twitter') NOT NULL,
						`description` varchar(100),
						`notify` tinyint(1) NOT NULL default 0,
						`value` varchar(255) NOT NULL,
						`notes` varchar(255) DEFAULT NULL,
						PRIMARY KEY (`id`),
						KEY `fk_person_id` (`person_id`),
						KEY `fk_type` (`type`),
						CONSTRAINT `register_contact_listing_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `register_users` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating register_contacts table in RegisterInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `register_roles` (
						`id` int(10) NOT NULL AUTO_INCREMENT,
						`name` varchar(45) NOT NULL,
						`description` varchar(255) NOT NULL DEFAULT '',
						PRIMARY KEY (`id`),
						UNIQUE KEY `uk_name` (`name`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating register_roles table in RegisterInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `register_users_roles` (
						`user_id` int(11) NOT NULL AUTO_INCREMENT,
						`role_id` int(10) NOT NULL,
						PRIMARY KEY (`user_id`,`role_id`),
						FOREIGN KEY `register_users_roles_ibfk_1` (`user_id`) REFERENCES `register_users` (`id`),
						FOREIGN KEY `register_users_roles_ibfk_2` (`role_id`) REFERENCES `register_roles` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating register_users_roles table in RegisterInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$add_roles_query = "
					INSERT
					INTO	register_roles
					VALUES	(null,'register manager','Can view/edit customers and organizations'),
							(null,'register reporter','Can view customers and organizations')
				";
				$GLOBALS['_database']->Execute($add_roles_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error adding register roles in RegisterInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 1;
				$update_schema_version = "
					INSERT
					INTO	register__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					app_log("SQL Error in RegisterInit::schema_manager: ".$GLOBALS['_database']->ErrorMsg(),'error',__FILE__,__LINE__);
					$this->error = "Error adding roles to database";
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			if ($current_schema_version < 2) {
				app_log("Upgrading schema to version 2",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `register_organization_products` (
						`organization_id`	int(11) NOT NULL,
						`product_id`		int(11) NOT NULL,
						`quantity`			decimal(9,2) NOT NULL,
						`date_expires`		datetime DEFAULT '9999-12-31 23:59:59',
						PRIMARY KEY `pk_organization_product` (`organization_id`,`product_id`),
						FOREIGN KEY `fk_organization` (`organization_id`) REFERENCES `register_organizations` (`id`),
						FOREIGN KEY `fk_product` (`product_id`) REFERENCES `product_products` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating organizations table in RegisterInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 2;
				$update_schema_version = "
					INSERT
					INTO	register__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					app_log("SQL Error in RegisterInit::schema_manager: ".$GLOBALS['_database']->ErrorMsg(),'error',__FILE__,__LINE__);
					$this->error = "Error adding roles to database";
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			if ($current_schema_version < 3) {
				app_log("Upgrading schema to version 3",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE `register_person_metadata` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`person_id` int(11) NOT NULL,
						`key` varchar(32) NOT NULL,
						`value` text,
						PRIMARY KEY (`id`),
						UNIQUE KEY `person_id` (`person_id`,`key`),
						CONSTRAINT `person_metadata_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `register_users` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating organizations table in RegisterInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 3;
				$update_schema_version = "
					INSERT
					INTO	register__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					app_log("SQL Error in RegisterInit::schema_manager: ".$GLOBALS['_database']->ErrorMsg(),'error',__FILE__,__LINE__);
					$this->error = "Error adding roles to database";
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			if ($current_schema_version < 4) {
				app_log("Upgrading schema to version 4",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					ALTER TABLE register_users ADD timezone varchar(32) NOT NULL DEFAULT 'America/New_York'
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating organizations table in RegisterInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 4;
				$update_schema_version = "
					INSERT
					INTO	register__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					app_log("SQL Error in RegisterInit::schema_manager: ".$GLOBALS['_database']->ErrorMsg(),'error',__FILE__,__LINE__);
					$this->error = "Error adding roles to database";
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			if ($current_schema_version < 5) {
				app_log("Upgrading schema to version 5",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `register_relations` (
						`parent_id` int(11) NOT NULL,
						`person_id` int(11) NOT NULL,
						PRIMARY KEY (`parent_id`,`person_id`),
						FOREIGN KEY `fk_parent_id` (`parent_id`) REFERENCES `register_users` (`id`),
						FOREIGN KEY `fk_person_id` (`person_id`) REFERENCES `register_users` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating register relations table in RegisterInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 5;
				$update_schema_version = "
					INSERT
					INTO	register__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					app_log("SQL Error in RegisterInit::schema_manager: ".$GLOBALS['_database']->ErrorMsg(),'error',__FILE__,__LINE__);
					$this->error = "Error adding roles to database";
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			if ($current_schema_version < 6) {
				app_log("Upgrading schema to version 6",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `register_password_tokens` (
						`person_id` int(11) NOT NULL,
						`code`		varchar(255) NOT NULL,
						`date_expires`	datetime DEFAULT '1990-01-01 00:00:00',
						`client_ip`		varchar(32),
						PRIMARY KEY (`person_id`),
						FOREIGN KEY `fk_person_id` (`person_id`) REFERENCES `register_users` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating register relations table in RegisterInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 6;
				$update_schema_version = "
					INSERT
					INTO	register__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					app_log("SQL Error in RegisterInit::schema_manager: ".$GLOBALS['_database']->ErrorMsg(),'error',__FILE__,__LINE__);
					$this->error = "Error adding roles to database";
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			if ($current_schema_version < 7) {
				app_log("Upgrading schema to version 7",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$alter_table_query = "
					ALTER TABLE `register_users` MODIFY COLUMN `status` enum('NEW','ACTIVE','EXPIRED','HIDDEN','DELETED') NOT NULL DEFAULT 'ACTIVE'
				";
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_users table in RegisterInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 7;
				$update_schema_version = "
					INSERT
					INTO	register__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					app_log("SQL Error in RegisterInit::schema_manager: ".$GLOBALS['_database']->ErrorMsg(),'error',__FILE__,__LINE__);
					$this->error = "Error adding roles to database";
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			if ($current_schema_version < 8) {
				app_log("Upgrading schema to version 8",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$alter_table_query = "
					ALTER TABLE `register_organizations` ADD COLUMN `status` enum('NEW','ACTIVE','EXPIRED','HIDDEN','DELETED') NOT NULL DEFAULT 'ACTIVE'
				";
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_organizations table in RegisterInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 8;
				$update_schema_version = "
					INSERT
					INTO	register__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					app_log("SQL Error in RegisterInit::schema_manager: ".$GLOBALS['_database']->ErrorMsg(),'error',__FILE__,__LINE__);
					$this->error = "Error adding roles to database";
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
		}
	}
	
	# Because We Already Used Basic Names
	class Person extends RegisterPerson
	{}
	class Customer extends RegisterCustomer
	{}
	class Admin extends RegisterAdmin
	{}
	class Department extends RegisterDepartment
	{}
	class Role extends RegisterRole
	{}

	class RegisterCustomers {
		public function expire($date_threshold) {
			if (get_mysql_date($date_threshold))
				$date = get_mysql_date($date_threshold);
			else {
				$this->error = "Invalid date: '$date_threshold'";
				return null;
			}

			$find_people_query = "
				SELECT	u.id,
						u.login,
						u.date_created,
						IFNULL(max(s.last_hit_date),'0000-00-00 00:00:00') last_login
				FROM	register_users u
				LEFT OUTER JOIN session_sessions s
				ON		s.user_id = u.id
				AND		s.company_id = ".$GLOBALS['_SESSION_']->company."
				WHERE	u.status in ('ACTIVE','NEW')
				GROUP BY u.id
				HAVING	last_login < '$date'
				AND		u.date_created < '$date'
			";

			$people = $GLOBALS['_database']->Execute($find_people_query);
			if (! $people) {
				$this->error = "SQL Error in RegisterCustomers::expire: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$count = 0;
			while($record = $people->FetchNextObject(false)) {
				app_log("Expiring ".$record->login."' [".$record->id."]",'notice');
				$customer = new RegisterCustomer($record->id);
				$customer->update($record->id,array("status" => "EXPIRED"));
				$count ++;
			}
			return $count;
		}
		public function find($parameters = array()) {
			$find_person_query = "
				SELECT	id
				FROM	register_users
				WHERE	id = id";
	
			if (isset($parameters['id']) && preg_match('/^\d+$/',$parameters['id']))
			{
				$find_person_query .= "
				AND		id = ".$parameters['id'];
			}
			elseif (isset($parameters['id']))
			{
				$this->error = "Invalid id in Person::find";
				return null;
			}
			if (isset($parameters['code']))
			{
				$find_person_query .= "
				AND		login = ".$GLOBALS['_database']->qstr($parameters['code'],get_magic_quotes_gpc());
			}
			if (isset($parameters['status']))
			{
				if (is_array($parameters['status']))
				{
					$count = 0;
					$find_person_query .= "
					AND	status IN (";
					foreach ($parameters['status'] as $status)
					{
						if ($count > 0) $find_person_query .= ","; 
						$count ++;
						if (preg_match('/^[\w\-\_\.]+$/',$status))
						$find_person_query .= $status;
					}
				}
				else {
					$find_person_query .= "
						AND		status = ".$GLOBALS['_database']->qstr($parameters['status'],get_magic_quotes_gpc());
				}
			}
			else
			{
				$find_person_query .= "
				AND		status not in ('EXPIRED','HIDDEN','DELETED')";
			}
	
			if (isset($parameters['first_name']))
			{
				$find_person_query .= "
				AND		first_name = ".$GLOBALS['_database']->qstr($parameters['first_name'],get_magic_quotes_gpc());
			}
	
			if (isset($parameters['last_name']))
			{
				$find_person_query .= "
				AND		last_name = ".$GLOBALS['_database']->qstr($parameters['last_name'],get_magic_quotes_gpc());
			}
	
			if (isset($parameters['email_address']))
			{
				$find_person_query .= "
				AND		email_address = ".$GLOBALS['_database']->qstr($parameters['email_address'],get_magic_quotes_gpc());
			}

			if (isset($parameters['department_id']))
			{
				$find_person_query .= "
				AND		department_id = ".$GLOBALS['_database']->qstr($parameters['department_id'],get_magic_quotes_gpc());
			}
			if (isset($parameters['organization_id']))
			{
				$find_person_query .= "
				AND		organization_id = ".$GLOBALS['_database']->qstr($parameters['organization_id'],get_magic_quotes_gpc());
			}

			if (preg_match('/^(login|first_name|last_name|organization_id)$/',$parameters['_sort']))
			{
				$find_person_query .= " ORDER BY ".$parameters['_sort'];
			}
			else
				$find_person_query .= " ORDER BY login";

			if (isset($parameters['_limit']) && preg_match('/^\d+$/',$parameters['_limit']))
			{
				if (preg_match('/^\d+$/',$parameters['_offset']))
					$find_person_query .= "
					LIMIT	".$parameters['_offset'].",".$parameters['_limit'];
				else
					$find_person_query .= "
					LIMIT	".$parameters['_limit'];
			}

			$rs = $GLOBALS['_database']->Execute($find_person_query);
			if (! $rs)
			{
				$this->error = "SQL Error in RegisterPerson::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$people = array();
			while (list($id) = $rs->FetchRow())
			{
				$customer = new RegisterCustomer();
				array_push($people,$customer->details($id));
			}
			return $people;
		}
		public function count($parameters = array()) {
			$find_person_query = "
				SELECT	count(*)
				FROM	register_users
				WHERE	id = id";
	
			if (preg_match('/^\d+$/',$parameters['id']))
			{
				$find_person_query .= "
				AND		id = ".$parameters['id'];
			}
			elseif ($parameters['id'])
			{
				$this->error = "Invalid id in Person::find";
				return 0;
			}
			if ($parameters['code'])
			{
				$find_person_query .= "
				AND		login = ".$GLOBALS['_database']->qstr($parameters['code'],get_magic_quotes_gpc());
			}
	
			if ($parameters['first_name'])
			{
				$find_person_query .= "
				AND		first_name = ".$GLOBALS['_database']->qstr($parameters['first_name'],get_magic_quotes_gpc());
			}
	
			if ($parameters['last_name'])
			{
				$find_person_query .= "
				AND		last_name = ".$GLOBALS['_database']->qstr($parameters['last_name'],get_magic_quotes_gpc());
			}
	
			if ($parameters['email_address'])
			{
				$find_person_query .= "
				AND		email_address = ".$GLOBALS['_database']->qstr($parameters['email_address'],get_magic_quotes_gpc());
			}

			if ($parameters['department_id'])
			{
				$find_person_query .= "
				AND		department_id = ".$GLOBALS['_database']->qstr($parameters['department_id'],get_magic_quotes_gpc());
			}
			if ($parameters['organization_id'])
			{
				$find_person_query .= "
				AND		organization_id = ".$GLOBALS['_database']->qstr($parameters['organization_id'],get_magic_quotes_gpc());
			}
			if (isset($parameters['status']))
			{
				if (is_array($parameters['status']))
				{
					$count = 0;
					$find_person_query .= "
					AND	status IN (";
					foreach ($parameters['status'] as $status)
					{
						if ($count > 0) $find_person_query .= ","; 
						$count ++;
						if (preg_match('/^[\w\-\_\.]+$/',$status))
						$find_person_query .= $status;
					}
				}
				else {
					$find_person_query .= "
						AND		status = ".$GLOBALS['_database']->qstr($parameters['status'],get_magic_quotes_gpc());
				}
			}
			else
			{
				$find_person_query .= "
				AND		status not in ('EXPIRED','HIDDEN','DELETED')";
			}
			
			$rs = $GLOBALS['_database']->Execute($find_person_query);
			if (! $rs)
			{
				$this->error = "SQL Error in RegisterPerson::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			list($count) = $rs->FetchRow();
			return $count;
		}
	}
?>
