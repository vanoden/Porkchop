<?php
namespace Register;

class Person Extends \BaseModel {
    public string $code = "";				// Alias for login
    public string $title = "";				// Title
    public string $first_name = "";			// First Name
    public string $middle_name = "";		// Middle Name
    public string $last_name = "";			// Last Name
    public ?int $organization_id = null;	// Organization ID
    public ?int $department_id = null;		// Department ID
    public string $message = "";
    public string $department;
    public string $status = "NEW";
    public bool $automation = false;
    public int $password_age = 0;
	public int $auth_failures = 0;
    public string $timezone = "America/New_York";
    public string $auth_method = "local";
    public int $time_based_password = 0;
	public bool $opt_in = false;
	public ?string $date_created = "";
	public ?string $date_updated = "";
	public ?string $date_expires = "";
	public ?string $unsubscribe_key = "";
	public ?string $validation_key = "";
	public ?string $custom_metadata = "";
	public ?string $notes = "";
	public ?int $default_billing_location_id = null;
	public ?int $default_shipping_location_id = null;
	public ?string $last_hit_date = "";
	public ?string $profile = "";
    protected string $secret_key = "";

    protected $_settings = array( "date_format" => "US" );
	protected $_database;

	/**
	 * Constructor
	 * @param mixed $id 
	 * @return void 
	 */
    public function __construct($id = 0) {
    	$this->_database = new \Database\Service();
		$this->_tableName = 'register_users';
		$this->_tableUKColumn = 'login';
		$this->_cacheKeyPrefix = 'customer';
		$this->_metaTableName = 'register_user_metadata';
		$this->_tableMetaFKColumn = 'user_id';
		$this->_tableMetaKeyColumn = 'key';
		$this->_auditEvents = true;
        $this->_aliasField('login','code');
		$this->_addStatus(array("NEW","ACTIVE","EXPIRED","HIDDEN","DELETED","BLOCKED"));

        // Find Person if id given
		parent::__construct($id);
    }

    public function setId($id=0) {
        $this->id = $id;
    }

	/** @method full_name
	 * Returns the first and last name concatenated, or just one if only one is set.
	 * If neither is set, returns the code or '[empty]' if code is also empty.
	 * @return string
	 */
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

	/** @method add(parameters)
	 * Adds a new user to the database.
	 * @param array $parameters
	 * @return bool
	 */
    public function add($parameters = []) {
        // Clear any previous errors
		$this->clearError();

        // Login Is Also Code
        if (!isset($parameters['login']) && isset($parameters['code'])) $parameters['login'] = $parameters['code'];

        // Validate Login
        if (!$this->validLogin($parameters['login'])) {
            $this->error("Invalid Login");
            return false;
        }

        // Defaults
        if (!isset($parameters['timezone'])) $parameters['timezone'] = 'America/New_York';
        if (!isset($parameters['status'])) $parameters['status'] = 'NEW';
        if (!isset($parameters['date_expires'])) $parameters['date_expires'] = '2038-01-01 00:00:00';
        if (!isset($parameters['validation_key'])) $parameters['validation_key'] = NULL;
        if (!isset($parameters['secret_key'])) $parameters['secret_key'] = '';

		sanitize($parameters['login']);

        // Initialize Database Service
        $database = new \Database\Service();

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
					validation_key,
					secret_key
				)
				VALUES
				(
					sysdate(),
					sysdate(),
					?,
					?,
					?,
					?,
					?,
					?
				)
		";

        // Bind Parameters
        $database->AddParam($parameters['date_expires']);
        $database->AddParam($parameters['status']);
        $database->AddParam($parameters['login']);
        $database->AddParam($parameters['timezone']);
        $database->AddParam($parameters['validation_key']);
        $database->AddParam($parameters['secret_key']);
        $database->Execute($add_user_query);
        if ($database->ErrorMsg()) {
            $this->SQLError($database->ErrorMsg());
            return false;
        }
        $this->id = $database->Insert_ID();

		// audit the add event
        $this->recordAuditEvent($this->id,'Added new person: ' . $parameters['login']);

        return $this->update($parameters);
    }

	/** @method update(parameters)
	 * Updates an existing user in the database.
	 * @param array $parameters
	 * @return bool
	*/
    public function update($parameters = []): bool {
		// Clear any previous errors
		$this->clearError();

        // Must have ID
        if (!$this->id) {
            $this->error("User ID Required for Update");
            return false;
        }

        // Initialize Database Service
        $database = new \Database\Service();

        // Prepare Query
        $update_customer_query = "
				UPDATE	register_users
				SET		id = id
			";

		$audit_messages = [];

		if (!empty($parameters['first_name']) && $parameters['first_name'] != $this->first_name) {
			if (! preg_match('/^[\w\-\.\_\s]*$/',$parameters['first_name'])) {
				$this->error("Invalid name");
				return false;
			}
			$update_customer_query .= ",
			first_name = ?";
			$database->AddParam($parameters['first_name']);
			$audit_messages[] = "First Name changed to ".$parameters['first_name'];
		}
		if (isset($parameters['last_name']) && $parameters['last_name'] != $this->last_name) {
			if (! preg_match('/^[\w\-\.\_\s]*$/',$parameters['last_name'])) {
				$this->error("Invalid name");
				return false;
			}
			$update_customer_query .= ",
			last_name = ?";
			$database->AddParam($parameters['last_name']);
			$audit_messages[] = "Last Name changed to ".$parameters['last_name'];
		}
		if (isset($parameters['login']) and !empty($parameters['login']) && $parameters['login'] != $this->code) {
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
            $database->AddParam($parameters['login']);
			$audit_messages[] = "Login changed to ".$parameters['login'];
		}
		if (isset($parameters['organization_id']) and ! empty($parameters['organization_id']) && $parameters['organization_id'] != $this->organization_id) {
			$update_customer_query .= ",
			organization_id = ?";
			$database->AddParam($parameters['organization_id']);
			$audit_messages[] = "Organization changed to ".$parameters['organization_id'];
		}
		if (isset($parameters['auth_failures']) and is_numeric($parameters['auth_failures']) && $parameters['auth_failures'] != $this->auth_failures) {
			$update_customer_query .= ",
			auth_failures = ?";
			$database->AddParam($parameters['auth_failures']);
			$audit_messages[] = "Auth Failures changed to ".$parameters['auth_failures'];
		}
		if (isset($parameters['status']) && $parameters['status'] != $this->status) {
			$update_customer_query .= ",
			status = ?";
			$database->AddParam($parameters['status']);
			$audit_messages[] = "Status changed to ".$parameters['status'];
		}
		if (isset($parameters['timezone']) && $parameters['timezone'] != $this->timezone) {
			if (! in_array($parameters['timezone'], \DateTimeZone::listIdentifiers())) {
				$this->error("Invalid timezone");
				return false;
			}
			$update_customer_query .= ",
			timezone = ?";
			$database->AddParam($parameters['timezone']);
			$audit_messages[] = "Timezone changed to ".$parameters['timezone'];
		}

		if (isset($parameters['validation_key'])) {
			$update_customer_query .= ",
			validation_key = ?";
			$database->AddParam($parameters['validation_key']);
			$audit_messages[] = "Validation Key changed";
		}

		if (isset($parameters['profile']) && $parameters['profile'] != $this->profile) {
			$update_customer_query .= ",
			profile = ?";
			$database->AddParam($parameters['profile']);
			$audit_messages[] = "Profile changed to ".$parameters['profile'];
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
			$audit_messages[] = "Automation changed to ".$parameters['automation'];
        }

        if (isset($parameters['time_based_password']) && $parameters['time_based_password'] != $this->time_based_password) {
            if ($parameters['time_based_password']) {
                $update_customer_query .= ",
                time_based_password = 1";
            } else {
                $update_customer_query .= ",
                time_based_password = 0";
            }
            $audit_messages[] = "Time Based Password changed to ".$parameters['time_based_password'];
        }

		if (isset($parameters['secret_key']) && $parameters['secret_key'] != $this->secret_key) {
			$update_customer_query .= ",
			secret_key = ?";
            $database->AddParam($parameters['secret_key']);
            $audit_messages[] = "Secret Key changed";
		}

        if (empty($audit_messages)) {
            app_log("No changes to update for user ".$this->id,'debug');
            return true;
        }

		$update_customer_query .= "
			WHERE	id = ?
		";

		$database->AddParam($this->id);

        // Execute Query
        $database->Execute($update_customer_query);

        // Check for Errors
        if ($database->ErrorMsg()) {
            $this->SQLError($database->ErrorMsg());
            return false;
        }

        // Bust Cache
        $cache_key = "customer[" . $this->id . "]";
        $cache = new \Cache\Item($GLOBALS['_CACHE_'], $cache_key);
        $cache->delete();

        // audit the update event
		$this->recordAuditEvent($this->id, implode("; ", $audit_messages));

        // Get Updated Information
        return $this->details();
    }

	/** @method organization()
	 * Returns the Organization object associated with this person.
	 * @return \Register\Organization
	 */
    public function organization() {
        return new \Register\Organization($this->organization_id);
    }

    /** @method verify_email(key)
	 * Process Email Verification Request
	 * @param string $validation_key
	 * @return bool|\Register\Person
	 */
    function verify_email($validation_key) {
        // Clear any previous errors
        $this->clearError();

        // Must have ID and Key
        if (!$this->id) return false;
        if (!$validation_key) return false;

        // Initialize Database Service
        $database = new \Database\Service();

        // Prepare Database Query
        $check_key_query = "
				SELECT	id,validation_key
				FROM	register_users
				WHERE	id = ?
			";

        // Bind Parameters
        $database->AddParam($this->id);
        $rs = $database->Execute($check_key_query);
        if ($database->ErrorMsg()) {
            $this->SQLError($database->ErrorMsg());
            return false;
        }
        list($id, $unverified_key) = $rs->FetchRow();
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
        $database->resetParams();
        $database->AddParam($this->id);
        $rs = $database->Execute($validate_email_query);
        if ($database->ErrorMsg()) {
            $this->SQLError($database->ErrorMsg());
            return false;
        }
        $this->id = $id;
        return $this->details();
    }

    /** @method public addContact(parameters)
     * Add a contact method for this person
     * @param array $parameters Contact parameters
     * @return \Register\Contact|null The created contact or null on error
     */
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

    /** @method public updateContact(parameters)
     * Update a contact method for this person
     * @param int $id Contact ID
     * @param array $parameters Contact parameters
     * @return bool True on success, false on error
     */
    public function updateContact($id, $parameters = array()) {
        $contact = new Contact($id);
        return $contact->update($parameters);
    }

    /** @method public deleteContact(id)
     * Delete a contact method for this person
     * @param int $id Contact ID
     * @return bool True on success, false on error
     */
    public function deleteContact($id) {
        $contact = new Contact($id);
        return $contact->delete();
    }

    /** @method public contacts(parameters)
     * Get contact methods for this person
     * @param array $parameters Contact search parameters
     * @return array Array of \Register\Contact objects
     */
    public function contacts($parameters = array()) {
        $contactlist = new ContactList();
        $parameters['person_id'] = $this->id;
        return $contactlist->find($parameters);
    }

    /** @method public phone()
     * Get primary phone contact for this person
     * @return \Register\Contact|null The phone contact or null if not found
     */
    public function phone() {
        if (!$this->id) return new \Register\Contact();
        $contactlist = new ContactList();
        $found = $contactlist->find(array(
            'person_id' => $this->id,
            'type' => 'phone'
        ));
        if (!empty($found)) {
            list($phone) = $found;
            return $phone;
        } else {
            return null;
        }
    }

    /** @method public email()
     * Get primary email contact for this person
     * @return \Register\Contact|null The email contact or null if not found
     */
    public function email() {
        if (!$this->id) return new \Register\Contact();
        $contactlist = new ContactList();
        $found = $contactlist->find(array(
            'person_id' => $this->id,
            'type' => 'email'
        ));
        if (!empty($found)) {
            list($email) = $found;
            return $email;
        } else {
            return null;
        }
    }

    /** @method notify(message)
     * Send a notification message to all contacts marked for notification
     * @param \Email\Message $message The message to send
     * @return bool True on success, false on error
     */
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

    /** @method block()
     * Block this person from logging in
     * @return bool True on success, false on error
     */
	public function block() {
		app_log("Blocking customer '".$this->code."'",'INFO');
		return $this->update(array('status' => 'BLOCKED'));
	}

    /** @method delete()
     * Mark this person as deleted
     * @return bool True on success, false on error
     */
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

    /** @method public parents()
     * Get parent persons for this person
     * @return array Array of \Register\Person objects
     */
    public function parents() {
        $relationship = new \Register\Relationship();
        return $relationship->parents($this->id);
    }

    /** @method public children()
     * Get child persons for this person
     * @return array Array of \Register\Person objects
     */
    public function children() {
        $relationship = new \Register\Relationship();
        return $relationship->children($this->id);
    }

    /** @method public locations()
     * Get locations associated with this person
     * @return array Array of \Register\Location objects
     */
    public function locations() {
        // Clear any previous errors
        $this->clearError();

        // Initialize Database Service
        $database = new \Database\Service();

        // Prepare Query
        $get_locations_query = "
				SELECT	location_id
				FROM	register_users_locations
				WHERE	user_id = ?";

        // Bind Parameters
        $database->AddParam($this->id);

        // Execute Query
        $rs = $database->Execute($get_locations_query);
        if (!$rs) {
            $this->SQLError($database->ErrorMsg());
            return null;
        }
        $locations = array();
        while (list($id) = $rs->FetchNextObject(false)) {
            $location = new \Register\Location($id);
            array_push($locations, $location);
        }
        return $locations;
    }

    /** @method public human()
     * Check if this person is a human (not automation)
     * @return bool True if human
     */
    public function human() {
        if ($this->automation) return false;
        return true;
    }

    /** @method public automation()
     * Check if this person is automation
     * @return bool True if automation
     */
	public function automation() {
		if ($this->automation) return true;
		return false;
	}

    /** @method public validLogin(login)
     * Validate a login name
     * @param string $login The login name to validate
     * @return bool True if valid login name
     */
	public function validLogin($login) {
		if (preg_match("/^[\w\-\_@\.\+\s]{2,100}\$/", $login)) return true;
		else return false;
	}

    /** @method public validFirstName(string)
     * Validate a first name
     * @param string $string The first name to validate
     * @return bool True if valid first name
     */
    public function validFirstName($string) {
        if (preg_match("/^[\w\-\.\_\s]{1,100}\$/", $string)) return true;
        else return false;
    }

    /** @method public validLastName(string)
     * Validate a last name
     * @param string $string The last name to validate
     * @return bool True if valid last name
     */
    public function validLastName($string) {
        return $this->validFirstName($string);
    }

    /** @method public settings(key)
     * Get a setting value for this person
     * @param string $key The setting key
     * @return mixed The setting value or null if not found
     */
	public function settings($key) {
	
		// Only Show If metadata key is in _settings array
		if (! isset($this->_settings[$key])) return null;
		
		// We will add a metadata search here, if no matching metadata, return default
		return $this->_settings[$key];
	}

    /** @method public password_expired()
     * Check if this person's password has expired
     * @return bool True if password has expired
     */
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

    /** @method public abbrev_name()
     * Get the abbreviated name for this person
     * @return string The abbreviated name
     */
	public function abbrev_name() {
		return substr($this->first_name,0,1)." ".$this->last_name;
	}

    /** @method public initials()
     * Get the initials for this person
     * @return string The initials
     */
	public function initials() {
		return substr($this->first_name,0,1).substr($this->last_name,0,1);
	}

    /** @method public icon()
     * Get the icon object for this person
     * @return \Register\PersonIcon The icon object
     */
	public function icon() {
		return new \Register\PersonIcon($this->id);
	}

    /** @method otp_secret_key()
     * Returns the OTP secret key for the person
     * @return string
     */
	public function otp_secret_key() {
		return $this->secret_key;
	}

	/** @method login()
	 * Returns the login (code) for the person
	 * @return string
	 */
	public function login() {
		return $this->code;
	}
}
