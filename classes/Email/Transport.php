<?php
	namespace Email;

	class Transport {
		private $_provider = 'smtp';

        public function __construct() {}
		
		public function Create($parameters = array()) {
			
			if (isset($parameters['provider'])) {
    			$provider = $parameters['provider'];
			} else {
			   $parameters['provider'] = 'SMTP';
			}
			
			if ($parameters['provider'] == 'Proxy') {
				return new \Email\Transport\Proxy($parameters);
			}
			elseif ($parameters['provider'] == 'Queue') {
				return new \Email\Transport\Queue($parameters);
			}
			else {
				app_log("Invalid Email Transport",'error');
				return null;
			}
		}

		public function error() {
			return $this->_error;
		}
	}
