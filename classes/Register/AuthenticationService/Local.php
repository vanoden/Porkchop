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
			$db_service = new \Database\Service();
			if ($db_service->supports_password()) {
				$check_password_query = "
					SELECT	`password`,password(?) FROM register_users WHERE login = ?";
				$rs = $GLOBALS['_database']->Execute($check_password_query,array($password,$login));
				if (! $rs) {
					$this->SQLError($GLOBALS['_database']->ErrorMsg());
					return false;
				}
				list($x,$y) = $rs->FetchRow();
				app_log("Pass: $x vs $y",'debug');

			    $get_user_query = "
				    SELECT	id
				    FROM	register_users
				    WHERE	login = ?
				    AND		password = password(?)
			    ";
			}
			else {
				$check_password_query = "
					SELECT	`password`,CONCAT('*', UPPER(SHA1(UNHEX(SHA1(?))))) FROM register_users WHERE login = ?";
				$rs = $GLOBALS['_database']->Execute($check_password_query,array($password,$login));
				if (! $rs) {
					$this->SQLError($GLOBALS['_database']->ErrorMsg());
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
			$bind_params = array($login,$password);

			query_log($get_user_query,$bind_params);
			$rs = $GLOBALS['_database']->Execute(
				$get_user_query,$bind_params
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
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

			$db_service = new \Database\Service();
			if ($db_service->supports_password()) {
				$check_password_query = "
				SELECT	`password`,password(?) FROM register_users WHERE login = ?";
				$rs = $GLOBALS['_database']->Execute($check_password_query,array($password,$login));
				if (! $rs) {
					$this->SQLError($GLOBALS['_database']->ErrorMsg());
					return false;
				}
				list($x,$y) = $rs->FetchRow();
				app_log("Pass: $x vs $y",'debug');

				$update_password_query = "
					UPDATE	register_users
					SET	`password` = password(?),
						password_age = sysdate()
					WHERE	id = ?
				";
			}
			else {
				$check_password_query = "
					SELECT	`password`,CONCAT('*', UPPER(SHA1(UNHEX(SHA1(?))))) FROM register_users WHERE login = ?";
				$rs = $GLOBALS['_database']->Execute($check_password_query,array($password,$login));
				if (! $rs) {
					$this->SQLError($GLOBALS['_database']->ErrorMsg());
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
			$rs = $GLOBALS['_database']->Execute($update_password_query,array($password,$customer->id));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			// Check if any rows were actually updated
			if ($GLOBALS['_database']->Affected_Rows() == 0) {
				$this->error("No rows were updated");
				return false;
			}
			return true;
		}
	}
