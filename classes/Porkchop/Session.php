<?php
	namespace Porkchop;
	
	class Session {
		private $_client;
		private $_host;
		private $_login;
		private $_password;
		private $_error;
		private $_debug = 1;
		
		public function __construct($parameters = array()) {
			if (isset($parameters['host'])) $this->host($parameters['host']);
			if (isset($parameters['port'])) $this->port($parameters['port']);
			if (isset($parameters['login'])) $this->port($parameters['login']);
			if (isset($parameters['password'])) $this->port($parameters['password']);

			$this->_client = new \HTTP\Client();
		}

		public function code() {
			return $this->_client->cookies()[0]->value();
		}

		public function host($host = null) {
			if (isset($host)) {
				$this->_host = $host;
			}
			return $this->_host;
		}

		public function client() {
			return $this->_client;
		}

		public function connect($host,$port = 80) {
			$this->_host = $host;
			if (! $this->_client->connect($host,$port)) {
				$this->_error = "Failed to connect to host: ".$this->_client->error();
				return false;
			}
			return true;
		}

		public function authenticate($login,$password) {
			$register = new Register($this);
			if (! $register->login($login,$password)) {
				$this->_error = "Login failed: ".$register->error();
				return false;
			}
			return true;
		}

		public function error() {
			return $this->_error;
		}
	}
