<?php
	namespace Register\AuthenticationService;

	abstract class Base Extends \BaseClass {
		/** @method public authenticate(login,password)
		 * Authenticate a user with given login and password
		 * @param string $login Login name
		 * @param string $password Password
		 * @return bool True if authentication is successful, false otherwise
		 */
		abstract public function authenticate($login,$password);

		/** @method public changePassword(login,password)
		 * Change the password for the user with the given login
		 * @param string $login
		 * @param string $password
		 * @return bool True on success, false on failure
		 */
		abstract public function changePassword($login, $password);

		/** @method public validLogin(login)
		 * Validate the format of a login name
		 * @param string $login Login name
		 * @return bool True if valid, false otherwise
		 */
		abstract public function validLogin($login);

		/** @method public logFailure(login,ip_address,reason)
		 * Log a failed authentication event
		 * @param string $login Login name used
		 * @param string $ip_address IP Address of the client
		 * @param string $reason Reason for failure (if applicable)
		 */
		public function logFailure($login, $ip_address, $reason, $endpoint = '') {
			if (empty($reason)) {
				$reason = 'UNKNOWN';
			}
			if (empty($endpoint)) {
				$endpoint = $_SERVER['REQUEST_URI'] ?? '';
			}
			$authFailure = new \Register\AuthFailure();
			$parameters = array($ip_address, $login, $reason, $endpoint);
			if (! $authFailure->add($parameters)) {
				app_log("Failed to log authentication failure: ".$authFailure->getErrorMessage(),'error');
			}
		}
	}