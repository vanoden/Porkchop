<?php
namespace Register;

class Person Extends \BaseModel {
    public string $code = "";
    public string $title = "";
    public string $first_name = "";
    public string $middle_name = "";
    public string $last_name = "";
    public $location;
    public int $organization_id;
    public int $department_id;
    public string $login = "";
    public string $message = "";
    public string $department;
    public string $status = "NEW";
    public bool $automation = false;
    public int $password_age = 0;
	public int $auth_failures = 0;
    public string $timezone = "America/New_York";
    public string $auth_method = "local";
    public int $time_based_password = 0;
    protected $_settings = array( "date_format" => "US" );
	protected $_database;

    public function __construct($id = null) {
    	$this->_database = new \Database\Service();
		$this->_tableName = 'register_users';
		$this->_tableUKColumn = 'login';
		$this->_cacheKeyPrefix = 'customer';
		$this->_auditEvents = true;
        $this->_aliasField('login','code');

        // Clear Error Info
        $this->clearError();

		$this->_addStatus(array("NEW","ACTIVE","EXPIRED","HIDDEN","DELETED","BLOCKED"));

        // Find Person if id given
		parent::__construct($id);
    }

    public function setId($id=0) {
        $this->id = $id;
    }

	public function full_name() {
		$full_name = '';
		if (strlen($this->first_name)) $full_name .= $this->first_name;
		if (strlen($this->last_name)) {
			if (strlen($full_name)) $full_name .= " ";
			$full_name .= $this->last_name;
		}
		if (!strlen($full_name)) $full_name = $this->login;
		if (!strlen($full_name)) $full_name = '[empty]';
		return $full_name;
	}
    
    public function add($parameters = []) {

		$this->clearError();

        if (!isset($parameters['login']) && isset($parameters['code'])) $parameters['login'] = $parameters['code'];

        if (!$this->validLogin($parameters['login'])) {
            $this->error("Invalid Login");
            return false;
        }

        // Defaults
        if (!isset($parameters['timezone'])) $parameters['timezone'] = 'America/New_York';
        if (!isset($parameters['status'])) $parameters['status'] = 'NEW';
        if (!isset($parameters['date_expires'])) $parameters['date_expires'] = '2038-01-01 00:00:00';
        if (!isset($parameters['validation_key'])) $parameters['validation_key'] = NULL;

		sanitize($parameters['login']);

        // Add to Database
        $add_user_query = "
				INSERT
				INTO	register_users
				(
					date_created,
					date_updated,
					date_expires,
					status,
					login,
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
					?,
					?
				)
		";

        $GLOBALS['_database']->Execute($add_user_query, array(
            $parameters['date_expires'],
            $parameters['status'],
            $parameters['login'],
            $parameters['timezone'],
            $parameters['validation_key']
        ));
        if ($GLOBALS['_database']->ErrorMsg()) {
            $this->SQLError($GLOBALS['_database']->ErrorMsg());
            return false;
        }
        $this->id = $GLOBALS['_database']->Insert_ID();

		// audit the add event
		$auditLog = new \Site\AuditLog\Event();
		$auditLog->add(array(
			'instance_id' => $this->id,
			'description' => 'Added new '.$this->_objectName(),
			'class_name' => get_class($this),
			'class_method' => 'add'
		));

        app_log("Added customer " . $parameters['login'] . " [" . $this->id . "]", 'debug', __FILE__, __LINE__);
        return $this->update($parameters);
    }

    public function update($parameters = []): bool {

        if (!$this->id) {
            $this->error("User ID Required for Update");
            return false;
        }

        $auditEvent = new \Site\AuditLog\Event();

        // Loop through and apply changes
        $update_customer_query = "
				UPDATE	register_users
				SET		id = id
			";

		$bind_params = array();
		if (isset($parameters['first_name']) && $parameters['first_name'] != $this->first_name) {
			if (! preg_match('/^[\w\-\.\_\s]*$/',$parameters['first_name'])) {
				$this->error("Invalid name");
				return false;
			}
			$update_customer_query .= ",
			first_name = ?";
			array_push($bind_params,$parameters['first_name']);
			$auditEvent->appendDescription("First Name changed to ".$parameters['first_name']);
		}
		if (isset($parameters['last_name']) && $parameters['last_name'] != $this->last_name) {
			if (! preg_match('/^[\w\-\.\_\s]*$/',$parameters['last_name'])) {
				$this->error("Invalid name");
				return false;
			}
			$update_customer_query .= ",
			last_name = ?";
			array_push($bind_params,$parameters['last_name']);
			$auditEvent->appendDescription("Last Name changed to ".$parameters['last_name']);
		}
		if (isset($parameters['login']) and !empty($parameters['login']) && $parameters['login'] != $this->login) {
			if (!$this->validLogin($parameters['login'])) {
				$this->error("Invalid login");
				return false;
			}
			// Make Sure Login not Taken
			$check = new \Register\Person();
			if ($check->get($parameters['login'])) {
				$this->error("Login already in use");
				return false;
			}
			$update_customer_query .= ",
			login = ?";
			array_push($bind_params,$parameters['login']);
			$auditEvent->appendDescription("Login changed to ".$parameters['login']);
		}
		if (isset($parameters['organization_id']) and ! empty($parameters['organization_id']) && $parameters['organization_id'] != $this->organization_id) {
			$update_customer_query .= ",
			organization_id = ?";
			array_push($bind_params,$parameters['organization_id']);
			$auditEvent->appendDescription("Organization changed to ".$parameters['organization_id']);
		}
		if (isset($parameters['auth_failures']) and is_numeric($parameters['auth_failures']) && $parameters['auth_failures'] != $this->auth_failures) {
			$update_customer_query .= ",
			auth_failures = ?";
			array_push($bind_params,$parameters['auth_failures']);
			$auditEvent->appendDescription("Auth Failures changed to ".$parameters['auth_failures']);
		}
		if (isset($parameters['status']) && $parameters['status'] != $this->status) {
			$update_customer_query .= ",
			status = ?";
			array_push($bind_params,$parameters['status']);
			$auditEvent->appendDescription("Status changed to ".$parameters['status']);
		}
		if (isset($parameters['timezone']) && $parameters['timezone'] != $this->timezone) {
			if (! in_array($parameters['timezone'], \DateTimeZone::listIdentifiers())) {
				$this->error("Invalid timezone");
				return false;
			}
			$update_customer_query .= ",
			timezone = ?";
			array_push($bind_params,$parameters['timezone']);
			$auditEvent->appendDescription("Timezone changed to ".$parameters['timezone']);
		}

		if (isset($parameters['validation_key'])) {
			$update_customer_query .= ",
			validation_key = ?";
			array_push($bind_params,$parameters['validation_key']);
			$auditEvent->appendDescription("Validation Key changed");
		}

        if (isset($parameters['automation']) && is_bool($parameters['automation']) && $parameters['automation'] != $this->automation) {
            if ($parameters['automation']) {
                $update_customer_query .= ",
						automation = 1";
            }
            else {
                $update_customer_query .= ",
						automation = 0";
            }
			$auditEvent->appendDescription("Automation changed to ".$parameters['automation']);
        }

        if (isset($parameters['time_based_password']) && is_bool($parameters['time_based_password']) && $parameters['time_based_password'] != $this->time_based_password) {
            if ($parameters['time_based_password']) {
                $update_customer_query .= ",
                time_based_password = 1";
            } else {
                $update_customer_query .= ",
                time_based_password = 0";
            }
        }

		$update_customer_query .= "
			WHERE	id = ?
		";

		array_push($bind_params,$this->id);
        $GLOBALS['_database']->Execute($update_customer_query,$bind_params);
        if ($GLOBALS['_database']->ErrorMsg()) {
            $this->SQLError($GLOBALS['_database']->ErrorMsg());
            return null;
        }

        // Bust Cache
        $cache_key = "customer[" . $this->id . "]";
        $cache = new \Cache\Item($GLOBALS['_CACHE_'], $cache_key);
        $cache->delete();

        // audit the update event
		app_log("Log customer updates?");
        $auditEvent->addIfDescription(array(
            'instance_id' => $this->id,
            'class_name' => get_class($this),
            'class_method' => 'update'
        ));	

        // Get Updated Information
        return $this->details();
    }

    public function organization() {
        return new \Register\Organization($this->organization_id);
    }
    
	public function getMeta($id = 0) {
		if (!$id) $id = $this->id;
		$get_meta_query = "
			SELECT	`key`,value
			FROM	register_person_metadata
			WHERE	person_id = ?
		";
		$rs = $GLOBALS['_database']->Execute($get_meta_query, array($id));
		if (!$rs) {
			$this->SQLError($GLOBALS['_database']->ErrorMsg());
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
            $this->error("No person_id for metadata");
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
            $this->SQLError($GLOBALS['_database']->ErrorMsg());
            return null;
        }
        return 1;
    }
    
    public function metadata($key) {
		$bind_params = array();

        $get_results_query = "
				SELECT	value
				FROM	register_person_metadata
				WHERE	`person_id` = ?
				AND	`key` = ?";

		array_push($bind_params,$this->id);
		array_push($bind_params,$key);

        $rs = $GLOBALS['_database']->Execute($get_results_query,$bind_params);
        if (!$rs) {
            $this->SQLError($GLOBALS['_database']->ErrorMsg());
            return null;
        }
        list($value) = $rs->FetchRow();
		return htmlspecialchars($value);
    }
 
    public function searchMeta($key, $value = '') {
		$bind_params = array();
		$get_results_query = "
			SELECT	person_id
			FROM	register_person_metadata
			WHERE	`key` = ?";
		array_push($bind_params,$key);

		if ($value) {
			$get_results_query .= "
				AND		value = ?";
			array_push($bind_params,$value);
		}

		$rs = $GLOBALS['_database']->Execute($get_results_query,$bind_params);
		if (!$rs) {
			$this->SQLError($GLOBALS['_database']->ErrorMsg());
			return null;
		}
		$objects = array();
		while (list($id) = $rs->FetchRow()) {
            $person = new Person($id);
			if ($person->status == 'DELETED') continue;
			array_push($objects, $person);
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
            $this->SQLError($GLOBALS['_database']->ErrorMsg());
            return false;
        }
        list($id, $unverified_key) = $rs->fields;
        if (!$id) {
            app_log("Key doesn't match");
            $this->error("Invalid Login or Validation Key");
            return false;
        }
        if (!$unverified_key) {
            app_log("No key in system to match");
            $this->error("Email Address already verified for this account");
            return false;
        }
        if ($unverified_key != $validation_key) {
            app_log($unverified_key . " != " . $validation_key);
            $this->error("Invalid Login or Validation Key");
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
            $this->SQLError($GLOBALS['_database']->ErrorMsg());
            return false;
        }
        $this->id = $id;
        return $this->details();
    }
    
    public function addContact($parameters = array()) {
        $parameters['person_id'] = $this->id;
        $contact = new Contact();
        $contact->add($parameters);
        if ($contact->error()) {
            $this->error("Error adding contact: " . $contact->error());
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
    
    public function notify($message) {
        // Make Sure We have identifed a person
        if (!preg_match('/^\d+$/', $this->id)) {
            $this->error("Customer not specified");
            return false;
        }
        
        // Get Contact Info
        $contactList = new \Register\ContactList();
        $contacts = $contactList->find(array(
            "user_id" => $this->id,
            "type" => "email",
            "notify" => true
        ));
        
        if ($contactList->error()) {
            app_log("Error loading contacts: " . $contactList->error(), 'error', __FILE__, __LINE__);
            $this->error("Error loading contacts");
            return false;
        }
        foreach ($contacts as $contact) {
            app_log("Sending notifications to " . $contact->value, 'notice');
            $message->to($contact->value);
			$transportFactory = new \Email\Transport();
            $transport = $transportFactory->Create(array(
                'provider' => $GLOBALS['_config']->email->provider
            ));
            if (! isset($transport)) {
                $this->error("Error initializing email transport");
                app_log("Message to " . $contact->value . " failed: " . $this->error(), 'error');
                return false;
            }
            $transport->hostname($GLOBALS['_config']->email->hostname);
            $transport->token($GLOBALS['_config']->email->token);
            if ($transport->deliver($message)) {
                app_log("Message to " . $contact->value . " successful");
            } elseif ($transport->error()) {
                $this->error("Error sending notification: " . $transport->error());
                app_log("Message to " . $contact->value . " failed: " . $this->error(), 'error');
                return false;
            } else {
                $this->error("Unhandled Error sending notification");
                app_log("Message to " . $contact->value . " failed", 'error');
                return false;
            }
        }
        return true;
    }

	public function block() {
		app_log("Blocking customer '".$this->login."'",'INFO');
		return $this->update(array('status' => 'BLOCKED'));
	}

    public function delete(): bool {
        app_log("Changing person " . $this->id . " to status DELETED", 'debug', __FILE__, __LINE__);

        # Bust Cache
        $cache_key = "customer[" . $this->id . "]";
        $cache_item = new \Cache\Item($GLOBALS['_CACHE_'], $cache_key);
        $cache_item->delete();

        $this->update($this->id, array(
            'status' => "DELETED"
        ));
		return true;
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
            $this->SQLError($GLOBALS['_database']->ErrorMsg());
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

	public function settings($key) {
	
		// Only Show If metadata key is in _settings array
		if (! isset($this->_settings[$key])) return null;
		
		// We will add a metadata search here, if no matching metadata, return default
		return $this->_settings[$key];
	}

	public function password_expired() {
		if (isset($this->organization()->password_expiration_days) && !empty($this->organization()->password_expiration_days)) {
			$passwordAllowedAgeSeconds = $this->organization()->password_expiration_days * 86400;
			$passwordAgeSeconds = time() - $this->password_age;
			if ($passwordAgeSeconds < $passwordAllowedAgeSeconds) {
				return false;
			} else {
				app_log("Password expired: $passwordAgeSeconds >= $passwordAllowedAgeSeconds",'info');
				return true;
			}
		}
		return false;
	}

	public function abbrev_name() {
		return substr($this->first_name,0,1)." ".$this->last_name;
	}

	public function initials() {
		return substr($this->first_name,0,1).substr($this->last_name,0,1);
	}

	public function icon() {
		return new \Register\PersonIcon($this->id);
	}
}
