<?php
	namespace Email\Transport;
	
	class Proxy {
		private $_error;
		private $_hostname;
		private $_username;
		private $_password;
		
		public function __construct($parameters = array()) {
			if (isset($parameters['hostname']) and ! $this->hostname($parameters['hostname'])) return null;
			if (isset($parameters['username']) and ! $this->username($parameters['username'])) return null;
			if (isset($parameters['password']) and ! $this->password($parameters['password'])) return null;
		}

		public function hostname($hostname) {
			$this->_hostname = $hostname;
			return $this->_hostname;
		}

		public function username($username) {
			$this->_username = $username;
			return $this->_username;
		}

		public function password($password) {
			$this->_password = $password;
		}

		public function secure($secure) {
			if (is_bool($secure)) {
				$this->_secure = $secure;
				return $this->_secure;
			}
			else {
				$this->_error = "secure must be true or false";
				return null;
			}
		}

		public function deliver($email) {
			require_once("Mail.php");
			#require_once('Mail/mime.php');
			$connection = array(
				'host'		=> $this->_hostname,
				'port'		=> $this->_port,
				'auth'		=> $this->_auth,
				'username'	=> $this->_username,
				'password'	=> $this->_password,
			);
			if ($this->_secure) {
				$connection['host'] = $this->_secure."://".$connection['host'];
			}

			$headers = array(
				'From'			=> $email->from,
				'Subject'		=> $email->subject,
				'Content-type'	=> 'text/html'
			);

			$smtp = Mail::factory(
				'smtp',
				$connection
			);

			$mail = $smtp->send(
				$this->_to,
				$headers,
				$this->_body
			);

			if (PEAR::isError($mail)) {
				$this->error = "Error sending email: ".$mail->getMessage();
				return null;
			} else {
				return 1;
			}
