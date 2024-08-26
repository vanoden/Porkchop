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

		public function parse($string): bool {
			// Parse the Data
			$this->_login = $string[0].$string[1].$string[2].$string[3].$string[4].$string[5].$string[6].$string[7].$string[8].$string[9].$string[10].$string[11].$string[12].$string[13].$string[14].$string[15];
			$this->_password = $string[16].$string[17].$string[18].$string[19].$string[20].$string[21].$string[22].$string[23].$string[24].$string[25].$string[26].$string[27].$string[28].$string[29].$string[30].$string[31];
			return true;
		}

		public function build(&$string): int {
			// Build the data
			$string = $this->padString($this->_login,16).$this->padString($this->_password,16);
			return strlen($string);
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