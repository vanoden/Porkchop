<?php
	namespace Porkchop\Product;
	
	class Product {
		private $_session;
		private $_error;
		private $_code;
		private $_name;
		private $_id;
		private $_status;
		private $_type;
		private $_description;
		
		public function __construct($session) {
			$this->_session = $session;
		}

		public function load($data) {
			$this->_id = $data['id'];
			$this->_code = $data['code'];
			$this->_name = $data['name'];
			$this->_status = $data['status'];
			$this->_type = $data['type'];
			$this->_description = $data['description'];
		}
		
		public function get($code) {
			$request = new \HTTP\Request();
			$request->addParam('method','getProduct');
			$request->addParam('code',$code);
			$request->host($this->_session->host());
			$request->uri('/_product/api');
			$request->method('POST');
			if ($request->error()) {
				$this->_error = "Error: ".$request->error();
				return false;
			}
			else {
				$client = $this->_session->client();
				if (! $client->connect($this->_session->host())) {
					$this->_error = 'Error connecting to server: '.$this->_error;
					return false;
				}

				$response = $client->post($request);
				if ($client->error()) {
					$this->_error = "Error sending request: ".$client->error();
					return false;
				}
				if ($response->code(200)) {
					$document = new \Document('xml');
					$document->parse($response->content());
					$result = $document->data();
					if ($result['success'] == 1) {
						$asset = $result['asset'];
						$this->_id = $asset['id'];
						$this->_code = $asset['code'];
						$this->_name = $asset['name'];
						$this->_status = $asset['status'];
						$this->_type = $asset['type'];
						$this->_description = $asset['description'];
					}
					return true;
				}
				else {
					$this->_error = "Error parsing authentication response: ".$response->error();
					return false;
				}
			}
		}
		
		public function id() {
			return $this->_id;
		}
		
		public function code() {
			return $this->_code;
		}
		
		public function name() {
			return $this->_name;
		}
		
		public function type() {
			return $this->_type;
		}
		
		public function status() {
			return $this->_status;
		}
		
		public function description() {
			return $this->_description;
		}

		public function error() {
			return $this->_error;
		}
	}
