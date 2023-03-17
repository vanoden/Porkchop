<?php
	namespace HTTP;

	class Response {
		protected $_error;
		protected int $_code = 200;
		protected $_status;
		protected $_headers = array();
		protected $_content;
		protected $_cookies = array();
		
		public function __construct() {
			//$this->header = new Header();
		}
		
		public function parse($string) {
			$section = 'status';
			while(list($line,$string) = preg_split('/\r?\n/',$string,2)) {
				if ($section == 'status') {
					if (preg_match('/^HTTP\/\d\.\d\s(\d+)\s(.+)/',$line,$matches)) {
						$this->_code = $matches[1];
						$this->_status = $matches[2];
						$section = 'headers';
					}
					else {
						$this->_error = "Can't find status line";
						return false;
					}
				}
				if($section == 'headers') {
					if (strlen($line) < 1) {
						$section = 'body';
					}
					elseif (preg_match('/^([^\:]+)\:\s*(.*)/',$line,$matches)) {
						if ($matches[1] == 'Set-Cookie') {
							$cookie = new \HTTP\Cookie();
							$cookie->parse($matches[2]);
							array_push($this->_cookies,$cookie);
						}
						else {
							$this->_headers[$matches[1]] = $matches[2];
						}
					}
				}
				if($section == 'body') {
					$this->_content = $string;
					return true;
				}
			}
		}
		
		public function content() {
			return $this->_content;
		}
		
		public function code(int $value = 0): int {
			if ($value > 0) $this->_code = $value;
			return $this->_code;
		}
		
		public function status() {
			return $this->_status;
		}
		
		public function header($key) {
			return $this->_headers[$key];
		}
		
		public function cookies() {
			return $this->_cookies;
		}

		public function error($string = null) {
			if (isset($string)) $this->_error = $string;
			return $this->_error;
		}
	}
