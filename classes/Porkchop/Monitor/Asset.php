<?php
	namespace Porkchop\Monitor;
	
	class Asset {
		private $_session;
		private $_error;
		private $_code;
		private $_name;
		private $_id;
		private $_status;
		private $_organization_id;
		private $_product;
		
		public function __construct($session) {
			$this->_session = $session;
		}
		
		public function get($code) {
			$request = new \HTTP\Request();
			$request->addParam('method','getAsset');
			$request->addParam('code',$code);
			$request->host($this->_session->host());
			$request->uri('/_monitor/api');
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
						$this->_organization_id = $asset['organization_id'];
						$this->_product = new \Porkchop\Product\Product();
						$this->_product->load($asset['product']);
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
		
		public function product() {
			return $this->_product;
		}

		public function error() {
			return $this->_error;
		}
	}
