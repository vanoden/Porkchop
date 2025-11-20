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
			// Check if any rows were actually updated
			//if ($database->Affected_Rows() == 0) {
            //    // @TODO during register process it audits the original password itself being changed
			//	$this->error("No rows were updated");
			//	return false;
			//}

			app_log("Password updated for customer ".$customer->id,'info',__FILE__,__LINE__);
			$customer->recordAuditEvent($customer->id, 'Password changed');

			// Update user statistics
			$stored_stats = new \Register\User\Statistics($customer->id);
			$parameters = array(
				'last_password_change_date' => new \DateTime(),
				'password_change_count' => $stored_stats->password_change_count + 1
			);
			// Update stored statistics
			if (!$stored_stats->update($parameters)) {
				app_log("Error updating user statistics for customer ".$customer->id.": ".$stored_stats->error(),'error',__FILE__,__LINE__);
			}

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
