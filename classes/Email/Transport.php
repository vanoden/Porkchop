<?
	namespace Email;

	class Transport {
		private $_provider = 'smtp';
		private $_error;

		public function Create($parameters = array()) {
			if (isset($parameters['provider'])) $provider = $parameters['provider'];
			else $parameters['provider'] = 'SMTP';
			if ($parameters['provider'] == 'Proxy') {
				return new \Email\Transport\Proxy($parameters);
			}
			else {
				$this->_error = "Invalid Transport";
				return null;
			}
		}
		public function error() {
			return $this->_error;
		}
	}
?>