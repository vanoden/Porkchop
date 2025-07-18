<?php
	namespace Register;

    class Customer extends Person {
		public bool $elevated = false;
		public int $unreadMessages = 0;
		protected string $password = '';

		/**
		 * Constructor
		 * @param int $id 
		 * @return void 
		 */
		public function __construct($id = 0) {
			parent::__construct($id);
		}

		/**
		 * Create a Customer Record
		 * @param array $parameters 
		 * @return bool 
		 */
		public function add($parameters = []) {
			if (parent::add($parameters)) {
				$this->auditRecord('REGISTRATION_SUBMITTED','Customer added', $this->id);
				$this->changePassword($parameters['password']);
				return true;
			}
			else return false;
		}

		/** @method update(parameters)
		 * Update a customer record
		 * @param array $parameters 
		 * @return bool 
		 */
		public function update($parameters = []): bool {

            // Password must be changed per Authentication Service
			if (isset($parameters['password'])) {

				app_log("Updating password for customer ".$this->id, 'debug', __FILE__, __LINE__);

                // Authentication Service needs login to change password
                if (empty($this->code)) $this->code = $parameters["login"];
                if (empty($this->code)) {
                    $this->error("Login required to change password");
                    return false;
                }
				if ($this->changePassword($parameters['password'])) {
					unset($parameters['password']);
				}
				else {
					return false;
				}
			}

			if ($_SERVER["SCRIPT_FILENAME"] == BASE."/core/install.php") app_log("Installer updating new admin account",'info');
			else {
				if (!empty($parameters['organization_id']) && $this->organization_id != $parameters['organization_id']) $this->auditRecord("ORGANIZATION_CHANGED","Organization changed from ".$this->organization()->name." to ".$parameters['organization_id']);
				if (!empty($parameters['status']) && $this->status != $parameters['status']) $this->auditRecord("STATUS_CHANGED","Status changed from ".$this->status." to ".$parameters['status']);
				if (!empty($parameters['first_name']) && $this->first_name != $parameters['first_name'] || !empty($parameters['last_name']) && $this->last_name != $parameters['last_name'])  $this->auditRecord("USER_UPDATED","Customer Name changed from " . $this->first_name . " " . $this->last_name . " to " . $parameters['first_name'] . " " . $parameters['last_name']);
				if (isset($parameters['profile_visibility']) && $this->profile != $parameters['profile_visibility']) $this->auditRecord("PROFILE_VISIBILITY_CHANGED","Profile visibility changed from ".$this->profile." to ".$parameters['profile_visibility']);
			}

			parent::update($parameters);
			if ($this->error()) return false;

			$auditLog = new \Site\AuditLog\Event();

			// roles
			if (isset($GLOBALS['_SESSION_']->customer) && $GLOBALS['_SESSION_']->customer->can('manage customers')) {
				$rolelist = new RoleList();
				$roles = $rolelist->find();
				foreach ($roles as $role) {
					if (isset($parameters['roles']) && is_array($parameters['roles'])) {
						if (array_key_exists($role['id'],$parameters['roles'])) {
							$auditLog->appendDescription("Added role ".$role['name']);
							$this->add_role($role['id']);
						} else {
							$auditLog->appendDescription("Added role ".$role['name']);
							$this->drop_role($role['id']);
						}
					}
				}
			}
			
			// audit the update event
			app_log("Well, log it already!");
			$auditLog->addIfDescription(array(
				'instance_id' => $this->id,
				'class_name' => get_class($this),
				'class_method' => 'update'
			));

			return $this->details();
		}

		/** @method recordHit()
		 * Record last hit date
		 */
		public function recordHit() {
			$this->clearError();
			$database = new \Database\Service();

			// Prepare Query
			$update_customer_query = "
				UPDATE	register_users
				SET		last_hit_date = now()
				WHERE	id = ?
			";
			$database->AddParam($this->id);
			$database->Execute($update_customer_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
			}
		}

		/** @method clearAuthFailures()
		 * Clear Auth Failures
		 */
		public function clearAuthFailures() {
			$this->clearError();
			$this->clearCache();

			$database = new \Database\Service();
			$update_customer_query = "
				UPDATE	register_users
				SET		auth_failures = 0
				WHERE	id = ?
			";
			$database->AddParam($this->id);
			$database->Execute($update_customer_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
			}
		}

		/** @method organization(organization)
		 * Get/Set Organization
		 * @param \Register\Organization|Organization ID|null $organization
		 * @return \Register\Organization|null
		 */
		public function organization($organization = null): ?\Register\Organization {
			if (!empty($organization)) {
				if (is_numeric($organization)) {
					$this->organization_id = $organization;
					return new \Register\Organization($organization);
				}
				elseif (is_object($organization)) {
					$this->organization_id = $organization->id;
					return $organization;
				}
			}
			if ($this->organization_id) {
				$organization = new \Register\Organization($this->organization_id);
				return $organization;
			}
			return null;
		}

		/** @method increment_auth_failures()
		 * Count auth failures
		 */
		public function increment_auth_failures() {
			// Clear Errors
			$this->clearError();

			// Check if ID is set
			if (! isset($this->id)) return false;

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query
			$update_customer_query = "
				UPDATE	register_users
				SET		auth_failures = auth_failures + 1
				WHERE	id = ?";

			// Add Parameters
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
			return $this->details();
		}

		/** @method add_role(role_id)
		 * Add a Role to the Customer
		 * @param string $role_id The ID of the role to add
		 */
		function add_role ($role_id) {
		
			if ($GLOBALS['_SESSION_']->elevated()) {
				app_log("Elevated Session adding role");
			}
			elseif ($GLOBALS['_SESSION_']->customer->can('manage customers')) {
				app_log("Granting role '$role_id' to customer '".$this->id."'",'info',__FILE__,__LINE__);
			}
			else {
				app_log("Non admin failed to update roles",'notice',__FILE__,__LINE__);
				$this->error("Insufficient Privileges");
				return 0;
			}
			
			$add_role_query = "
				INSERT
				INTO	register_users_roles
				(		user_id,role_id)
				VALUES
				(		?,
						?
				)
				ON DUPLICATE KEY UPDATE user_id = user_id
			";

			$GLOBALS['_database']->Execute(
				$add_role_query,
				array($this->id,$role_id)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return 0;
			}

			# Bust Cache
			$cache_key = "customer[" . $this->id . "]";
			$cache = new \Cache\Item($GLOBALS['_CACHE_'], $cache_key);
			$cache->delete();

			$this->auditRecord('ROLE_ADDED','Role has been added: '.$role_id);
			return 1;
		}

		/** @method drop_role(role_id)
		 * Remove a Role from the Customer
		 * @param string $role_id The ID of the role to remove
		 */
		function drop_role($role_id) {
			$drop_role_query = "
				DELETE
				FROM	register_users_roles
				WHERE	user_id = ?
				AND		role_id = ?
			";
			
			//error_log("Update Customer: $drop_role_query");
			$GLOBALS['_database']->Execute(
				$drop_role_query,
				array($this->id,$role_id)
			);
			
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			$this->auditRecord('ROLE_DROPPED','Role '.$role_id.' dropped');
			return true;
		}

		/** @method isActive()
		 * Check if the customer is active
		 * @return bool True if active, otherwise false
		 */
		function isActive() {
			if (in_array($this->status,array('NEW','ACTIVE'))) return true;
			return false;
		}

		/** @method isBlocked()
		 * Check if the customer is blocked
		 * @return bool True if blocked, otherwise false
		 */
		function isBlocked() {
			if (is_string($this->status) && $this->status == "BLOCKED") return true;
			return false;
		}

		/** @method authenticate(login, password)
		 * Check login and password against configured authentication mechanism
		 * @param string $login
		 * @param string $password
		 * @return bool
		 */
		function authenticate ($login, $password): bool {
			$this->clearError();

			// Validate Input
			if (! $this->validLogin($login)) {
				$failure = new \Register\AuthFailure();
				$failure->add(array($_SERVER['REMOTE_ADDR'],$login,'INVALIDLOGIN',$_SERVER['PHP_SELF']));
				return false;
			}

			// Initialize Database Service
			$database = new \Database\Service();

			// Get Authentication Method
			$get_user_query = "
				SELECT	id,auth_method,status,password_age
				FROM	register_users
				WHERE	login = ?
			";

			// Add Parameters
			$database->AddParam($login);

			// Execute Query
			$rs = $database->Execute($get_user_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			list($id,$auth_method,$status,$password_age) = $rs->fields();
			if (! $id) {
				app_log("Auth denied because no account found matching '$login'",'notice',__FILE__,__LINE__);
				$failure = new \Register\AuthFailure();
				$failure->add(array($_SERVER['REMOTE_ADDR'],$login,'NOACCOUNT',$_SERVER['PHP_SELF']));
				return false;
			}
			if (!empty($auth_method)) $this->auth_method = $auth_method;

			// check if they have an expired password for organzation rules
			$this->get($login);		
			if ($this->password_expired()) {
				$this->error("Your password is expired.  Please use Recover Password to restore.");
				$failure = new \Register\AuthFailure();
				$failure->add(array($_SERVER['REMOTE_ADDR'],$login,'PASSEXPIRED',$_SERVER['PHP_SELF']));
				$this->auditRecord("AUTHENTICATION_FAILURE","Password expired");
				return false;
			}

			// Load Specified Authentication Service
			$authenticationFactory = new \Register\AuthenticationService\Factory();
			$authenticationService = $authenticationFactory->service($this->auth_method);

			// Authenticate using service
			if ($authenticationService->authenticate($login,$password)) {
				app_log("'$login' authenticated successfully",'notice',__FILE__,__LINE__);
				$this->clearAuthFailures();
				$this->auditRecord("AUTHENTICATION_SUCCESS","Authenticated successfully");

				// update last_hit_date	for user login
				$this->recordHit();
				
				return true;
			}
			else {
				app_log("'$login' failed to authenticate",'notice',__FILE__,__LINE__);
				$failure = new \Register\AuthFailure();
				$failure->add(array($_SERVER['REMOTE_ADDR'],$login,'WRONGPASS',$_SERVER['PHP_SELF']));
				$this->increment_auth_failures();
				if ($this->auth_failures() >= 6) {
					app_log("Blocking customer '".$this->code."' after ".$this->auth_failures()." auth failures.  The last attempt was from '".$_SERVER['remote_ip']."'");
					$this->block();
					$this->auditRecord("AUTHENTICATION_FAILURE","Blocked after ".$this->auth_failures()." failures");
				}
				return false;
			}
		}

		/** @method changePassword(password)
		 * Change the customer's password
		 * @param mixed $password 
		 * @return bool 
		 */
		public function changePassword($password): bool {

			if (isset($GLOBALS['_config']->register->minimum_password_strength) && $this->password_strength($password) < $GLOBALS['_config']->register->minimum_password_strength) {
				$this->error("Password needs more complexity");
				return false;
			}
			// Load Specified Authentication Service
			$authenticationFactory = new \Register\AuthenticationService\Factory();
			$authenticationService = $authenticationFactory->service($this->auth_method);
			
			if ($authenticationService->changePassword($this->code,$password)) {
				$this->resetAuthFailures();
				$this->auditRecord('PASSWORD_CHANGED','Password changed');
				return true;
			}
			else {
				$this->error($authenticationService->error());
				return false;
			}
		}

		/** @method validLogin(string)
		 * Check if the password is valid
		 * @param string $string The password to check
		 * @return bool True if valid, otherwise false
		 */
		public function validPassword($string): bool {
			$strength = $this->password_strength($string);
			return $strength >= $GLOBALS['_config']->register->minimum_password_strength;
		}

		/** @method password_strength(string)
		 * How complex is the password?
		 * @param string $string Password to check
		 * @return int Complexity score
		 */
		public function password_strength($string) {
			# Initial score on length alone
			$password_strength = strlen($string);
	
			# Subtract 1 as any one character will match below
			$password_strength --;
	
			# Add Points for Each Type of Char
			if (preg_match('/[A-Z]/', $string)) $password_strength += 1;
			if (preg_match('/[\@\$\_\-\.\!\&]/', $string)) $password_strength += 1;
			if (preg_match('/\d/', $string)) $password_strength += 1;
			if (preg_match('/[a-z]/', $string)) $password_strength += 1;
	
			return $password_strength;
		}

		/** @method auth_failures()
		 * See How Many Auth Failures the account has
		 * @return int 
		 */
		public function auth_failures() {
			$get_failures_query = "
				SELECT	auth_failures
				FROM	register_users
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_failures_query,array($this->id));
			if (! $rs) {
				$this->error("SQL Error in Register::Customer::auth_failures(): ".$GLOBALS['_database']->ErrorMsg());
				return null;
			}
			list($count) = $rs->FetchRow();
			return $count;
		}

		/** @method resetAuthFailures()
		 * Reset the number of authentication failures
		 * @return bool True if successful, otherwise false
		 */
		public function resetAuthFailures() {
			return $this->update(array("auth_failures" => 0));
		}

		/** @method products(product)
		 * Get List of Purchased Products
		 * @param string $product Optional SKU to filter by
		 * @return array List of purchased products
		 */
		public function products($product='') {
			// Clear Errors
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query
			$get_products_query = "
				SELECT	p.id,
						date_format(cp.expire_date,'%c/%e/%Y') expire_date,
						cp.quantity,
						unix_timestamp(sysdate()) - unix_timestamp(cp.expire_date) expired,
						pt.group_flag
				FROM	online_product.customer_products cp,
						product.products p,
						product.product_types pt
				WHERE	cp.customer_id = ?
				AND		p.product_id = cp.product_id
				AND		p.type_id = pt.type_id
				AND		cp.parent_id = 0
				AND		(cp.expire_date > sysdate() 
				OR		cp.quantity > 0
				OR		pt.group_flag = 1)
				AND		cp.void_flag = 0
			";
			// Add Parameters
			$database->AddParam($this->id);

			// Conditional
			if (isset($product) && $product) {
				$get_products_query .= "
				AND p.sku = ?";
				$database->AddParam($product);
			}

			// Execute Query
			$rs = $database->Execute($get_products_query);
			if ($rs->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return 0;
			}
			$products = array();
			while ($results = $rs->FetchRow()) {
				$product = new \Product\Item($results['id']);
				array_push($products,$product);
			}
			return $products;
		}

		/** @method can(privilege_name)
		 * Check if the user has a specific privilege
		 * @param string $privilege_name The name of the privilege to check
		 * @return bool True if user has the privilege, otherwise false
		 */
		public function can($privilege_name): bool {
			if ($GLOBALS['_SESSION_']->elevated()) return true;
			return $this->has_privilege($privilege_name);
		}

		/** @method has_role(role_name)
		 * See If a User has been granted a Role
		 * @param string $role_name The name of the role to check
		 * @return bool True if user has the role, otherwise false
		 */
		public function has_role($role_name) {
			$this->clearError();
			$role = new \Register\Role();
			if (! $role->get($role_name)) {
				$this->error("Role not found");
				return false;
			}
			return $this->has_role_id($role->id);
		}

		/** @method has_role_id(role_id)
		 * See If a User has been granted a Role
		 */
		public function has_role_id($role_id) {
			$this->clearError();

			$database = new \Database\Service();

			// Check Role Query
			$check_role_query = "
				SELECT	role_id
				FROM 	register_users_roles
				WHERE	user_id = ?
				AND		role_id = ?
			";

			$database->AddParam($this->id);
			$database->AddParam($role_id);

			$rs = $database->Execute($check_role_query);

			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			list($has_it) = $rs->Fields();
			if ($has_it) {
				return $has_it;
			}
			else {
				return false;
			}
		}

		/** @method has_privilege(privilege name)
		 * Check if customer has specified privilege
		 * @param string Privilege Name
		 * @return bool True if customer has privilege, otherwise false
		 */
		public function has_privilege($privilege_name) {
			$this->clearError();
			$database = new \Database\Service();
			$privilege = new \Register\Privilege();
			
			if (! $privilege->get($privilege_name)) {
				if ($privilege_name != "manage privileges" && $GLOBALS['_SESSION_']->customer->can("manage privileges")) {
					$privilege->add(array('name' => $privilege_name));
				}
				else {
					return false;
				}
			}
			$check_privilege_query = "
				SELECT	1
				FROM	register_users_roles rur,
						register_roles_privileges rrp
				WHERE	rur.user_id = ?
				AND		rrp.role_id = rur.role_id
				AND		rrp.privilege_id = ?
			";
			$database->AddParam($this->id);
			$database->AddParam($privilege->id);

			$rs = $database->Execute($check_privilege_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}
			list($found) = $rs->FetchRow();

			if ($found == "1") return true;
			else return false;
		}
		
		/** @method notify_role_members(role_id, message)
		 * Send notification to Members in a Role
		 */
		public function notify_role_members($role_id,$message) {
			$role = new \Register\Role($role_id);
			if (! $role->id) {
				$this->error("Role not found");
				return false;
			}
			$members = $role->members();
			foreach ($members as $member) {
				$this->notify($member->id,$message);
			}
			return true;
		}

		/** @method roles()
		 * Get List of User Roles
		 */
		public function roles() {
			// Clear previous errors
			$this->clearError();
	
			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query
			$get_roles_query = "
				SELECT	r.id
				FROM	register_roles r
						INNER JOIN 	register_users_roles rur
						ON r.id = rur.role_id
				WHERE	rur.user_id = ?
			";

			// Add Parameters
			$database->AddParam($this->id);

			$rs = $database->Execute(
				$get_roles_query
			);

			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}

			$roles = array();
			while (list($id) = $rs->FetchRow()) {
				$role = new Role($id);
				array_push($roles,$role);
			}
			
			return $roles;
		}

		/** @method expire()
		 * Expire the Customer by changing their status to EXPIRED
		 */
		public function expire() {
			$this->update($this->id,array("status" => 'EXPIRED'));
			return true;
		}

		/** @method last_active()
		 * Get the last active date of the user
		 * @return string|null Last active date or null if no sessions found
		 */
		public function last_active() {
			// Clear previous errors
			$this->clearError();

			// If no user ID, return null
			if (! $this->id) return null;

			// Initialize Session List
			$sessionList = new \Site\SessionList();
			$sessions = $sessionList->find(
				[	"user_id"	=> $this->id,
				],
				[	"sort"	=> 'last_hit_date',
					"order"	=> 'desc',
					"limit"	=> 1
				]
			);
			if ($sessionList->error()) {
				$this->error("Error getting session: ".$sessionList->error());
				return null;
			}
			if (count($sessions) > 0) $session = $sessions[0];
			else return null;
			return $session->last_hit_date;
		}

		/** @method is_super_elevated()
		 * Check if the user has a super elevated session
		 * @return bool True if super elevated, otherwise false
		 */
		public function is_super_elevated() {
			$sessionList = new \Site\SessionList();
			list($session) = $sessionList->find(array("user_id" => $this->id,"_sort" => 'last_hit_date',"_desc" => true,'_limit' => 1));
			if ($sessionList->error()) {
				$this->error("Error getting session: ".$sessionList->error());
				return false;
			}
			if (! $session) return false;
			return time() < strtotime($session->super_elevation_expires);
		}

		/** @method contacts()
		 * Get a list of contacts for the customer
		 * @param array $params Optional parameters to filter contacts
		 * @return \Register\ContactList|null List of contacts or null on error
		 */
		public function contacts($params = array()) {
			$contactList = new \Register\ContactList();
			$parameters = array(
				'person_id'	=> $this->id
			);

			if (isset($params['type'])) $parameters['type'] = $params['type'];
			$contacts = $contactList->find($parameters);
			if ($contactList->error()) {
				$this->error($contactList->error());
				return null;
			}
			else {
				return $contacts;
			}
		}

		/** @method notify_email()
		 * Get the primary email address for notifications
		 * @return string|null Email address or null if not found
		 */
		public function notify_email() {
			$contactList = new \Register\ContactList();
			$parameters = array(
				'person_id'	=> $this->id,
				'type'		=> 'email',
				'notify'	=> true,
			);
			$contacts = $contactList->find($parameters);
			if (empty($contacts)) {
				return null;
			}
			list($contact) = $contacts;
			return $contact->value;
		}

		/** @method hasNotifyEmail()
		 * Check if the user has any email addresses set to 'Notify'
		 * @return bool True if user has notify email, false otherwise
		 */
		public function hasNotifyEmail(): bool {
			$notify_email = $this->notify_email();
			return !empty($notify_email);
		}

		/** @method getNotifyEmails()
		 * Get all email addresses set to 'Notify' for this user
		 * @return array Array of email addresses
		 */
		public function getNotifyEmails(): array {
			$contactList = new \Register\ContactList();
			$parameters = array(
				'person_id'	=> $this->id,
				'type'		=> 'email',
				'notify'	=> true,
			);
			$contacts = $contactList->find($parameters);
			$emails = array();
			foreach ($contacts as $contact) {
				$emails[] = $contact->value;
			}
			return $emails;
		}

		/** @method locations()
		 * Get a list of locations associated with the customer
		 * @param array $parameters Optional parameters for location retrieval
		 * @return \Register\Location[]|null List of locations or null on error
		 */
		public function locations($parameters = array()) {
			$get_locations_query = "
				SELECT	rol.location_id
				FROM	register_organization_locations rol
				WHERE	rol.organization_id = ?
				UNION
				SELECT	rul.location_id
				FROM	register_user_locations rul
				WHERE	rul.user_id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_locations_query,array($this->organization()->id,$this->id));
			
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			$locations = array();
			while (list($id) = $rs->FetchRow()) {
				$location = new \Register\Location($id,$parameters);
				array_push($locations,$location);
			}
			return $locations;
		}

		/** @method randomPassword()
		 * Generate a random password
		 * @return string Random password
		 */
		public function randomPassword() {
			$pass = "";
			$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890?|@!*&%#';
			$num_chars = strlen($chars) -1;

			while ($this->password_strength($pass) < $GLOBALS['_config']->register->minimum_password_strength) {
				$pass .= substr($chars,rand(0,$num_chars),1);
			}
			return $pass; //turn the array into a string
		}

		/** @method validationKey()
		 * Get the validation key for the user
		 * @return string|null Validation key or null on error
		 */
		public function validationKey() {
			$database = new \Database\Service();
			$get_key_query = "
				SELECT	validation_key
				FROM	register_users
				WHERE	id = ?
			";
			$database->AddParam($this->id);
			$rs = $database->Execute($get_key_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}
			list($key) = $rs->FetchRow();
			return $key;
		}

		/** @method login()
		 * Get the login code for the user
		 * @return string Login code
		 */
		public function login() {
			return $this->code;
		}

		/** @method resetKey()
		 * Get the password reset key for the user
		 * @return string|null Password reset key or null on error
		 */
		public function resetKey() {
			$token = new \Register\PasswordToken();
			$key = $token->getKey($this->id);
			if ($token->error()) {
				$this->error($token->error());
				return null;
			}
			return $key;
		}

		/** @method acceptedTOU(tou_id)
		 * Check if the user has accepted the Terms of Use
		 * @param int $tou_id The ID of the Terms of Use
		 * @return bool True if accepted, false otherwise
		 */
		public function acceptedTOU($tou_id) {
			$tou = new \Site\TermsOfUse($tou_id);
			$latest_version = $tou->latestVersion();
			if ($tou->error()) {
				$this->error($tou->error());
				return false;
			}
			elseif (empty($latest_version)) {
				$this->error('No published version of tou '.$tou_id);
				return false;
			}
			else {
				$actionList = new \Site\TermsOfUseActionList();
				app_log("Checking for user ".$this->id." approval of version ".$latest_version->id,'trace');
				list($action) = $actionList->find(array('version_id' => $latest_version->id,'user_id' => $this->id,'type' => 'ACCEPTED'));
				if ($actionList->error()) {
					$this->error($actionList->error());
					return false;
				}
				if (!empty($action)) {
					app_log("User has approved version ".$latest_version->id,'trace');
					return true;
				}
			}
			return false;
		}

		/** @method acceptTOU(version_id)
		 * Accept the Terms of Use for the user
		 * @param int $version_id The ID of the Terms of Use version
		 * @return bool True if accepted, false on error
		 */
		public function acceptTOU($version_id) {
			$version = new \Site\TermsOfUseVersion($version_id);
			$version->addAction($this->id,'ACCEPTED');

			if ($version->error()) {
				$this->error($version->error());
				return false;
			}
			return true;
		}

		/** @method declineTOU(version_id)
		 * Decline the Terms of Use for the user
		 * @param int $version_id The ID of the Terms of Use version
		 * @return bool True if declined, false on error
		 */
		public function declineTOU($version_id) {
			$version = new \Site\TermsOfUseVersion($version_id);
			$version->addAction($this->id,'DECLINED');

			if ($version->error()) {
				$this->error($version->error());
				return false;
			}
			return true;
		}

		/** @method auditRecord(type, notes, customer_id)
		 * Audit Record
		 * @param string $type
		 * @param string $notes
		 * @param int $customer_id, used only for empty session
		 * @return bool
		 */
		public function auditRecord($type, $notes, $customer_id = null) {
			$audit = new \Register\UserAuditEvent();
			if (!empty($GLOBALS['_SESSION_']->customer->id)) $customer_id = $GLOBALS['_SESSION_']->customer->id;
			if (!empty($this->id) && empty($customer_id)) $customer_id = $this->id;

			if ($audit->validClass($type) == false) {
				app_log("Invalid audit class: ".$type,'error');
				return false;
			}

			$audit->add(array(
				'user_id'		=> $this->id,
				'admin_id'		=> $customer_id,
				'event_date'	=> date('Y-m-d H:i:s'),
				'event_class'	=> $type,
				'event_notes'	=> $notes
			));
			
			if ($audit->error()) {
				app_log($audit->error(),'error');
				return false;
			}
			return true;
		}

		/** @method sessions(parameters)
		 * Get a list of sessions for the customer
		 * @param array $parameters Optional parameters to filter sessions
		 * @return \Site\SessionList|null List of sessions or null on error
		 */
		public function sessions($parameters = array()) {
			$sessionList = new \Site\SessionList();
			$parameters['user_id'] = $this->id;
			$sessions = $sessionList->find($parameters);
			if ($sessionList->error()) {
				$this->error($sessionList->error());
				return null;
			}
			else {
				return $sessions;
			}
		}

		/** @method requiresOTP()
		 * Determines if this customer requires OTP authentication
		 * Checks organization, roles, and user settings in order
		 * @return bool True if OTP is required
		 */
		public function requiresOTP(): bool {
			
			app_log("DEBUG: requiresOTP() called for customer ID: ".$this->id, 'debug', __FILE__, __LINE__);

			// If use_otp false, return false immediately
			if (!$GLOBALS['_config']->register->use_otp) {
				return false;
			}
		
			// Check organization setting
			$organization = $this->organization();
			if ($organization && !empty($organization->time_based_password)) {
				return true;
			}
			
			// Check role settings
			$userRoles = $this->roles();
			foreach ($userRoles as $role) {
				if (!empty($role->time_based_password)) {
					return true;
				}
			}

			// Check user setting
			if (!empty($this->time_based_password)) {
				return true;
			}
			
			return false;
		}

		/** @method sendOTPRecovery(email_address)
		 * Send OTP recovery email to the specified email address
		 * @param string $email_address Email address to send recovery to
		 * @return bool True if sent successfully, false on error
		 */
		public function sendOTPRecovery($email_address): bool {
			$this->clearError();

			// Check if the user has an email set to 'Notify'
			$notify_email = $this->notify_email();
			if (empty($notify_email)) {
				$this->error("No email address is set to 'Notify' for this account. Please contact support to update your email preferences.");
				return false;
			}

			// Verify that the provided email matches the notify email (for security)
			if ($email_address !== $notify_email) {
				$this->error("The provided email address does not match the email address set to 'Notify' for this account.");
				return false;
			}

			// Generate recovery token
			$token = $this->generateOTPRecoveryToken();
			if (!$token) {
				return false;
			}

			// Build recovery URL
			$recovery_url = "http";
			if ($GLOBALS['_config']->site->https) $recovery_url = "https";
			$recovery_url .= "://" . $GLOBALS['_config']->site->hostname . "/_register/otp?recovery_token=" . $token;

			// Prepare email template using the template system and config path
			$template_path = isset($GLOBALS['_config']->register->otp_recovery->template)
				? $GLOBALS['_config']->register->otp_recovery->template
				: null;
			if (!$template_path || !file_exists($template_path)) {
				$this->error("OTP recovery email template not found");
				return false;
			}
			$template_content = file_get_contents($template_path);
			$notice_template = new \Content\Template\Shell();
			$notice_template->content($template_content);
			$notice_template->addParam('RECOVERY.URL', $recovery_url);
			$notice_template->addParam('RECOVERY.LINK', $recovery_url);
			$notice_template->addParam('COMPANY.NAME', $GLOBALS['_SESSION_']->company->name ?? '');

			// Create and send email
			$message = new \Email\Message();
			$message->html(true);
			$message->to($email_address);
			$message->from($GLOBALS['_config']->register->forgot_password->from ?? 'no-reply@' . $GLOBALS['_config']->site->hostname);
			$message->subject("Two-Factor Authentication Recovery");
			$message->body($notice_template->output());

			$transportFactory = new \Email\Transport();
			$transport = $transportFactory->Create(array('provider' => $GLOBALS['_config']->email->provider));
			
			if (!$transport) {
				$this->error("Error initializing email transport");
				return false;
			}

			if ($transport->error()) {
				$this->error("Error initializing email transport: " . $transport->error());
				return false;
			}

			$transport->hostname($GLOBALS['_config']->email->hostname);
			$transport->token($GLOBALS['_config']->email->token);

			if ($transport->deliver($message)) {
				$this->auditRecord('OTP_RECOVERY_REQUESTED', 'OTP recovery email sent to: ' . $email_address);
				return true;
			}
			else {
				$this->error("Error sending recovery email: " . ($transport->error() ?: "Unknown error"));
				return false;
			}
		}

		/** @method generateOTPRecoveryToken()
		 * Generate OTP recovery token
		 * @return string|false Recovery token or false on error
		 */
		public function generateOTPRecoveryToken() {
			$this->clearError();

			if (!$this->id) {
				$this->error("Customer not identified");
				return false;
			}

			// Generate secure random token
			$token = hash('sha256', random_bytes(32) . microtime() . $this->id);

			// Set expiration (24 hours from now)
			$expires = date('Y-m-d H:i:s', strtotime('+1 day'));

			// Insert recovery token
			$insert_query = "
				INSERT INTO register_otp_recovery 
				(user_id, recovery_token, date_created, date_expires, used)
				VALUES (?, ?, NOW(), ?, 0)
				ON DUPLICATE KEY UPDATE 
					recovery_token = VALUES(recovery_token),
					date_created = VALUES(date_created),
					date_expires = VALUES(date_expires),
					used = 0
			";

			$GLOBALS['_database']->Execute($insert_query, array($this->id, $token, $expires));

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}

			return $token;
		}

		/** @method verifyOTPRecoveryToken(token)
		 * Verify and consume OTP recovery token
		 * @param string $token Recovery token
		 * @return bool True if valid and consumed
		 */
		public function verifyOTPRecoveryToken($token): bool {
			$this->clearError();

			if (empty($token)) {
				$this->error("Recovery token required");
				return false;
			}

			// Find valid, unused token
			$select_query = "
				SELECT user_id 
				FROM register_otp_recovery 
				WHERE recovery_token = ? 
				AND date_expires > NOW() 
				AND used = 0
			";

			$rs = $GLOBALS['_database']->Execute($select_query, array($token));

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}

			if (!$rs || $rs->RecordCount() == 0) {
				$this->error("Invalid or expired recovery token");
				return false;
			}

			list($user_id) = $rs->FetchRow();

			// Mark token as used
			$update_query = "
				UPDATE register_otp_recovery 
				SET used = 1 
				WHERE recovery_token = ?
			";

			$GLOBALS['_database']->Execute($update_query, array($token));

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}

			// Load customer if not already loaded
			if ($this->id != $user_id) {
				$customer = new \Register\Customer($user_id);
				if ($customer->error()) {
					$this->error($customer->error());
					return false;
				}
				$this->id = $customer->id;
				$this->details();
			}

			// Clear the user's secret_key (reset 2FA setup)
			$result = $this->update(array('secret_key' => ''));
			if (!$result) {
				$this->error("Failed to clear secret key during OTP recovery");
				return false;
			}

			$this->auditRecord('OTP_RESET', 'OTP recovery token used to reset 2FA and secret key cleared');
			return true;
		}

		/** @method resetOTPSetup()
		 * Reset user's OTP setup (clear secret key)
		 * @return bool True if successful
		 */
		public function resetOTPSetup(): bool {
			$this->clearError();

			if (!$this->id) {
				$this->error("Customer not identified");
				return false;
			}

			// Clear the secret key
			$result = $this->update(array('secret_key' => ''));

			if ($result) {
				$this->auditRecord('OTP_RESET', 'OTP setup reset - secret key cleared');
				
				// Clear cache
				$cache_key = "customer[" . $this->id . "]";
				$cache = new \Cache\Item($GLOBALS['_CACHE_'], $cache_key);
				$cache->delete();
			}

			return $result;
		}

		/**	 * Generate a new backup code for the user
		 * Attempt to login with a backup code
		 * @param string $code
		 * @return int|false user_id if valid, false if not
		 */
		public static function loginWithBackupCode($code) {
			$db = $GLOBALS['_database'];
			$query = "SELECT user_id, id FROM register_backup_codes WHERE code = ? AND used = 0 LIMIT 1";
			$rs = $db->Execute($query, array($code));
			if ($db->ErrorMsg()) {
				return false;
			} elseif ($rs && $row = $rs->FetchRow()) {
				list($user_id, $code_id) = $row;
				// Mark code as used
				$update = $db->Execute("UPDATE register_backup_codes SET used = 1 WHERE id = ?", array($code_id));
				// Clear the user's secret_key so they can reset TOTP
				$db->Execute("UPDATE register_users SET secret_key = '' WHERE id = ?", array($user_id));
				return $user_id;
			}
			return false;
		}

		/** @method deleteAllBackupCodes()
		 * Delete all backup codes for this user
		 */
		public function deleteAllBackupCodes() {
			$db = $GLOBALS['_database'];
			$query = "DELETE FROM register_backup_codes WHERE user_id = ?";
			$db->Execute($query, array($this->id));
		}

		/** @method generateBackupCodes(count)
		 * Generate and save new backup codes for this user
		 * @param int $count
		 * @return array|false
		 */
		public function generateBackupCodes($count = 6) {
			$codes = array();
			$db = $GLOBALS['_database'];

			// Get user's primary email
			$email = $this->notify_email();
			if (empty($email)) {
				$this->error('Generate Backup Codes: email address not found for user [must also be set to notify]');
				return false;
			}

			for ($i = 0; $i < $count; $i++) {
				$code = strtoupper(bin2hex(random_bytes(4)));
				$codes[] = $code;
				$db->Execute("INSERT INTO register_backup_codes (user_id, code, used) VALUES (?, ?, 0)", array($this->id, $code));
			}

			// Send backup codes email using config template path
			$template_path = isset($GLOBALS['_config']->register->backup_codes->template)
				? $GLOBALS['_config']->register->backup_codes->template
				: null;
			if ($template_path && file_exists($template_path)) {
				$template_content = file_get_contents($template_path);
				$notice_template = new \Content\Template\Shell();
				$notice_template->content($template_content);
				$notice_template->addParam('BACKUP.CODES', implode('<br>', array_map('htmlentities', $codes)));
				$notice_template->addParam('USER.NAME', $this->full_name());
				$notice_template->addParam('SITE.NAME', $GLOBALS['_config']->site->hostname);
				$notice_template->addParam('COMPANY.NAME', $GLOBALS['_SESSION_']->company->name ?? '');

				if ($email) {
					$message = new \Email\Message();
					$message->html(true);
					$message->to($email);
					$message->from($GLOBALS['_config']->register->forgot_password->from ?? 'no-reply@' . $GLOBALS['_config']->site->hostname);
					$message->subject("Your New Backup Codes");
					$message->body($notice_template->output());
					$transportFactory = new \Email\Transport();
					$transport = $transportFactory->Create(array('provider' => $GLOBALS['_config']->email->provider));
					if ($transport) {
						$transport->hostname($GLOBALS['_config']->email->hostname);
						$transport->token($GLOBALS['_config']->email->token);
						$transport->deliver($message);
					}
				}
			}
			return $codes;
		}

		/** @method getAllBackupCodes()
		 * Get all backup codes for this user
		 * @return array
		 */
		public function getAllBackupCodes() {
			$db = $GLOBALS['_database'];
			$rs = $db->Execute("SELECT code, used FROM register_backup_codes WHERE user_id = ? ORDER BY id ASC", array($this->id));
			$codes = array();
			if ($rs) {
				while ($row = $rs->FetchRow()) {
					$codes[] = array('code' => $row[0], 'used' => $row[1]);
				}
			}
			return $codes;
		}
    }
