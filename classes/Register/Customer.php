<?php
	namespace Register;

    class Customer extends Person {
		public bool $elevated = false;
		public int $unreadMessages = 0;
		protected string $password = '';
		private \Register\User\Statistics|null $_statistics = null;

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
				// Initialize register_user_statistics record with appropriate fields
				$this->statistics()->initRecord();
				
				// Determine if this is admin account creation or new registration
				// For admin account creation, user_id should be the admin creating it (from session)
				// For new registration, user_id and instance_id both match the mysql insert id
				$audit_user_id = null;
				if (!empty($GLOBALS['_SESSION_']->customer->id) && $GLOBALS['_SESSION_']->customer->id != $this->id) {
					// Admin account creation - use admin's ID from session
					$audit_user_id = $GLOBALS['_SESSION_']->customer->id;
				} else {
					// New registration - user_id and instance_id match the mysql insert id
					$audit_user_id = $this->id;
				}
				
				// Record audit event
				// instance_id always matches the mysql insert id of the new record
				$this->auditRecord('REGISTRATION_SUBMITTED','Customer added', $audit_user_id);
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
				if (!empty($parameters['organization_id']) && $this->organization_id != $parameters['organization_id']) {
					$oldOrg = $this->organization();
					$oldOrgName = $oldOrg ? $oldOrg->name : 'Unknown';
					$this->auditRecord("ORGANIZATION_CHANGED","Organization changed from ".$oldOrgName." to ".$parameters['organization_id']);
				}
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
			$this->statistics()->recordHit();
		}

		/** @method clearAuthFailures()
		 * Clear Auth Failures
		 */
		public function clearAuthFailures() {
			$this->statistics()->resetFailedLogins();
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

		/** @method add_role(role_id)
		 * Add a Role to the Customer
		 * @param string $role_id The ID of the role to add
		 * @return bool True if successful, otherwise false
		 */
		function add_role ($role_id): bool {
			// Clear any previous errors
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			$role = new \Register\Role($role_id);
			if (! $role->id) {
				$this->error("Role not found");
				return false;
			}

			// Security Check - Ensure user has privilege to assign roles
			if ($GLOBALS['_SESSION_']->elevated()) {
				app_log("Elevated Session adding role");
			}
			elseif ($GLOBALS['_SESSION_']->customer->can('manage customers')) {
				app_log("Granting role '$role_id' to customer '".$this->id."'",'info',__FILE__,__LINE__);
			}
			else {
				app_log("Non admin failed to update roles",'notice',__FILE__,__LINE__);
				$this->error("Insufficient Privileges");
				return false;
			}

			// Prevent self-privilege escalation: Users cannot grant themselves roles that increase their privilege level
			$current_user = $GLOBALS['_SESSION_']->customer;
			if ($current_user && $current_user->id == $this->id) {
				// User is trying to grant a role to themselves (Ok if the have ADMINISTRATOR level 'manage customers' privilege)
				if (! $current_user->has_privilege('manage customers',\Register\PrivilegeLevel::ADMINISTRATOR) && $this->wouldGrantHigherPrivilege($role_id, $this)) {
					$this->error("You cannot grant yourself privileges that would increase your privilege level");
					return false;
				}
			}

			// Prepare SQL Query
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

			// Bind Parameters
			$database->AddParam($this->id);
			$database->AddParam($role_id);

			// Execute Query
			$database->Execute($add_role_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			# Bust Cache
			$cache_key = "customer[" . $this->id . "]";
			$cache = new \Cache\Item($GLOBALS['_CACHE_'], $cache_key);
			$cache->delete();

			$this->recordAuditEvent($this->id,'Role '.$role->name.' assigned');
			return true;
		}

		/** @method drop_role(role_id)
		 * Remove a Role from the Customer
		 * @param string $role_id The ID of the role to remove
		 */
		function drop_role($role_id) {
			// Clear any previous errors
			$this->clearError();

			$role = new \Register\Role($role_id);
			if (! $role->id) {
				$this->error("Role not found");
				return false;
			}

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare SQL Query
			$drop_role_query = "
				DELETE
				FROM	register_users_roles
				WHERE	user_id = ?
				AND		role_id = ?
			";

			// Bind Parameters
			$database->AddParam($this->id);
			$database->AddParam($role_id);

			// Execute Query
			$database->Execute($drop_role_query);

			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			$this->recordAuditEvent($this->id,'Role '.$role->name.' removed');
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

			// Get IP address and user agent for logging
			$request = new \HTTP\Request();
			$request->deconstruct();
			$ip_address = $request->client_ip;

			// Get User Agent
			$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

			// Identify EndPoint
			$endpoint = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : (isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '');

			// Validate Input
			if (! $this->validLogin($login)) {
				// Log authentication failure
				$failure = new \Register\AuthFailure();
				$failure->add(array($ip_address,$login,'UNKNOWN',$endpoint,$user_agent));
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
				// Log authentication failure
				$failure = new \Register\AuthFailure();
				$failure->add(array($ip_address,$login,'NOACCOUNT',$endpoint,$user_agent));
				return false;
			}
			if (!empty($auth_method)) $this->auth_method = $auth_method;

			// check if they have an expired password for organzation rules
			$this->get($login);		
			if ($this->password_expired()) {
				$this->error("Your password is expired.  Please use Recover Password to restore.");
				// Log authentication failure
				$failure = new \Register\AuthFailure();
				$failure->add(array($ip_address,$login,'PASSEXPIRED',$endpoint,$user_agent));
				// Update statistics: increment failed_login_count and set last_failed_login_date
				$this->statistics()->recordFailedLogin();
				return false;
			}

			// Load Specified Authentication Service
			$authenticationFactory = new \Register\AuthenticationService\Factory();
			$authenticationService = $authenticationFactory->service($this->auth_method);

			// Authenticate using service
			if ($authenticationService->authenticate($login,$password)) {
				app_log("'$login' authenticated successfully",'notice',__FILE__,__LINE__);
				// Successful authentication - update statistics only, no separate log entry
				// recordLogin() updates: last_login_date, last_hit_date, failed_login_count=0, 
				// first_login_date (if not set), and increments session_count
				$this->clearAuthFailures();
				$this->statistics()->recordLogin();
				
				return true;
			}
			else {
				app_log("'$login' failed to authenticate",'notice',__FILE__,__LINE__);
				// Log authentication failure
				$failure = new \Register\AuthFailure();
				$failure->add(array($ip_address,$login,'WRONGPASS',$endpoint,$user_agent));
				$this->statistics()->recordFailedLogin();
				if ($this->auth_failures() >= 6) {
					app_log("Blocking customer '".$this->code."' after ".$this->auth_failures()." auth failures.  The last attempt was from '".$_SERVER['remote_ip']."'");
					$this->block();
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
				$this->statistics()->recordPasswordChange();
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
			return $this->statistics()->failed_login_count;
		}

		/** @method resetAuthFailures()
		 * Reset the number of authentication failures
		 * @return bool True if successful, otherwise false
		 */
		public function resetAuthFailures() {
			return $this->statistics()->resetFailedLogins();
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

		/**
		 * Magic method to handle can() calls with different parameter counts
		 * @param string $name Method name
		 * @param array $parameters Method parameters
		 * @return mixed
		 */
		public function __call($name, $parameters) {
			if ($name == "can") {
				if (count($parameters) == 2) {
					// Check if second parameter is an object (entity-based access control)
					if (is_object($parameters[1])) {
						return $this->_canWithEntity($parameters[0], $parameters[1]);
					}
					// Otherwise, treat as privilege level check
					return $this->_canLevel($parameters[0], $parameters[1]);
				} elseif (count($parameters) == 1) {
					return $this->_canLevel($parameters[0], \Register\PrivilegeLevel::ADMINISTRATOR); // Default to administrator level
				}
			}
			
			// Delegate other method calls to parent class
			return parent::__call($name, $parameters);
		}

		/**
		 * Check if customer has a privilege with specific level
		 * @param string $privilege_name The privilege name to check
		 * @param mixed $required_level The required privilege level (int or string)
		 * @return bool True if customer has the privilege at the required level
		 */
		protected function _canLevel($privilege_name, $required_level): bool {
			if ($GLOBALS['_SESSION_']->elevated()) return true;

			// Convert level to integer if it's a string
			$level_int = \Register\PrivilegeLevel::convertLevelToInt($required_level);
			if ($level_int === null) {
				return false;
			}

			return $this->has_privilege($privilege_name, $level_int);
		}

		/**
		 * Check if customer can perform action on a specific entity
		 * @param string $privilege_name The privilege name to check
		 * @param object $entity The entity object (Register::Customer, Register::Organization, Register::SubOrganization)
		 * @return bool True if customer has the privilege and is authorized for the entity
		 */
		protected function _canWithEntity($privilege_name, \Register\PrivilegeLevel $entity): bool {
			if ($GLOBALS['_SESSION_']->elevated()) return true;
			return $this->has_privilege($privilege_name, $entity->requiredPrivilegeLevel());
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
		public function has_privilege($privilege_name, ?int $required_level = \Register\PrivilegeLevel::ADMINISTRATOR) {
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
				SELECT	rrp.level
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
				return false;
			}

			while (list($level) = $rs->FetchRow()) {
				//print_r("Privilege ID: ".var_export($privilege->id,true)." User Level: ".var_export($level,true)." Required Level: ".var_export($required_level,true)."\n");
				// Is the required level present in user's privilege level?
				// Use bitwise check for privilege levels
				if (inMatrix($level,$required_level)) {
					//print_r("Matched!\n");
					return true;
				}
			}
			return false;
		}

		/**
		 * Check if user's privilege level contains the required level
		 * Uses bitwise AND to determine if a specific level is included
		 * @param int $user_level The user's combined privilege level
		 * @param int $required_level The required privilege level
		 * @return bool True if user has the required level
		 */
		private function checkPrivilegeLevel(int $user_level, int $required_level): bool {
			// Use the PrivilegeLevel::hasLevel() method which implements correct bitwise checking
			return \Register\PrivilegeLevel::hasLevel($user_level, $required_level);
		}

		/**
		 * Get privilege level name by ID
		 * @param int $id The privilege level ID
		 * @return string|null The privilege level name or null if not found
		 */
		public function privilegeName(int $id): ?string {
			return \Register\PrivilegeLevel::privilegeName($id);
		}

		/**
		 * Get privilege level ID by name
		 * @param string $name The privilege level name
		 * @return int|null The privilege level ID or null if not found
		 */
		public function privilegeId(string $name): ?int {
			return \Register\PrivilegeLevel::privilegeId($name);
		}

		/**
		 * Validate privilege level name
		 * @param string $name The privilege level name to validate
		 * @return bool True if valid
		 */
		public function validPrivilegeName(string $name): bool {
			return \Register\PrivilegeLevel::validPrivilegeName($name);
		}

		/**
		 * Check if customer can modify privileges for a role
		 * @param \Register\Role $role The role to check permissions for
		 * @return bool True if customer can modify role privileges, otherwise false
		 */
		public function canModifyRolePrivileges($role): bool {
			$this->clearError();

			// Elevated sessions can do anything
			if ($GLOBALS['_SESSION_']->elevated()) {
				return true;
			}

			// Users with administrator level can modify any role
			if ($this->has_privilege('manage privileges', \Register\PrivilegeLevel::ADMINISTRATOR)) {
				return true;
			}

			// Users with organization_manager level can only modify roles they themselves have
			if ($this->has_privilege('manage privileges', \Register\PrivilegeLevel::ORGANIZATION_MANAGER)) {
				// Check if this user has the role
				if ($role instanceof \Register\Role && $role->id) {
					return $this->has_role_id($role->id);
				}
				return false;
			}

			// Other privilege levels cannot modify role privileges
			return false;
		}

		/**
		 * Check if granting a role to a customer would increase their privilege level
		 * @param int $role_id The role ID to check
		 * @param \Register\Customer $target_customer The customer receiving the role (optional, defaults to self)
		 * @return bool True if granting the role would increase privilege level, false otherwise
		 */
		private function wouldGrantHigherPrivilege(int $role_id, $target_customer = null): bool {
			if ($target_customer === null) {
				$target_customer = $this;
			}

			// Get the role and its privileges
			$role = new \Register\Role($role_id);
			if (!$role->id) {
				return false; // Invalid role, can't grant higher privilege
			}

			$role_privileges = $role->privileges();

			// Check each privilege in the role
			foreach ($role_privileges as $role_privilege) {
				// Get the target customer's current level for this privilege
				$current_level = $this->getPrivilegeLevelForUser($target_customer->id, $role_privilege->id);

				// If the role grants a higher level than the user currently has, it would increase privilege
				if ($role_privilege->level > $current_level) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Get the maximum privilege level a user has for a specific privilege
		 * @param int $user_id The user ID
		 * @param int $privilege_id The privilege ID
		 * @return int The maximum privilege level (0 if user has no privilege)
		 */
		private function getPrivilegeLevelForUser(int $user_id, int $privilege_id): int {
			$database = new \Database\Service();

			$get_level_query = "
				SELECT	MAX(rrp.level) as max_level
				FROM	register_users_roles rur,
						register_roles_privileges rrp
				WHERE	rur.user_id = ?
				AND		rrp.role_id = rur.role_id
				AND		rrp.privilege_id = ?
			";

			$database->AddParam($user_id);
			$database->AddParam($privilege_id);

			$rs = $database->Execute($get_level_query);
			if (!$rs || $database->ErrorMsg()) {
				return 0;
			}

			$row = $rs->FetchRow();
			if ($row) {
				list($max_level) = $row;
				return $max_level ?? 0;
			}

			return 0;
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
			$organization = $this->organization();
		if (!$organization) {
			$this->error("Customer has no associated organization");
			return null;
		}
		$rs = $GLOBALS['_database']->Execute($get_locations_query,array($organization->id,$this->id));
			
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
	 * Audit Record - Migrated to use site_audit_events table
	 * @param string $type Event type/class (for backward compatibility)
	 * @param string $notes Description of the event
	 * @param int $customer_id User ID making the change (from session if not provided)
	 * @return bool
	 */
	public function auditRecord($type, $notes, $customer_id = null) {
		// Get user_id from session if not provided
		if (empty($customer_id)) {
			if (!empty($GLOBALS['_SESSION_']->customer->id)) {
				$customer_id = $GLOBALS['_SESSION_']->customer->id;
			} elseif (!empty($this->id)) {
				$customer_id = $this->id;
			}
		}

		// Use site_audit_events table (replaces register_user_audit)
		$audit = new \Site\AuditLog\Event();
		$result = $audit->add(array(
			'instance_id' => $this->id,
			'description' => $type . ': ' . $notes,
			'class_name' => $this->_getActualClass(),
			'class_method' => 'auditRecord',
			'customer_id' => $customer_id
		));
		
		if ($audit->error()) {
			app_log($audit->error(),'error');
			return false;
		}
		return $result;
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

		/** @method public statistics()
		 * Get the statistics object for the customer
		 * @return \Register\User\Statistics Statistics object
		 */
		public function statistics(): \Register\User\Statistics {
			if ($this->_statistics instanceof \Register\User\Statistics) {
				return $this->_statistics;
			}
			return new \Register\User\Statistics($this->id);
		}

		/** @method requiresOTP()
		 * Determines if this customer requires OTP authentication
		 * Checks organization, roles, and user settings in order
		 * @return bool True if OTP is required
		 */
		public function requiresOTP(): bool {
			
			app_log("DEBUG: requiresOTP() called for customer ID: ".$this->id, 'debug', __FILE__, __LINE__);

			// If use_otp false, return false immediately
			$configuration = new \Site\Configuration();
			if (!$configuration->getValueBool("use_otp")) {
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

			app_log("Attempting to deliver OTP recovery email to: " . $email_address, 'debug', __FILE__, __LINE__, 'otplogs');
			if ($transport->deliver($message)) {
				app_log("OTP recovery email delivered successfully to: " . $email_address, 'info', __FILE__, __LINE__, 'otplogs');
				$this->auditRecord('OTP_RECOVERY_REQUESTED', 'OTP recovery email sent to: ' . $email_address);
				return true;
			}
			else {
				$errorMsg = $transport->error() ?: "Unknown error";
				app_log("Failed to deliver OTP recovery email to: " . $email_address . ". Error: " . $errorMsg, 'error', __FILE__, __LINE__, 'otplogs');
				$this->error("Error sending recovery email: " . $errorMsg);
				return false;
			}
		}

		/** @method sendBackupCodeUsedNotification()
		 * Send backup code used notification email
		 * @return bool True if sent successfully, false on error
		 */
		public function sendBackupCodeUsedNotification(): bool {
			$this->clearError();

			// Get the notify email address
			$notify_email = $this->notify_email();
			if (empty($notify_email)) {
				// Don't error if no notify email - just log and return true
				app_log("No notify email set for customer " . $this->id . ", skipping backup code notification", 'info', __FILE__, __LINE__, 'otplogs');
				return true;
			}

			// Check if template configuration exists
			if (!isset($GLOBALS['_config']->register->backup_code_used_notification)) {
				$this->error("Backup code notification email configuration not found");
				return false;
			}

			$email_config = $GLOBALS['_config']->register->backup_code_used_notification;
			if (!isset($email_config->template) || !file_exists($email_config->template)) {
				$this->error("Backup code notification email template not found");
				return false;
			}

			$template = new \Content\Template\Shell(
				array(
					'path' => $email_config->template,
					'parameters' => array(
						'CUSTOMER.NAME' => $this->full_name(),
						'CUSTOMER.EMAIL' => $notify_email,
						'DATE.TIME' => date('F j, Y \a\t g:i A T'),
						'IP.ADDRESS' => $GLOBALS['_REQUEST_']->client_ip ?? 'Unknown',
						'COMPANY.NAME' => $GLOBALS['_SESSION_']->company->name ?? 'Spectros Instruments'
					)
				)
			);

			if ($template->error()) {
				$this->error("Error generating backup code notification email: " . $template->error());
				return false;
			}

			// Create and send email
			$message = new \Email\Message();
			$message->html(true);
			$message->to($notify_email);
			$message->from($email_config->from);
			$message->subject($email_config->subject);
			$message->body($template->output());

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
				$this->auditRecord('BACKUP_CODE_USED', 'Backup code used notification sent to: ' . $notify_email);
				app_log("Backup code notification email sent to: " . $notify_email, 'info', __FILE__, __LINE__, 'otplogs');
				return true;
			}
			else {
				$this->error("Error sending backup code notification email: " . ($transport->error() ?: "Unknown error"));
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
		
		// Send OTP reset notification email
		$this->sendOTPResetNotification();
		
		return true;
		}

		/** @method sendOTPResetNotification()
		 * Send OTP reset notification email to customer
		 * @return bool True if email was sent successfully, false otherwise
		 */
		private function sendOTPResetNotification(): bool {
			if (!$this || !$this->id) {
				app_log("Invalid customer object for OTP reset notification", 'error', __FILE__, __LINE__);
				return false;
			}

			$to_email = $this->notify_email();
			if (empty($to_email)) {
				app_log("No email address available for customer " . $this->id, 'error', __FILE__, __LINE__);
				return false;
			}

			// Check if template configuration exists
			if (!isset($GLOBALS['_config']->register->otp_reset_notification)) {
				app_log("OTP reset notification email configuration not found", 'error', __FILE__, __LINE__);
				return false;
			}

			$email_config = $GLOBALS['_config']->register->otp_reset_notification;
			if (!isset($email_config->template) || !file_exists($email_config->template)) {
				app_log("OTP reset notification email template not found", 'error', __FILE__, __LINE__);
				return false;
			}

			$template = new \Content\Template\Shell(
				array(
					'path' => $email_config->template,
					'parameters' => array(
						'CUSTOMER.FIRST_NAME' => $this->first_name,
						'CUSTOMER.LOGIN' => $this->code,
						'RESET.DATE' => date('Y-m-d'),
						'RESET.TIME' => date('H:i:s T'),
						'SUPPORT.EMAIL' => $GLOBALS['_config']->site->support_email,
		
						'LOGIN.URL' => 'http' . ($GLOBALS['_config']->site->https ? 's' : '') . '://' . $GLOBALS['_config']->site->hostname . '/_register/login',
						'COMPANY.NAME' => $GLOBALS['_SESSION_']->company->name ?? 'Spectros Instruments'
					)
				)
			);

			if ($template->error()) {
				app_log("Error generating OTP reset notification email: " . $template->error(), 'error', __FILE__, __LINE__);
				return false;
			}

			$message = new \Email\Message();
			$message->html(true);
			$message->to($to_email);
			$message->from($email_config->from);
			$message->subject($email_config->subject);
			$message->body($template->output());

			$transportFactory = new \Email\Transport();
			$transport = $transportFactory->Create(array('provider' => $GLOBALS['_config']->email->provider));
			if ($transport && !$transport->error()) {
				$transport->hostname($GLOBALS['_config']->email->hostname);
				$transport->token($GLOBALS['_config']->email->token);
				if (!$transport->deliver($message)) {
					app_log("Error sending OTP reset notification email: " . $transport->error(), 'error', __FILE__, __LINE__);
					return false;
				} else {
					app_log("OTP reset notification email sent to " . $to_email, 'info', __FILE__, __LINE__);
					$this->auditRecord('OTP_RESET_NOTIFICATION_SENT', 'OTP reset notification email sent to: ' . $to_email);
					return true;
				}
			} else {
				app_log("Error creating email transport for OTP reset notification: " . ($transport ? $transport->error() : 'Transport creation failed'), 'error', __FILE__, __LINE__);
				return false;
			}
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
		
		// Send OTP reset notification email
		$this->sendOTPResetNotification();
				
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
				
				// Clear customer cache to ensure fresh data is loaded
				$cache_key = "customer[" . $user_id . "]";
				$cache = new \Cache\Item($GLOBALS['_CACHE_'], $cache_key);
				$cache->delete();
				
							// Send OTP reset notification email
			$customer = new \Register\Customer($user_id);
			if (!$customer->error()) {
				$customer->sendOTPResetNotification();
			}
				
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

		/** @method getStatistics()
		 * Get user statistics
		 * @return array
		 */
		public function getStatistics(): array {
			$statistics = new \Register\User\Statistics($this->id);
			return $statistics->toArray();
		}

		/** @method public purgeAgingSessions()
		 * Purge aging sessions for this user
		 * @return int Number of sessions purged
		 */
		public function purgeAgingSessions(): int {
			// Clear previous errors
			$this->clearError();

			// If no user ID, return false
			if (! $this->id) {
				$this->error("User ID not set");
				return 0;
			}

			// Initialize Database Service
			$database = new \Database\Service();

			$start_time = microtime(true);

			// Get Statistics from Database
			$session_stats = $this->getStatsFromSessions();
			if ($this->error()) {
				print_r("Error getting session statistics: " . $this->error() . "\n");
				return 0;
			}
			if (empty($session_stats)) {
				$this->error("No session statistics found for user");
				return 0;
			}
			print_r("Session Stats: <br>\n");
			print_r($session_stats);
			print_r("<br>\nElapsed: " . (microtime(true) - $start_time) . " seconds<br>\n");

			// Get Stored Statistics
			$stored_stats = $this->statistics();
			print_r("Stored Stats: <br>\n");
			print_r($stored_stats->toArray());
			print_r("<br>\nElapsed: " . (microtime(true) - $start_time) . " seconds<br>\n");

			if (empty($stored_stats)) {
				$stored_stats = new \Register\User\Statistics($this->id);
				$stored_stats->update(array(
					'last_hit_date' => $session_stats['last_hit_date'] ?? null,
					'session_count' => $session_stats['session_count'] ?? 0,
					'first_login_date' => $session_stats['first_login_date'] ?? null,
					'last_failed_login_date' => $session_stats['last_failed_login_date'] ?? null,
					'failed_login_count' => $session_stats['failed_login_count'] ?? 0,
				));
				if ($stored_stats->error()) {
					$this->error("Error updating stored statistics: " . $stored_stats->error());
					return 0;
				}
			}
			else {
				$parameters = [];
				if (!empty($session_stats['last_failed_login_date'])) {
					if (empty($stored_stats->last_failed_login_date) || $stored_stats->last_failed_login_date < $session_stats['last_failed_login_date']) {
						$parameters['last_failed_login_date'] = $session_stats['last_failed_login_date'];
					}
				}
				if (!empty($session_stats['last_hit_date'])) {
					if (empty($stored_stats->last_hit_date) || $stored_stats->last_hit_date < $session_stats['last_hit_date']) {
						$parameters['last_hit_date'] = $session_stats['last_hit_date'];
					}
				}
				if (!empty($session_stats['first_login_date'])) {
					if (empty($stored_stats->first_login_date) || $stored_stats->first_login_date > $session_stats['first_login_date']) {
						$parameters['first_login_date'] = $session_stats['first_login_date'];
					}
				}
				if (!empty($session_stats['session_count']) && $stored_stats->session_count < $session_stats['session_count']) {
					$parameters['session_count'] = $session_stats['session_count'];
				}
				if (!empty($session_stats['failed_login_count']) && $stored_stats->failed_login_count < $session_stats['failed_login_count']) {
					$parameters['failed_login_count'] = $session_stats['failed_login_count'];
				}
				if (!empty($parameters)) {
					print_r("<br>\nParameters: <br>\n");
					print_r($parameters);
					print_r($stored_stats);
					if ($stored_stats->update($parameters)) {
						print_r("Stored statistics updated successfully\n");
					} elseif ($stored_stats->error()) {
						print_r("Error updating stored statistics\n");
						$this->error("Error updating stored statistics: " . $stored_stats->error());
						return 0;
					}
					else {
						$this->error("Unknown error updating stored statistics");
						print_r("Unknown error updating stored statistics\n");
						return 0;
					}
				}
				print_r("Woohoo!");
				return 0;
			}
			print_r("Total time: " . (microtime(true) - $start_time) . " seconds\n");
exit;
			print_r("Deleting old sessions: <br>\n");
			$delete_query = "
				CALL purge_aging_sessions_for_user(?)
			";
			$database->AddParam($this->id);
			$database->Execute($delete_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return 0;
			}
			print_r("Total time: " . (microtime(true) - $start_time) . " seconds\n");
			return 0;
		}

		/** @method getStatsFromSessions()
		 * Get user statistics from session data
		 * @return array
		 */
		public function getStatsFromSessions(): array {
			// Clear Previous Errors
			$this->clearError();

			// Prepare Database Service
			$database = new \Database\Service();

			// Require User ID
			if (!is_numeric($this->id) || $this->id <= 0) {
				$this->error("User ID required to collect session statistics");
				return [];
			}

			// Initialize Response Array
			$results = [];

			// Collect statistics from user sessions
			$session_query = "
				SELECT	COUNT(*) AS session_count,
						MAX(last_hit_date) AS last_hit_date
				FROM	session_sessions
				WHERE	user_id = ?
			";

			$database->AddParam($this->id);
			$rs = $database->Execute($session_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}
			if ($row = $rs->FetchRow()) {
				$results["session_count"] = (int)$row['session_count'];
				$results["last_hit_date"] = $row['last_hit_date'];
			}

			$first_session_query = "
				SELECT	MAX(hit_date)
				FROM	session_hits
				WHERE	script = '/_register/login'
				AND		session_id = (
					SELECT	id
					FROM	session_sessions
					WHERE	user_id = ?
					ORDER BY last_hit_date
					LIMIT 1
				)
			";

			$database->resetParams();
			$database->AddParam($this->id);
			$rs = $database->Execute($first_session_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}
			if ($row = $rs->FetchRow()) {
				$results["first_login_date"] = $row[0];
			}

			// Get last login failure from register_auth_failures
			$failureList = new \Register\AuthFailureList();
			$last_failure = $failureList->last(["login" => $this->code]);
			if ($failureList->error()) {
				$this->error($failureList->error());
				return [];
			}
			$results["last_failed_login_date"] = $last_failure->date;

			// Get the number of login failures since the last successful login
			$failureList->find(["login" => $this->code]);
			$results['failed_login_count'] = $failureList->count();

			return $results;
		}
	}