<?php
	namespace Register\AuthenticationService;

	class LDAP Extends Base {
		public $domain;
		public $account;

		public function __construct($options = array()) {
			$this->domain = $options['domain'];
		}
		public function authenticate($login,$password) {
			// Check User Query
			$get_user_query = "
				SELECT	id
				FROM	register_users
				WHERE	login = ?";
            
			$rs = $GLOBALS['_database']->Execute($get_user_query,array($login));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Register::AuthenticationService::LDAP::authenticate(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}

			list($id) = $rs->fields;
			if (! $id) {
				error_log("No account for $login");
				$this->message = "Account not found";
			}

			$LDAPServerAddress1	= $GLOBALS['_config']->authentication->$this->domain->server1;
			$LDAPServerAddress2	= $GLOBALS['_config']->authentication->$this->domain->server2;
			$LDAPServerPort		= "389";
			$LDAPServerTimeOut	= "60";
			$LDAPContainer		= $GLOBALS['_config']->authentication->$this->domain->container;
			$BIND_username		= strtoupper($this->domain)."\\$login";
			$BIND_password		= $password;

			if (($ds=ldap_connect($LDAPServerAddress1)) || ($ds=ldap_connect($LDAPServerAddress2))) {
				ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
				ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);

				if($r=ldap_bind($ds,$BIND_username,$BIND_password)) {
					error_log("LDAP Authentication for $login successful");
					$account = new \Register\Customer($id);
					return true;
				} else {
					$this->message = "Auth Failed: ".ldap_error($ds);
					$GLOBALS['_page']->error = "Auth Failed: ".ldap_error($ds);
					if (ldap_get_option($ds, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error)) {
						error_log("Error Binding to LDAP: $extended_error");
					} else {
						error_log("LDAP Authentication for $login failed");
					}
					return false;
				}
			}
		}

		public function changePassword($password) {
			$this->error("LDAP Password Change Not supported");
			return false;
		}
	}