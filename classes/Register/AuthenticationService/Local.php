<?php
	namespace Register\AuthenticationService;

	class Local Extends Base {
		public $account;
		public function authenticate($login,$password) {
			if (! $login) {
				app_log("No 'login' for authentication");
				return false;
			}

			/**
			 * Check User Query
			 * @TODO
			 * OP's MySQL Server version is 8.0.12. From MySQL Documentation, PASSWORD function has been deprecated for version > 5.7.5:
			 *   replacement that gives the same answer in version 8: CONCAT('*', UPPER(SHA1(UNHEX(SHA1('mypass')))))
			 */
			$database = new \Database\Service();
			// Old MySQL Password Function
			if ($database->supports_password()) {
				$check_password_query = "
					SELECT	`password`,password(?) FROM register_users WHERE login = ?";
				$database->resetParams();
				$database->AddParam($password);
				$database->AddParam($login);
				$rs = $database->Execute($check_password_query);
				if (! $rs) {
					$this->SQLError($database->ErrorMsg());
					return false;
				}
				list($x,$y) = $rs->FetchRow();
				app_log("Pass: $x vs $y",'trace');

			    $get_user_query = "
				    SELECT	id
				    FROM	register_users
				    WHERE	login = ?
				    AND		password = password(?)
			    ";
			}
			// New SHA1 Password Function
			else {
				$check_password_query = "
					SELECT	`password`,CONCAT('*', UPPER(SHA1(UNHEX(SHA1(?))))) FROM register_users WHERE login = ?";
				$database->resetParams();
				$database->AddParam($password);
				$database->AddParam($login);
				$rs = $database->Execute($check_password_query);
				if (! $rs) {
					$this->SQLError($database->ErrorMsg());
					return false;
				}
				list($x,$y) = $rs->FetchRow();
				app_log("SHA1: $x vs $y",'debug');

			    $get_user_query = "
				    SELECT	id
				    FROM	register_users
				    WHERE	login = ?
				    AND		password = CONCAT('*', UPPER(SHA1(UNHEX(SHA1(?)))));
			    ";
            }
			$database->resetParams();
			$database->AddParam($login);
			$database->AddParam($password);

			$rs = $database->Execute($get_user_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			list($id) = $rs->FetchRow();
			
            // Login Failed
			if (! $id) {
				$this->error("Login Failed");
				return false;
			}
			$this->account = new \Register\Customer($id);
			return true;
		}

		/** @method public changePassword(login,password)
		 * Change the password for the user with the given login
		 * @param string $login
		 * @param string $password
		 * @return bool True on success, false on failure
		 */
		public function changePassword($login,$password) {
			if ($_SERVER['SCRIPT_FILENAME'] == BASE."/core/install.php") app_log("Installer setting password for $login",'info');
			else app_log($GLOBALS['_SESSION_']->customer->code." changing password for ".$login,'info');
	
			$customer = new \Register\Customer();
			if (! $customer->get($login)) {
				return false;
			}

			// Initialize Database Service
			$database = new \Database\Service();
			if ($database->supports_password()) {
				$check_password_query = "
					SELECT `password`,
							password(?)
					FROM	register_users WHERE login = ?";
				$database->AddParam($password);
				$database->AddParam($login);
				$rs = $database->Execute($check_password_query);
				if (! $rs) {
					$this->SQLError($database->ErrorMsg());
					return false;
				}
				list($x,$y) = $rs->FetchRow();
				app_log("Pass: $x vs $y",'debug');

				$update_password_query = "
					UPDATE	register_users
					SET		`password` = password(?),
							password_age = sysdate()
					WHERE	id = ?
				";
			}
			else {
				$check_password_query = "
					SELECT	`password`,CONCAT('*', UPPER(SHA1(UNHEX(SHA1(?)))))
					FROM	register_users
					WHERE	login = ?";
				$database->AddParam($password);
				$database->AddParam($login);
				$rs = $database->Execute($check_password_query);
				if (! $rs) {
					$this->SQLError($database->ErrorMsg());
					return false;
				}
				list($x,$y) = $rs->FetchRow();
				app_log("SHA1: $x vs $y",'debug');

				$update_password_query = "
					UPDATE	register_users
					SET		`password` = CONCAT('*', UPPER(SHA1(UNHEX(SHA1(?))))),
							password_age = sysdate()
					WHERE	id = ?
				";
			}
			$database->resetParams();
			$database->AddParam($password);
			$database->AddParam($customer->id);
			$rs = $database->Execute($update_password_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			app_log("Password updated for customer ".$customer->id,'info',__FILE__,__LINE__);
			$customer->recordAuditEvent($customer->id, 'Password changed');
            
			return true;
		}

		/** @method public validLogin(login)
		 * Validate the format of a login name
		 * @param string $login Login name
		 * @return bool True if valid, false otherwise
		 */
		public function validLogin($login) {
			// Valid login: Alphanumeric, underscores, dots, hyphens; 3-30 characters
			if (preg_match('/^[a-zA-Z0-9_.-]{3,30}$/', $login)) {
				return true;
			} else {
				$this->error("Invalid login format");
				return false;
			}
		}
	}
