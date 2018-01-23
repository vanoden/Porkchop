<?php
	namespace HTTP;
	
	class CookieJar {
		public $_cookies = array();
		
		public function add($cookie) {
			if (is_array($cookie)) {
				foreach ($cookie as $subcookie) {
					$this->add($subcookie);
				}
			}
			else {
				array_push($this->_cookies,$cookie);
			}
		}
		
		public function related($domain,$path) {
			$cookies = array();
			foreach ($this->_cookies as $cookie) {
				if (preg_match('@^'.$cookie->path().'@',$path)) {
					if ($domain == $cookie->domain() || preg_match('/\.'.$cookie->domain,$domain)) {
						array_push($cookies,$cookie);
					}
					else {
						print "Bad domain: ".$cookie->domain()." vs ".$domain."<br>\n";
					}
				}
				else {
					print "Bad path: ".$cookie->path()." vs ".$path."<br>\n";
				}
			}
			return $cookies;
		}
		
		public function all() {
			return $this->_cookies;
		}
	}
?>