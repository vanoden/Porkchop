<?php
	namespace Email;

	class Transport {
		private $_provider = 'smtp';
		private $_error;

        public function __construct() {}
		
		public function Create($parameters = array()) {
			
			if (isset($parameters['provider'])) {
    			$provider = $parameters['provider'];
			} else {
			   $parameters['provider'] = 'SMTP';
			}
			
			if ($parameters['provider'] == 'Proxy') {
				return new \Email\Transport\Proxy($parameters);
			} else {
				return null;
			}
		}

		public function error() {
			return "Invalid Email Transport";
		}
	}
