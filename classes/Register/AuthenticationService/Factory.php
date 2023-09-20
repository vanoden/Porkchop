<?php
	namespace Register\AuthenticationService;

	class Factory Extends \BaseClass {
		public function service($auth_method,$options = array()) {
			if (preg_match('/^ldap\/(\w+)$/',$auth_method,$matches)) {
				$options['domain'] = $matches[1];
				return new \Register\AuthenticationService\LDAP($options);
			}
			else return new \Register\AuthenticationService\Local($options);
		}
	}
