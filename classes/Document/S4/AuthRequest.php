<?php
	namespace Document\S4;

	class AuthRequest Extends \Document\S4\Message {
		private $_userId;

		public function __construct() {
			$this->_typeId = 13;
			$this->_typeName = "Auth Request";
			$this->_login = "";
		}

		public function parse(array $array, $length = 0): bool {
			// Parse the Data
			$this->_login = "";
			$this->_password = "";
			$pos = 0;
			app_log("Parsing $length bytes of data",'info');

			while ($pos < $length) {
				if (ord($array[$pos]) == 0) {
					$pos ++;
					break;
				}
				else {
					$this->_login .= $array[$pos];
				}
				$pos ++;
			}
			while ($pos < $length) {
				$this->_password .= $array[$pos];
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

		public function login(?string $login = null): string {
			if (isset($login)) {
				$this->_login = $login;
			}
			return $this->_login;
		}

		public function password(?string $password = null): string {
			if (isset($password)) {
				$this->_password = $password;
			}
			return $this->_password;
		}

		public function userId(): ?int {
			$customer = new \Register\Customer();
			if ($customer->authenticate($this->_login, $this->_password)) {
				return $customer->id;
			}
			else {
				$this->error("Invalid Login");
				return null;
			}
		}

		public function readable(): string {
			return "Login: ".$this->_login.", Password: ".$this->_password;
		}
	}