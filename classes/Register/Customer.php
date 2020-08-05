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
			if (isset($GLOBALS['_SESSION_']->customer) && $GLOBALS['_SESSION_']->customer->has_role('register manager')) {
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

		function add_role ($role_id) {
		
			if ($GLOBALS['_SESSION_']->elevated()) {
				app_log("Elevated Session adding role");
			} elseif ($GLOBALS['_SESSION_']->customer->has_role('register manager')) {
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
				$this->error = $GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return 1;
		}

		function drop_role($role_id) {
		
			// our own polymorphism
			if (! $GLOBALS['_SESSION_']->customer->has_role('register manager')) {
				$this->error = "Only Register Managers can update roles.";
				return false;
			}
			
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
				$this->error = $GLOBALS['_database']->ErrorMsg();
				return false;
			}
			
			return true;
		}

		// Check login and password against configured authentication mechanism
		function authenticate ($login,$password) {
			if (! $login) return 0;

			// Get Authentication Method
			$get_user_query = "
				SELECT	id,auth_method,status
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

			list($id,$this->auth_method,$status) = $rs->fields;
			if (! $id) {
				app_log("Auth denied because no account found matching '$login'",'notice',__FILE__,__LINE__);
				return 0;
			}
			
			if (! in_array($status,array('NEW','ACTIVE'))) {
				app_log("Auth denied because account '$login' is '$status'",'notice',__FILE__,__LINE__);
				return 0;
			}

			if (preg_match('/^ldap\/(\w+)$/',$this->auth_method,$matches))
				$result = $this->LDAPauthenticate($matches[1],$login,$password);
			else
				$result = $this->LOCALauthenticate($login,$password);

			// Logging
			if ($result) {
				app_log("'$login' authenticated successfully",'notice',__FILE__,__LINE__);
			}
			else app_log("'$login' failed to authenticate",'notice',__FILE__,__LINE__);

			return $result;
		}

		// Authenticate using database for credentials
		function LOCALauthenticate ($login,$password) {
		
			if (! $login) {
				app_log("No 'login' for authentication");
				return 0;
			}

			/**
			 * Check User Query
			 * @TODO
			 * OP's MySQL Server version is 8.0.12. From MySQL Documentation, PASSWORD function has been deprecated for version > 5.7.5:
			 *   replacement that gives the same answer in version 8: CONCAT('*', UPPER(SHA1(UNHEX(SHA1('mypass')))))
			 */
            if (preg_match('/^8\./', $GLOBALS['_database']->_connectionID->server_info)) {
			    $get_user_query = "
				    SELECT	id
				    FROM	register_users
				    WHERE	login = ?
				    AND		password = CONCAT('*', UPPER(SHA1(UNHEX(SHA1('".$password."')))));
			    ";
            } else {
			    $get_user_query = "
				    SELECT	id
				    FROM	register_users
				    WHERE	login = ?
				    AND		password = password('".$password."')
			    ";
            }

			$rs = $GLOBALS['_database']->Execute(
				$get_user_query,
				array(
					$login
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = $GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($id) = $rs->FetchRow();

            // Login Failed
			if (! $id) return 0;
			$this->id = $id;
			$this->details();
			return 1;
		}

		// Authenticate using external LDAP service
		public function LDAPauthenticate($domain,$login,$password) {
		
			// Check User Query
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

			if (($ds=ldap_connect($LDAPServerAddress1)) || ($ds=ldap_connect($LDAPServerAddress2))) {
				ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
				ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);

				if($r=ldap_bind($ds,$BIND_username,$BIND_password)) {
					error_log("LDAP Authentication for $login successful");
					$this->details($id);
					return 1;
				} else {
					$this->message = "Auth Failed: ".ldap_error($ds);
					$GLOBALS['_page']->error = "Auth Failed: ".ldap_error($ds);
					if (ldap_get_option($ds, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error)) {
						error_log("Error Binding to LDAP: $extended_error");
					} else {
						error_log("LDAP Authentication for $login failed");
					}
					return 0;
				}
			}
		}

		// Personal Inventory (Online Products)
		public function products($product='') {
		
			###############################################
			## Get List of Purchased Products			###
			###############################################
			
			// Prepare Query
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
	
			// Conditional
			if ($product) $get_products_query .= "AND p.sku = '".mysql_escape_string($product)."'\n";
	
			// Execute Query
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
				$this->error = "SQL Error in RegisterCustomer::has_role: ".$GLOBALS['_database']->ErrorMsg();
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
			$check_privilege_query = "
				SELECT	1
				FROM	register_users_roles rur,
						register_roles_privileges rrp,
						register_privileges p
				WHERE	rur.user_id = ?
				AND		rrp.role_id = rur.role_id
				AND		p.id = rrp.privilege_id
				AND		p.name = ?
			";
			$rs = $GLOBALS['_database']->Execute($check_privilege_query,array($this->id,$privilege_name));
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
				$this->error = $GLOBALS['_database']->ErrorMsg();
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
				
		public function error() {
			return $this->error;
		}
    }
