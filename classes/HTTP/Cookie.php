<?php
	namespace HTTP;
	
	class Cookie {
		private $_error;
		private $_name;
		private $_domain;
		private $_expires;
		private $_path;
		private $_value;
		
		public function parse($string) {
			$sections = preg_split('/\;\s?/',$string);
			foreach ($sections as $section) {
				list($key,$value) = preg_split('/=/',$section);
				if ($key == 'domain') $this->_domain = $value;
				elseif($key == 'expires') $this->_expires = strtotime($value);
				elseif($key == 'Max-Age') $this->_max_age = $value;
				elseif($key == 'path') $this->_path = $value;
				else {
					$this->_name = $key;
					$this->_value = $value;
				}
			}
			return true;
		}
		
		public function name() {
			return $this->_name;
		}
		public function value() {
			return $this->_value;
		}
		public function expired() {
			if ($this->_expires < time) return true;
			return false;
		}
		public function path() {
			return $this->_path;
		}
		public function error() {
			return $this->_error;
		}
	}
