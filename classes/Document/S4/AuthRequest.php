<?php
	namespace Document\S4;

	class AuthRequest Extends \Document\S4\Message {
		private $_userId;
		private $_login;
		private $_password;

		public function __construct() {
			$this->_typeId = 13;
			$this->_typeName = "Auth Request";
			$this->_login = "";
		}

		public function parse(array $array): bool {
			// Parse the Data
			$this->_login = "";
			$this->_password = "";
			$pos = 0;
			app_log("Parsing ".count($array)." bytes of data",'info');

			while ($pos < count($array)) {
				if (ord($array[$pos]) == 0) {
					$pos ++;
					break;
				}
				else {
					$this->_login .= chr(ord($array[$pos]));
				}
				$pos ++;
			}
			while ($pos < count($array)) {
				$this->_password .= chr(ord($array[$pos]));
				$pos ++;
			}
			app_log("AuthRequest::parse() - Login: ".$this->_login.", Password: ".$this->_password,'info');
			return true;
		}

		public function build(array &$array): int {
			// Build the data
			$string = $this->padString($this->_login,16).$this->padString($this->_password,16);
			$array = str_split($string);
			return count($array);
		}

		public function login(string $login = null): string {
			if (isset($login)) {
				$this->_login = $login;
			}
			return $this->_login;
		}

		public function password(string $password = null): string {
			if (isset($password)) {
				$this->_password = $password;
			}
			return $this->_password;
		}

		public function userId(): ?\Register\Customer {
			$customer = new \Register\Customer();
			if ($customer->authenticate($this->_login, $this->_password)) {
				return $customer->id;
			}
			else {
				$this->error("Invalid Login");
				return null;
			}
		}
	}