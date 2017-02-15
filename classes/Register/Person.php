<?php
	namespace Register;

    class Person {
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
			$schema = new Schema();
			if ($schema->error) {
				$this->error = "Failed to initialize schema: ".$schema->error;
			}
			else {
				# Find Person if id given
				if ($id) {
					$this->id= $id;
					$this->details();
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
	
		public function details() {
			if (! $this->id) {
				$this->error = "ID Required for Person::details()";
				return null;
			}

			$cache_key = "customer[".$this->id."]";

			# Cached Customer Object, Yay!
			$cache = new \Cache($cache_key);
			if (($this->id) and ($customer = $cache->get())) {
				$this->first_name = $customer->first_name;
				$this->last_name = $customer->last_name;
				$this->code = $customer->code;
				$this->login = $customer->code;
				$this->organization_id = $customer->organization_id;
				$this->department_id = $customer->department_id;
				if (isset($customer->department)) $this->department = $customer->department;
				$this->organization = new Organization($customer->organization_id);
				$this->status = $customer->status;
				$this->timezone = $customer->timezone;
				$this->auth_method = $customer->auth_method;
				$customer->_cached = 1;

				# In Case Cache Corrupted
				if ($customer->id) {
					app_log("Customer ".$this->login." [".$this->id."] found in cache",'debug',__FILE__,__LINE__);
					return $customer;
				}
				else {
					$this->error = "Customer ".$this->id." returned unpopulated cache";
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
				array($this->id)
			);
			if (! $rs) {
				$this->error = "SQL Error in RegisterPerson::details: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$customer = $rs->FetchNextObject(false);
			if (! isset($customer->id)) {
				app_log("No customer found for ".$this->id);
			}

			# Fetch Organization Info
			#app_log("Getting organization for person '$id'",'debug',__FILE__,__LINE__);
			$customer->organization = new Organization($customer->organization_id);
			if ($customer->organization->error) {
				$this->error = "Error initializing organization: ".$customer->organization->error;
				return null;
			}

			# Fetch Contact Info
			#app_log("Getting contact details for person '$person_id'",'debug',__FILE__,__LINE__);
			$_contact = new Contact();
			if ($_contact->error) {
				$this->error = "Error initializing contact: ".$_contact->error;
				return null;
			}
			$contact_list = $_contact->find(array("person_id" => $customer->id));
			if ($_contact->error) {
				$this->error = "Error finding contacts: ".$_contact->error;
				return null;
			}
			foreach ($contact_list as $contact) {
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

		public function password_strength($string) {
			$password_strength = strlen($string);
			if (preg_match('/[A-Z]/',$string)) $password_strength += 1;
			if (preg_match('/[\@\$\_\-\.\!\&]/',$string)) $password_strength += 1;
			if (preg_match('/\d/',$string)) $password_strength += 1;
			if (preg_match('/[a-z]/',$string)) $password_strength += 1;
			return $password_strength;
		}
		public function add($parameters) {
		    if (! preg_match("/^[\w\-\_@\.\+\s]{2,100}\$/", $parameters['login'])) {
				$this->error = "Invalid Login";
				return null;
			}
			if (! $GLOBALS['_config']->no_password) {
				$password_length = strlen($parameters['password']);

				if ($this->password_strength($parameters['password']) < $_GLOBALS['_config']->register->minimum_password_strength) {
					$this->error = "Password too weak.";
					return null;
				}
				if ($password_length > 100) {
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
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Person::add: Error: ".$GLOBALS['_database']->ErrorMsg()." Query: ".preg_replace("/[\t\r\n]/"," ",$add_user_query);
				return null;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			app_log("Added customer ".$parameters['login']." [".$this->id."]",'debug',__FILE__,__LINE__);
			return $this->details();
		}

		public function update($parameters = array()) {
			if (! $this->id) {
				$this->error = "User ID Required for Update";
				return null;
			}

			if ((! role("register manager")) and ($GLOBALS['_SESSION_']->customer->id != $this->id)) {
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

			foreach (array_keys($valid_params) as $param) {
				if (array_key_exists($param,$parameters)) {
					if ($param == "password") {
						app_log("Changing password",'notice',__FILE__,__LINE__);
						$update_customer_query .= ",
							`password` = password(".$GLOBALS['_database']->qstr($parameters[$param],get_magic_quotes_gpc()).")";
					}
					else {
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
				array($this->id)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in RegisterPerson::update: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			# Bust Cache
			$cache_key = "customer[".$this->id."]";
			cache_unset($cache_key);

			# Get Updated Information
			return $this->details();
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
			if (! $rs) {
				$this->error = "SQL Error in Product::getMeta: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$metadata = array();
			while (list($label,$value) = $rs->FetchRow()) {
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
			$_contact = new Contact();
			$contact = $_contact->add($parameters);
			if ($_contact->error)
			{
				$this->error = "Error adding contact: ".$_contact->error;
				return null;
			}
			return $contact;
		}
		public function updateContact($id,$parameters = array()) {
			$contact = new Contact();
			return $contact->update($id,$parameters);
		}
		public function deleteContact($id) {
			$contact = new Contact();
			return $contact->delete($id);
		}
		public function findContacts($parameters = array()) {
			$contact = new Contact();
			return $contact->find($parameters);
		}
		public function contactDetails($id) {
			$contact = new Contact();
			return $contact->details($id);
		}
		public function notifyContact($parameters = array()) {
			$contact = new Contact();
			return $contact->notify($parameters);
		}
		public function notify($id,$message) {
			require_module('email');

			# Get Contact Info
			$_contact = new Contact();
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
?>