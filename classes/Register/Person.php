<?php
namespace Register;

class Person {

    public $id;
    public $first_name;
    public $last_name;
    public $location;
    public $organization;
    public $error;
    public $code;
    public $message;
    public $department;
    public $_cached = 0;
    public $status;

    public function __construct($id = 0) {

        # Clear Error Info
        $this->error = '';

        # Find Person if id given
        if (isset($id) && is_numeric($id)) {
            $this->id = $id;
            $this->details();
        }
    }

    public function exists($login) {
        list($person) = $this->find(array(
            "login" => $login
        ));

        if ($person->id) return true;
        else return false;
    }
    
    public function setId($id=0) {
        $this->id = $id;
    }
    
    public function details() {
    
        $cache_key = "customer[" . $this->id . "]";

        # Cached Customer Object, Yay!
        $cache = new \Cache\Item($GLOBALS['_CACHE_'], $cache_key);
        if (($this->id) and ($customer = $cache->get())) {
            $this->first_name = $customer->first_name;
            $this->last_name = $customer->last_name;
            $this->code = $customer->code;
            $this->login = $customer->code;
            $this->department_id = $customer->department_id;
            if (isset($customer->department)) $this->department = $customer->department;
            $this->organization = new Organization($customer->organization_id);
            $this->status = $customer->status;
            $this->timezone = $customer->timezone;
            $this->auth_method = $customer->auth_method;
            $this->automation = $customer->automation;
            $customer->_cached = 1;

            # In Case Cache Corrupted
            if ($customer->id) {
                app_log("Customer " . $this->login . " [" . $this->id . "] found in cache", 'trace', __FILE__, __LINE__);
                return $customer;
            }
            else {
                $this->error = "Customer " . $this->id . " returned unpopulated cache";
            }
        }

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
						timezone,
						automation
				FROM	register_users
				WHERE   id = ?
			";

        $rs = $GLOBALS['_database']->Execute($get_person_query, array(
            $this->id
        ));
        if (!$rs) {
            $this->error = "SQL Error in Register::Person::details(): " . $GLOBALS['_database']->ErrorMsg();
            return null;
        }
        $customer = $rs->FetchNextObject(false);
        if (!isset($customer->id)) {
            app_log("No customer found for " . $this->id);
            $this->id = null;
            return $this;
        }

        app_log("Caching details for person '" . $this->id . "'", 'trace', __FILE__, __LINE__);
        # Store Some Object Vars
        if ($customer->id && $customer->id > 0) {
            $this->id = $customer->id;
            $this->first_name = $customer->first_name;
            $this->last_name = $customer->last_name;
            $this->code = $customer->code;
            $this->login = $customer->code;
            $customer->login = $customer->code;
            $this->organization = new Organization($customer->organization_id);
            $this->department_id = $customer->department_id;
            if (isset($customer->department)) $this->department = $customer->department;
            $this->status = $customer->status;
            $this->timezone = $customer->timezone;
            $this->auth_method = $customer->auth_method;
            if ($customer->automation == 0) $this->automation = false;
            else $this->automation = true;
            $this->_cached = 0;
        }
        else {
            $this->id = null;
            $this->first_name = null;
            $this->last_name = null;
            $this->code = null;
            $this->login = null;
            $customer->login = null;
            $this->organization = new Organization();
            $this->department_id = null;
            $this->status = null;
            $this->timezone = null;
            $this->auth_method = null;
            $this->automation = false;
            $this->_cached = 0;
        }

        # Cache Customer Object
        if ($customer->id) cache_set($cache_key, $customer);

        # Return Object
        return $this;
    }

    public function full_name() {
        $full_name = '';
        if (strlen($this->first_name)) $full_name .= $this->first_name;
        if (strlen($this->last_name)) {
            if (strlen($full_name)) $full_name .= " ";
            $full_name .= $this->last_name;
        }
        if (!strlen($full_name)) $full_name = $this->code;
        if (!strlen($full_name)) $full_name = '[empty]';
        return $full_name;
    }
    
    public function password_strength($string) {
        $password_strength = strlen($string);
        if (preg_match('/[A-Z]/', $string)) $password_strength += 1;
        if (preg_match('/[\@\$\_\-\.\!\&]/', $string)) $password_strength += 1;
        if (preg_match('/\d/', $string)) $password_strength += 1;
        if (preg_match('/[a-z]/', $string)) $password_strength += 1;
        return $password_strength;
    }
    
    public function add($parameters = array()) {
        if (!validLogin($parameters['login'])) {
            $this->error = "Invalid Login";
            return null;
        }
        if (! isset($GLOBALS['_config']->no_password) or !($GLOBALS['_config']->no_password)) {
            $password_length = strlen($parameters['password']);

            if (isset($GLOBALS['_config']->register->minimum_password_strength) && $this->password_strength($parameters['password']) < $GLOBALS['_config']->register->minimum_password_strength) {
                $this->error = "Password too weak.";
                return false;
            }
            if ($password_length > 100) {
                $this->error = "Password too long.";
                return false;
            }
        }

        # Defaults
        if (!isset($parameters['timezone'])) $parameters['timezone'] = 'America/New_York';
        if (!isset($parameters['status'])) $parameters['status'] = 'NEW';
        if (!isset($parameters['date_expires'])) $parameters['date_expires'] = '2038-01-01 00:00:00';
        if (!isset($parameters['validation_key'])) $parameters['validation_key'] = NULL;

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
					timezone,
					validation_key
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
					?
				)
			";

        $GLOBALS['_database']->Execute($add_user_query, array(
            $parameters['date_expires'],
            $parameters['status'],
            $parameters['login'],
            $parameters['password'],
            $parameters['timezone'],
            $parameters['validation_key']
        ));
        if ($GLOBALS['_database']->ErrorMsg()) {
            $this->error = "SQL Error in Register::Person::add(): Error: " . $GLOBALS['_database']->ErrorMsg() . " Query: " . preg_replace("/[\t\r\n]/", " ", $add_user_query);
            return false;
        }
        $this->id = $GLOBALS['_database']->Insert_ID();
        app_log("Added customer " . $parameters['login'] . " [" . $this->id . "]", 'debug', __FILE__, __LINE__);
        return $this->update($parameters);
    }

    public function update($parameters = array()) {
    
        if (!$this->id) {
            $this->error = "User ID Required for Update";
            return false;
        }

        # Loop through and apply changes
        $update_customer_query = "
				UPDATE	register_users
				SET		id = id
			";

        $bind_params = array();
        if (isset($parameters['first_name'])) {
            $update_customer_query .= ",
                first_name = ?";
            array_push($bind_params,$parameters['first_name']);
        }
        if (isset($parameters['last_name'])) {
            $update_customer_query .= ",
                last_name = ?";
            array_push($bind_params,$parameters['last_name']);
        }
        if (isset($parameters['login']) and !empty($parameters['login'])) {
		if (!validLogin($parameters['login'])) {
			$this->error = "Invalid login";
			return false;
		}
            $update_customer_query .= ",
                login = ?";
            array_push($bind_params,$parameters['login']);
        }
        if (isset($parameters['organization_id']) and ! empty($parameters['organization_id'])) {
            $update_customer_query .= ",
                organization_id = ?";
            array_push($bind_params,$parameters['organization_id']);
        }
        if (isset($parameters['status'])) {
            $update_customer_query .= ",
                status = ?";
            array_push($bind_params,$parameters['status']);
        }
        if (isset($parameters['timezone'])) {
            $update_customer_query .= ",
                timezone = ?";
            array_push($bind_params,$parameters['timezone']);
        }
        if (isset($parameters['password'])) {
            app_log("Changing password", 'notice', __FILE__, __LINE__);
            $update_customer_query .= ",
                `password` = password(?)";
            array_push($bind_params,$parameters['password']);
        }

        if (isset($parameters['validation_key'])) {
            $update_customer_query .= ",
                validation_key = ?";
            array_push($bind_params,$parameters['validation_key']);
        }

        if (isset($parameters['automation']) && is_bool($parameters['automation'])) {
            if ($parameters['automation']) {
                $update_customer_query .= ",
						automation = 1";
            }
            else {
                $update_customer_query .= ",
						automation = 0";
            }
        }

        $update_customer_query .= "
				WHERE	id = ?
			";
        array_push($bind_params,$this->id);
        $GLOBALS['_database']->Execute($update_customer_query,$bind_params);
        if ($GLOBALS['_database']->ErrorMsg()) {
            $this->error = "SQL Error in Register::Person::update(): " . $GLOBALS['_database']->ErrorMsg();
            return null;
        }

        # Bust Cache
        $cache_key = "customer[" . $this->id . "]";
        $cache = new \Cache\Item($GLOBALS['_CACHE_'], $cache_key);
        $cache->delete();

        # Get Updated Information
        return $this->details();
    }
    
    public function getMeta($id = 0) {
        if (!$id) $id = $this->id;
        $get_meta_query = "
				SELECT	`key`,value
				FROM	register_person_metadata
				WHERE	person_id = ?
			";
        $rs = $GLOBALS['_database']->Execute($get_meta_query, array(
            $id
        ));
        if (!$rs) {
            $this->error = "SQL Error in Register::Person::getMeta(): " . $GLOBALS['_database']->ErrorMsg();
            return null;
        }
        $metadata = array();
        while (list($label, $value) = $rs->FetchRow()) {
            $metadata[$label] = $value;
        }
        return $metadata;
    }
    
    public function setMeta($arg1, $arg2, $arg3 = 0) {
        if (func_num_args() == 3) {
            $id = $arg1;
            $key = $arg2;
            $value = $arg3;
        }
        else {
            $id = $this->id;
            $key = $arg1;
            $value = $arg2;
        }
        if (!$id) {
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
        $GLOBALS['_database']->Execute($add_meta_query, array(
            $id,
            $key,
            $value
        ));
        if ($GLOBALS['_database']->ErrorMsg()) {
            $this->error = "SQL Error in Register::Person::setMeta(): " . $GLOBALS['_database']->ErrorMsg();
            return null;
        }
        return 1;
    }
    
    public function searchMeta($key, $value = '') {
        $get_results_query = "
				SELECT	person_id
				FROM	register_person_metadata
				WHERE	`key` = " . $GLOBALS['_database']->qstr($key, get_magic_quotes_gpc());

        if ($value) $get_results_query .= "
				AND		value = " . $GLOBALS['_database']->qstr($value, get_magic_quotes_gpc());

        $rs = $GLOBALS['_database']->Execute($get_results_query);
        if (!$rs) {
            $this->error = "SQL Error in Register::Person::searchMeta(): " . $GLOBALS['_database']->ErrorMsg();
            return null;
        }
        $objects = array();
        while (list($id) = $rs->FetchRow()) {
            $object = $this->details($id);
            if ($object->status == 'DELETED') continue;
            array_push($objects, $object);
        }
        return $objects;
    }
    # Process Email Verification Request
    function verify_email($validation_key) {

        if (!$this->id) return false;
        if (!$validation_key) return false;

        $check_key_query = "
				SELECT	id,validation_key
				FROM	register_users
				WHERE	id = ?
			";
        $rs = $GLOBALS['_database']->Execute($check_key_query, array(
            $this->id
        ));
        if ($GLOBALS['_database']->ErrorMsg()) {
            $this->error = "SQL Error in Register::Person::verify_email(): ".$GLOBALS['_database']->ErrorMsg();
            return false;
        }
        list($id, $unverified_key) = $rs->fields;
        if (!$id) {
            app_log("Key doesn't match");
            $this->error = "Invalid Login or Validation Key";
            return false;
        }
        if (!$unverified_key) {
            app_log("No key in system to match");
            $this->error = "Email Address already verified for this account";
            return false;
        }
        if ($unverified_key != $validation_key) {
            app_log($unverified_key . " != " . $validation_key);
            $this->error = "Invalid Login or Validation Key";
            return false;
        }
        $validate_email_query = "
				UPDATE	register_users
				SET		validation_key = null
				WHERE	id = ?
			";
        $rs = $GLOBALS['_database']->Execute($validate_email_query, array(
            $this->id
        ));
        if ($GLOBALS['_database']->ErrorMsg()) {
            $this->error = "SQL Error in Register::Person::verify_email(): ".$GLOBALS['_database']->ErrorMsg();
            return false;
        }
        $this->id = $id;
        return $this->details();
    }
    
    public function addContact($parameters = array()) {
        $parameters['person_id'] = $this->id;
        $contact = new Contact();
        $contact->add($parameters);
        if ($contact->error) {
            $this->error = "Error adding contact: " . $contact->error;
            return null;
        }
        return $contact;
    }
    
    public function updateContact($id, $parameters = array()) {
        $contact = new Contact($id);
        return $contact->update($parameters);
    }
    
    public function deleteContact($id) {
        $contact = new Contact($id);
        return $contact->delete();
    }
    
    public function contacts($parameters = array()) {
        $contactlist = new ContactList();
        $parameters['person_id'] = $this->id;
        return $contactlist->find($parameters);
    }
    
    public function phone() {
        if (!$this->id) return new \Register\Contact();
        $contactlist = new ContactList();
        list($phone) = $contactlist->find(array(
            'person_id' => $this->id,
            'type' => 'phone'
        ));
        return $phone;
    }
    
    public function email() {
        if (!$this->id) return new \Register\Contact();
        $contactlist = new ContactList();
        list($email) = $contactlist->find(array(
            'person_id' => $this->id,
            'type' => 'email'
        ));
        return $email;
    }
    
    public function notifyContact($parameters = array()) {
        $contact = new Contact();
        return $contact->notify($parameters);
    }
    
    public function notify($message) {
        # Make Sure We have identifed a person
        if (!preg_match('/^\d+$/', $this->id)) {
            $this->error = "Customer not specified";
            return false;
        }
        # Get Contact Info
        $contactList = new \Register\ContactList();
        $contacts = $contactList->find(array(
            "user_id" => $this->id,
            "type" => "email",
            "notify" => true
        ));
        if ($contactList->error) {
            app_log("Error loading contacts: " . $contactList->error, 'error', __FILE__, __LINE__);
            $this->error = "Error loading contacts";
            return false;
        }
        foreach ($contacts as $contact) {
            app_log("Sending notifications to " . $contact->value, 'notice');
            $message->to($contact->value);
            $transport = \Email\Transport::Create(array(
                'provider' => $GLOBALS['_config']
                    ->email
                    ->provider
            ));
            if (\Email\Transport::error()) {
                $this->error = "Error initializing email transport: " . \Email\Transport::error();
                app_log("Message to " . $contact->value . " failed: " . $this->error, 'error');
                return false;
            }
            $transport->hostname($GLOBALS['_config']
                ->email
                ->hostname);
            $transport->token($GLOBALS['_config']
                ->email
                ->token);
            if ($transport->deliver($message)) {
                app_log("Message to " . $contact->value . " successful");
            }
            elseif ($transport->error()) {
                $this->error = "Error sending notification: " . $transport->error();
                app_log("Message to " . $contact->value . " failed: " . $this->error, 'error');
                return false;
            }
            else {
                $this->error = "Unhandled Error sending notification";
                app_log("Message to " . $contact->value . " failed", 'error');
                return false;
            }
        }
        return true;
    }
    
    public function delete() {
        app_log("Changing person " . $this->id . " to status DELETED", 'debug', __FILE__, __LINE__);

        # Bust Cache
        $cache_key = "customer[" . $this->id . "]";
        $cache_item = new \Cache\Item($GLOBALS['_CACHE_'], $cache_key);
        $cache_item->delete();

        $this->update($this->id, array(
            'status' => "DELETED"
        ));
    }
    
    public function parents() {
        $relationship = new \Register\Relationship();
        return $relationship->parents($this->id);
    }
    
    public function children() {
        $relationship = new \Register\Relationship();
        return $relationship->children($this->id);
    }
    
    public function locations() {
        $get_locations_query = "
				SELECT	location_id
				FROM	register_users_locations
				WHERE	user_id = ?";
        $rs = $GLOBALS['_database']->Execute($get_locations_query, array(
            $this->id
        ));
        if (!$rs) {
            $this->error = "SQL Error in Register::Person::locations: " . $GLOBALS['_database']->ErrorMsg();
            return null;
        }
        $locations = array();
        while (list($id) = $rs->FetchNextObject(false)) {
            $location = new \Register\Location($id);
            array_push($locations, $location);
        }
        return $locations;
    }

    public function human() {
        if ($this->automation) return false;
        return true;
    }

    public function automation() {
        if ($this->automation) return true;
        return false;
    }

	public function validLogin($login) {
		if (preg_match("/^[\w\-\_@\.\+\s]{2,100}\$/", $login)) return true;
		else return false;
	}

    public function error() {
        return $this->error;
    }
}
