<?
	namespace HTTP;

	class Request {
		public $module;
		public $view;
		public $index;
		public $query_vars_array = array();
		public $client_ip;
		public $user_agent;
		private $_method;
		private $_host;
		private $_body;
		private $_uri;
		private $_content_type;
		private $_error;
		private $_parameters = array();
		private $_headers = array();

		public function __construct() {
		}

		public function addParam($key,$value) {
			$this->_parameters[$key] = $value;
		}
		
		public function host($host = null) {
			if (isset($host)) {
				$this->_host = $host;
			}
			return $this->_host;
		}

		public function uri($uri = null) {
			if (isset($uri)) {
				$this->_uri = $uri;
			}
			return $this->_uri;
		}
		public function serialize($parameters = array()) {
			$this->_error = null;

			if (isset($parameters['host'])) $this->_host = $parameters['host'];
			if (isset($parameters['method'])) $this->_method = $parameters['method'];
			if (isset($parameters['body'])) $this->_body = $parameters['body'];
			if (isset($parameters['uri'])) $this->_uri = $parameters['uri'];
			if (count($this->_parameters)) {
				$paramArray = array();
				foreach($this->_parameters as $key => $value) {
					array_push($paramArray,"$key=$value");
				}
				$this->_body = join('&',$paramArray);
			}

			if (!isset($this->_host)) {
				$this->_error = "Host not defined";
				return null;
			}
			if (!isset($this->_uri)) {
				$this->_error = "Path not defined";
				return null;
			}

			$this->_method = strtoupper($this->_method);
			if (in_array($this->_method,array('GET','POST','OPTIONS','HEAD'))) {
				# We're All Good
			}
			else if(isset($this->_method)) {
				$this->_error = "Invalid HTTP method";
				return null;
			}
			else if (strlen($this->_body)) {
				$this->_method = 'POST';
				$this->_content_type = "application/x-www-form-urlencoded";
			}
			else {
				$this->_method = 'GET';
			}
			
			if (! isset($this->_content_type)) {
				if ($this->_method == 'POST') {
					$this->_content_type = "application/x-www-form-urlencoded";
				}
			}
			$string = $this->_method." ".$this->_uri." HTTP/1.0\r\n";
			$string .= "Host: ".$this->_host."\r\n";
			if (isset($this->_content_type)) $string .= "Content-Type: ".$this->_content_type."\r\n";
			if (strlen($this->_body)) $string .= "Content-Length: ".strlen($this->_body)."\r\n";
			$string .= "\r\n";
			$string .= $this->_body;
			
			return $string;
		}

		public function deconstruct() {
			# Strip Path from URI
			$this->uri = preg_replace('@^'.PATH.'@','',$_SERVER['REQUEST_URI']);

			# Decode URI
			$this->uri = urldecode($this->uri);

			# Parse Query String
			if (preg_match("/^\/\_(\w+)\/(\w+)\/*(.+)*$/",$this->uri,$matches)) {
				$this->module = $matches[1];
			}

			# Identify module, view and index
			if ($this->module == "content") {
				if ($matches[2] == 'api') {
					$this->view = 'api';
					$this->query_vars = $matches[3];
				}
				elseif (isset($matches[3])) {
					$this->query_vars = $matches[2]."/".$matches[3];
					$this->view = "index";
				}
				else {
					$this->query_vars = $matches[2]."/";
					$this->view = "index";
				}
				$this->index = $matches[2];
				if (! isset($this->index)) $this->index = '';
			}
			elseif (! $this->module) {
				$this->module = 'content';
				$this->view = 'index';
				$this->query_vars = $this->uri;
				$this->index = '';
			}
			else {
				$this->index = '';
				if (count($matches) > 2) $this->view = $matches[2];
				if (count($matches) > 3) $this->query_vars = $matches[3];
				else $this->query_vars = '';
			}

			app_log("Request: ".$this->module."::".$this->view."::".$this->index,'debug',__FILE__,__LINE__);
			# Parse Remainder of Query String into Array
			$parsed_vars = preg_split("@/@",$this->query_vars);
			$qv_counter = 0;
			foreach ($parsed_vars as $element) {
				$this->query_vars_array[$qv_counter] = $element;
				if (preg_match("/=/",$element))
				{
					list($label,$value) = preg_split("/=/",$element);
					$this->query_vars_array[$label] = $value;
					$this->parameters[$label] = $value;
				}
				$qv_counter ++;
			}
			$this->body = file_get_contents('php://input');
		}
		
		public function body() {
			return $this->body;
		}
		
		public function method($method = null) {
			if (isset($method)) {
				$method = strtoupper($method);
				if (in_array($method,array('POST','GET','HEAD','OPTIONS'))) {
					$this->_method = $method;
				}
				else {
					$this->_error = "Invalid method";
					return null;
				}
			}

			return $this->_method;
		}
		public function parameters() {
			foreach ($_POST as $label => $value) {
				$this->parameters[$label] = $value;
			}
			return $this->parameters;
		}
		
		public function error() {
			return $this->_error;
		}
	}
?>