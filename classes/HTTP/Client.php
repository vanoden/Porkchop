<?php
	namespace HTTP;
	
	class Client {
		private $_error;
		private $_socket;
		private $_connected = false;
		private $_response;
		private $_cookiejar;

		public function __construct() {
			$this->_cookiejar = new \HTTP\CookieJar();
		}
		
		public function connect($host = '127.0.0.1',$port = 80) {
			$address = gethostbyname($host);

			$this->_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			if ($this->_socket == false) {
				$this->_error = "Failed to create socket: ".socket_strerror(socket_last_error());
				return false;
			}
			$result = socket_connect($this->_socket,$address,$port);
			if ($result === false) {
				$this->_error = "Failed to connect: ".socket_strerror(socket_last_error($this->_socket));
				return false;
			}
			$this->_connected = true;
			return true;
		}

		public function post($request) {
			$request->method('POST');
			return $this->request($request);
		}
		
		public function get($request) {
			$request->method('GET');
			return $this->request($request);
		}
		public function request($request) {
			if (! $this->_connected) {
				$this->_error = "Not connected";
				return null;
			}
			$string = $request->serialize();
			if ($request->error()) {
				$this->_error = "Error preparing request: ".$request->error();
				return null;
			}

			socket_write($this->_socket,$string,strlen($string));
			$content = '';
			while ($buffer = socket_read($this->_socket,2048)) {
				$content .= $buffer;
			}
			$this->_response = new \HTTP\Response();

			if ($this->_response->parse($content)) {
				# Store Cookies
				$this->_cookiejar->add($this->_response->cookies());
				return $this->_response;
			}
			else {
				$this->_error = $this->_response->error();
				return null;
			}
		}

		public function cookies() {
			return $this->_cookiejar->all();
		}

		public function error() {
			return $this->_error;
		}
	}
