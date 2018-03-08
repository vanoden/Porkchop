<?
	namespace Email\Transport;
	require THIRD_PARTY.'/autoload.php';

	class Proxy {
		private $_error;
		private $_hostname;
		private $_username;
		private $_password;
		
		public function __construct($parameters = array()) {
			if (isset($parameters['hostname']) and ! $this->hostname($parameters['hostname'])) return null;
			if (isset($parameters['username']) and ! $this->username($parameters['username'])) return null;
			if (isset($parameters['password']) and ! $this->password($parameters['password'])) return null;
		}

		public function hostname($hostname = null) {
			if (isset($hostname)) $this->_hostname = $hostname;
			return $this->_hostname;
		}

		public function token($token = null) {
			if (isset($token)) $this->_token = $token;
			return $this->_token;
		}

		public function deliver($email) {
			$client = new \GuzzleHttp\Client();
			$response = $client->request('POST','http://'.$this->hostname().'/send.php', [
				'form_params' => [
					'token'		=> $this->token(),
					'to'		=> $email->to(),
					'from'		=> $email->from(),
					'subject'	=> $email->subject(),
					'body'		=> $email->body()
				]
			]);
			if ($response->getStatusCode() == 200) {
				$content = $response->getBody();
				if (preg_match('/^ERROR\:\s(.*)$/',$content,$matches)) {
					$this->_error = $matches[1];
					return 0;
				}
				$this->_result = "Sent";
				return 1;
			}
			else {
				$this->_result = "Failed";
				$this->_error = $response->getStatusCode().": ".$response->getReasonPhrase();
				return 0;
			}
		}

		public function result() {
			return $this->_result;
		}

		public function error() {
			return $this->_error;
		}
	}
?>