<?php
	namespace Register;

    class Customer extends Person {
    
		public $auth_method;
		public $elevated = 0;

		public function __construct($person_id = 0) {
			parent::__construct($person_id);
			if ($this->id) $this->roles();
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
				$this->error = "SQL Error in Register::Customer::get: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
		}

		public function details() {
		    parent::details();
			if ($this->id) {
				$this->roles();
				return true;
			} else {
				return false;
			}
		}

		public function update($parameters = array()) {
		
			parent::update($parameters);

			// roles
			if (isset($GLOBALS['_SESSION_']->customer) && $GLOBALS['_SESSION_']->customer->can('manage customers')) {
				$rolelist = new RoleList();
				$roles = $rolelist->find();
				foreach ($roles as $role) {
					if (isset($parameters['roles']) && is_array($parameters['roles'])) {
						if (array_key_exists($role['id'],$parameters['roles'])) {
							$this->add_role($role['id']);
						} else {
							$this->drop_role($role['id']);
						}
					}
				}
			}

			return $this->details();
		}

		public function increment_auth_failures() {
			if (! isset($this->id)) return false;
			$update_customer_query = "
				UPDATE	register_users
				SET		auth_failures = auth_failures + 1
				WHERE	id = ?";
			$GLOBALS['_database']->Execute($update_customer_query,array($this->id));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error("SQL Error in Register::Customer::increment_auth_failures(): ".$GLOBALS['_database']->ErrorMsg());
				return false;
			}
			return true;
		}

		function add_role ($role_id) {
		
			if ($GLOBALS['_SESSION_']->elevated()) {
				app_log("Elevated Session adding role");
			} elseif ($GLOBALS['_SESSION_']->customer->can('manage customers')) {
				app_log("Granting role '$role_id' to customer '".$this->id."'",'info',__FILE__,__LINE__);
			} else {
				app_log("Non admin failed to update roles",'notice',__FILE__,__LINE__);
				$this->error = "Only Register Managers can update roles.";
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
				array(
					$this->id,
					$role_id
				)
			);
			
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Register::Customer::add_role(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return 1;
		}

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
				$this->error = "SQL Error in Register::Customer::drop_role(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			
			return true;
		}

		// Check login and password against configured authentication mechanism
		function authenticate ($login, $password) {
			if (! $login) return 0;

			// Get Authentication Method
			$get_user_query = "
				SELECT	id,auth_method,status,password_age
				FROM	register_users
				WHERE	login = ?
			";
			
			$rs = $GLOBALS['_database']->Execute(
				$get_user_query,
				array($login)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Register::Customer::authenticate(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			list($id,$this->auth_method,$status) = $rs->fields;			
			if (! $id) {
				app_log("Auth denied because no account found matching '$login'",'notice',__FILE__,__LINE__);
				return false;
			}

			if (! in_array($status,array('NEW','ACTIVE'))) {
				app_log("Auth denied because account '$login' is '$status'",'notice',__FILE__,__LINE__);
				return false;
			}

			// check if they have an expired password for organzation rules
			$this->get($login);		
			if ($this->password_expired()) return 0;

			// Load Specified Authentication Service
			$authenticationFactory = new \Register\AuthenticationService\Factory();
			$authenticationService = $authenticationFactory->service($this->auth_method);

			// Authenticate using service
			if ($authenticationService->authenticate($login,$password)) {
				app_log("'$login' authenticated successfully",'notice',__FILE__,__LINE__);
				$this->update(array("auth_failures" => 0));
				return true;
			}
			else {
				app_log("'$login' failed to authenticate",'notice',__FILE__,__LINE__);
				$this->increment_auth_failures();
				if ($this->auth_failures() >= 6) {
					app_log("Blocking customer '".$this->login."' after ".$this->auth_failures()." auth failures.  The last attempt was from '".$_SERVER['remote_ip']."'");
					$this->block();
					return false;
				}
			}
		}

		public function changePassword($password) {
			if ($this->password_strength($password) < $GLOBALS['_config']->register->minimum_password_strength) {
				$this->error("Password needs more complexity");
				return false;
			}

			// Load Specified Authentication Service
			$authenticationFactory = new \Register\AuthenticationService\Factory();
			$authenticationService = $authenticationFactory->service($this->auth_method);

			if ($authenticationService->changePassword($this->login,$password)) {
				return true;
			}
			else {
				$this->error($authenticationService->error());
				return false;
			}
		}

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

		// See How Many Auth Failures the account has
		public function auth_failures() {
			$get_failures_query = "
				SELECT	auth_failures
				FROM	register_users
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_failures_query,array($this->id));
			if (! $rs) {
				$this->error("SQL Error in Register::Customer::auth_failures(): ".$GLOBALS['_database']->ErrorMsg());
			}
			list($count) = $rs->FetchRow();
			return $count;
		}

		// Personal Inventory (Online Products)
		public function products($product='') {
			###############################################
			## Get List of Purchased Products			###
			###############################################
			$bind_params = array();

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
			array_push($bind_params,$this->id);

			// Conditional
			if (isset($product) && $product) {
				$get_products_query .= "
				AND p.sku = ?";
				array_push($bind_params,$product);
			}

			// Execute Query
			$rs = $GLOBALS['_database']->Execute($get_products_query,$bind_params);
			if ($rs->ErrorMsg()) {
				$this->error = "SQL Error in Register::Customer::products(): ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			$products = array();
			while ($record = $rs->FetchRow()) {
				$product = new \Product\Item($product['id']);
				$product->expire_date = $record['expire_date'];
				$product->quantity = $record['quantity'];
				$product->expired = $record['expired'];
				$product->group_flag = $record['group_flag'];
				array_push($products,$product);
			}
			return $products;
		}

		public function can($privilege_name) {
			return $this->has_privilege($privilege_name);
		}

		// See If a User has been granted a Role
		public function has_role($role_name) {
		
			// Check Role Query
			$check_role_query = "
				SELECT	r.id
				FROM	register_roles r
				INNER JOIN 	register_users_roles rur
				ON		r.id = rur.role_id
				WHERE	rur.user_id = ?
				AND		r.name = ?
			";
			
			$rs = $GLOBALS['_database']->Execute(
				$check_role_query,
				array(
					$this->id,
					$role_name
				)
			);
			
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Register::Customer::has_role(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			
			list($has_it) = $rs->fields;
			if ($has_it) {
				return $has_it;
			} else {
				return false;
			}
		}

		public function has_privilege($privilege_name) {
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
			$bind_params = array(
				$this->id,
				$privilege->id
			);

			query_log($check_privilege_query,$bind_params,true);
			$rs = $GLOBALS['_database']->Execute($check_privilege_query,$bind_params);
			if (! $rs) {
				$this->error = "SQL Error in Register::Customer::has_privilege(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($found) = $rs->FetchRow();

			if ($found == "1") return true;
			else return false;
		}
		
		// Get all users that have been granted a Role
		public function have_role($id) {
		
			// Check Role Query
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
				$this->error = "SQL Error in Register::Customer::have_role(): ".$GLOBALS['_database']->ErrorMsg();
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
		
		// Notify Members in a Role
		public function notify_role_members($role_id,$message) {
			$members = $this->have_role($role_id);
			foreach ($members as $member) {
				$this->notify($member->id,$message);
			}
		}

		// Get List of User Roles
		public function roles() {
		
			// Get Roles Query
			$get_roles_query = "
				SELECT	r.id
				FROM	register_roles r
						INNER JOIN 	register_users_roles rur
						ON r.id = rur.role_id
				WHERE	rur.user_id = ?
			";

			$rs = $GLOBALS['_database']->Execute(
				$get_roles_query,
				array($this->id)
			);
			
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Register::Customer::roles(): ".$GLOBALS['_database']->ErrorMsg();
				error_log($this->error);
				return null;
			}

			$roles = array();
			while (list($id) = $rs->FetchRow()) {
				$role = new Role($id);
				array_push($roles,$role);
			}
			
			return $roles;
		}
		
		public function role_id($name) {
		
			// Get Role Query
			$get_role_query = "
				SELECT	id
				FROM	register_roles
				WHERE	name = ?";
	
			$rs = $GLOBALS['_database']->Execute($get_role_query,array($name));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Register::Customer::role_id(): ".$GLOBALS['_database']->ErrorMsg();
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
		
		public function last_active() {
			$sessionList = new \Site\SessionList();
			list($session) = $sessionList->find(array("user_id" => $this->id,"_sort" => 'last_hit_date',"_desc" => true,'_limit' => 1));
			if ($sessionList->error) {
				$this->error = "Error getting session: ".$sessionList->error;
				return null;
			}
			if (! $session) return null;
			return $session->last_hit_date;
		}
		
		public function is_super_elevated() {
			$sessionList = new \Site\SessionList();
			list($session) = $sessionList->find(array("user_id" => $this->id,"_sort" => 'last_hit_date',"_desc" => true,'_limit' => 1));
			if ($sessionList->error) {
				$this->error = "Error getting session: ".$sessionList->error;
				return false;
			}
			if (! $session) return false;
			return time() < strtotime($session->super_elevation_expires);
		}
		
		public function contacts($params = array()) {
			$contactList = new \Register\ContactList();
			$parameters = array(
				'person_id'	=> $this->id
			);
			if (isset($params['type'])) $parameters['type'] = $params['type'];
			$contacts = $contactList->find($parameters);
			if ($contactList->error()) {
				$this->error = $contactList->error();
				return null;
			}
			else {
				return $contacts;
			}
		}
				
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
			$rs = $GLOBALS['_database']->Execute($get_locations_query,array($this->organization->id,$this->id));
			
			if (! $rs) {
				$this->error = "SQL Error in Register::Customer::locations(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$locations = array();
			while (list($id) = $rs->FetchRow()) {
				$location = new \Register\Location($id,$parameters);
				array_push($locations,$location);
			}
			return $locations;
		}

		public function exists($login) {
			list($person) = $this->get($login);
	
			if ($person->id) return true;
			else return false;
		}
    }
