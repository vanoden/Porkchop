<?
	namespace HTTP;

	class Request {
		public $module;
		public $view;
		public $index;
		public $query_vars_array = array();
		public $client_ip;
		public $user_agent;

		public function __construct() {
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
		
		public function parameters() {
			foreach ($_POST as $label => $value) {
				$this->parameters[$label] = $value;
			}
			return $this->parameters;
		}
	}
?>