<?php
	namespace Email\Transport;

	class Proxy Extends Base {
		private $_hostname;
		private $_username;
		private $_password;
		
		public function __construct($parameters = array()) {
			if (isset($parameters['hostname']) and ! $this->hostname($parameters['hostname'])) return null;
			if (isset($parameters['username']) and ! $this->username($parameters['username'])) return null;
			if (isset($parameters['password']) and ! $this->password($parameters['password'])) return null;
		}

		public function hostname($hostname = null) {
			if (isset($hostname)) $this->_hostname = $hostname;
			return $this->_hostname;
		}

		public function token($token = null) {
			if (isset($token)) $this->_token = $token;
			return $this->_token;
		}

		public function deliver($email) {
			$request = new \HTTP\Request();
			$request->url('http://'.$this->hostname().'/send.php');
			if ($request->error()) {
				$this->error($request->error());
				return false;
			}
			$request->addParam('token',$this->token());
			$request->addParam('to',$email->to());
			$request->addParam('from',$email->from());
			$request->addParam('subject',$email->subject());
			$request->addParam('body',urlencode($email->body()));
			if ($email->html()) $request->addParam('html','true');
			app_log("Email request: '".$request->serialize()."'",'trace');
			$client = new \HTTP\Client();
			$client->connect($this->hostname());
			if ($client->error()) {
				$this->error("Cannot connect to host: ".$client->error());
				return false;
			}
			$response = $client->post($request);
			if ($client->error()) {
				$this->error("Cannot send request: ".$client->error());
				return false;
			}
			app_log("Email response: ".print_r($response,true),'trace');
			if ($response->code() == 200) {
				$content = $response->content();
				app_log($content);
				if (preg_match('/^ERROR\:\s(.*)$/',$content,$matches)) {
					$this->error($matches[1]);
					$this->_result = "Failed";
					return false;
				}
				elseif (preg_match('/Mailer\sError/',$content)) {
					$this->error($content);
					$this->_result = "Failed";
					return false;
				}
				$this->_result = "Sent";
				return true;
			}
			else {
				$this->_result = "Failed";
				$this->_error = $response->code().": ".$response->status();
				return false;
			}
		}
	}
