<?php
	namespace Email\Transport;

	abstract class Base Extends \BaseModel {
		public ?string $_hostname;		// Hostname for the transport
		public ?int $_port = 25;		// Port for the transport, default is 25
		public ?string $_token;			// Token for authentication
		public ?string $_username;		// Username for authentication
		public ?string $_password;		// Password for authentication
		public bool $_secure = false; // Whether to use a secure connection (true/false)

		protected $_result = 'Unknown';

		/** @constructor */
		public function __construct($parameters = array()) {
			if (!empty($parameters['hostname'])) $this->hostname($parameters['hostname']);
			if (!empty($parameters['username'])) $this->username($parameters['username']);
			if (!empty($parameters['password'])) $this->password($parameters['password']);
			if (!empty($parameters['token'])) $this->token($parameters['token']);
			if (isset($parameters['secure'])) $this->secure($parameters['secure']);
		}

		/** @method public hostname(string)
		 * Sets or gets the hostname for the transport.
		 * @param string $hostname The hostname to set.
		 * @return string|null Returns the current hostname if no parameter is provided, otherwise returns void
		 */
		public function hostname($hostname = null) {
			if (isset($hostname)) $this->_hostname = $hostname;
			return $this->_hostname;
		}

		/** @method public token(string)
		 * Sets or gets the token for the transport.
		 * @param string $token The token to set.
		 * @return string|null Returns the current token if no parameter is provided, otherwise returns void
		 */
		public function token($token = null) {
			if (isset($token)) $this->_token = $token;
			return $this->_token;
		}

		/** @method public username(string)
		 * Sets or gets the username for the transport.
		 * @param string $username The username to set.
		 * @return string|null Returns the current username if no parameter is provided, otherwise returns void
		 */
		public function username($username = null) {
			if (isset($username)) $this->_username = $username;
			return $this->_username;
		}

		/** @method public password(string)
		 * Sets or gets the password for the transport.
		 * @param string $password The password to set.
		 * @return string|null Returns the current password if no parameter is provided, otherwise returns void
		 */
		public function password($password = null) {
			if (isset($password)) $this->_password = $password;
			return $this->_password;
		}

		/** @method public secure(bool)
		 * Sets or gets whether the transport uses a secure connection.
		 * @param bool $secure True for secure connection, false otherwise.
		 * @return bool|null Returns the current secure setting if no parameter is provided, otherwise returns void
		 */
		public function secure($secure = null) {
			if (isset($secure)) {
				if ((is_bool($secure) && $secure == true) || (is_int($secure) && $secure == 1) || (is_string($secure) && strtolower($secure) == 'true')) {
					$this->_secure = true;
				} else {
					$this->error("secure must be true or false");
					return null;
				}
			} else {
				$this->_secure = $secure;
			}
			return $this->_secure;
		}

		/** @method private _deliver(email)
		 * Abstract method to deliver the email message.
		 * @param \Email\Message $email The email message to send.
		 * @return bool Returns true on success, false on failure.
		 */
		abstract protected function _deliver($email);

		/** @method public deliver(email)
		 * Abstract method to deliver the email message.
		 * @param \Email\Message $email The email message to send.
		 * @return bool Returns true on success, false on failure.
		 */
		public function deliver($email) {
		    if (empty($email->from())) {
		        $this->error("No from user ID is set for InSite Message.");
		        return false;
		    }

		    if (empty($email->subject())) {
		        $this->error("No subject is set for InSite Message.");
		        return false;
		    }

		    if (empty($email->body())) {
		        $this->error("No body is set for InSite Message.");
		        return false;
		    }

			return $this->_deliver($email);
		}

		/** @method public result()
		 * Gets the result of the last operation.
		 * @return string Returns the result of the last operation.
		 */
		public function result() {
			return $this->_result;
		}
	}
