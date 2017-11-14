<?php
	namespace Porkchop;
	
	class Register {
		private $_error;
		private $_session;

		public function __construct($session) {
			$this->_session = $session;
		}
		
		public function login($login,$password) {
			$request = new \HTTP\Request();
			$request->addParam('method','authenticateSession');
			$request->addParam('login',$login);
			$request->addParam('password',$password);
			$request->host($this->_session->host());
			$request->uri('/_register/api');
			$request->method('POST');

			if ($request->error()) {
				$this->_error = "Error: ".$request->error();
				return false;
			}
			else {
				$client = $this->_session->client();
				if (! $client->connect('www.spectrosinstruments.com')) {
					$this->_error = 'Error connecting to server: '.$this->_error;
					return false;
				}

				$response = $client->post($request);
				if ($client->error()) {
					$this->_error = "Error sending request: ".$client->error();
					return false;
				}
				if ($response->code(302)) {
					return true;
				}
				else {
					$this->_error = "Error parsing authentication response: ".$response->error();
					return false;
				}
			}
		}
		
		public function error() {
			return $this->_error;
		}
	}
?>