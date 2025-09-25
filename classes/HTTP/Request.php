<?php
	namespace HTTP;

	class Request Extends \BaseClass {
		public $module;						// Porkchop Module
		public $view;						// View within the module
		public $index;						// Index within the view
		public $query_vars_array = array();	// Query variables
		public $client_ip;					// Client IP address
		public $user_agent;					// User-Agent string
		public $timer;						// Request timer
		public $url;						// Full request URL
		public $query_vars;					// Query variables
		private $_protocol;
		private $_method = 'GET';
		private $_host;
		private $_port = 80;
		private $_body;
		private $_uri = '/';
		private $_query_string = '';
		private $_parameters = array();
		private $_headers = array();

		/** @constructor
		 * Initializes the HTTP request
		 * @param array $parameters Optional parameters to set initial values for host, method, body, and uri
		 */
		public function __construct($parameters = array()) {
			$this->timer = microtime();
			if (isset($parameters['host'])) $this->host($parameters['host']);
			if (isset($parameters['method'])) $this->method($parameters['method']);
			if (isset($parameters['body'])) $this->body($parameters['body']);
			if (isset($parameters['uri'])) $this->uri($parameters['uri']);
			$this->_headers = array(
				"User-Agent" => $_SERVER['HTTP_USER_AGENT'] ?? 'Porkchop/1.0',
				"Accept" => "*/*"
			);
		}

		/** @method refererURI()
		 * Returns the referer URI from the HTTP request headers
		 * @return string|null The referer URI or null if not available
		 */
		public function refererURI() {
			app_log("Referer: ".$_SERVER['HTTP_REFERER']);
			if (preg_match('/^https?\:\/\/[\w\-\.]+(\/.*)/',$_SERVER['HTTP_REFERER'],$matches)) return $matches[1];
			return null;
		}

		/** @method addHeader(string $key, string $value)
		 * Adds a header to the request
		 * @param string $key The header key
		 * @param string $value The header value
		 */
		public function addHeader($key,$value) {
			$this->_headers[$key] = $value;
		}

		/** @method contentType(type = null)
		 * Sets or gets the content type for the request
		 * @param string $type The content type to set, or null to get the current content type
		 * @return string The current content type
		 */
		public function contentType($type = null) {
			if (isset($type)) $this->_headers['Content-Type'] = $type;
			return $this->_headers['Content-Type'] ?? null;
		}

		/** @method addParam(string $key, string $value)
		 * Adds a parameter to the request
		 * @param string $key The parameter key
		 * @param string $value The parameter value
		 */
		public function addParam($key,$value) {
			$this->_parameters[$key] = $value;
		}

		/** @method host(string $host = null)
		 * Sets or gets the host for the request
		 * @param string $host The host to set, or null to get the current host
		 * @return string The current host
		 */
		public function host($host = null) {
			if (isset($host)) $this->_host = $host;
			return $this->_host;
		}

		/** @method uri(string $uri = null)
		 * Sets or gets the URI for the request
		 * @param string $uri The URI to set, or null to get the current URI
		 * @return string The current URI
		 */
		public function uri($uri = null) {
			if (isset($uri)) $this->_uri = $uri;
			return $this->_uri;
		}

		/** @method url(string $url = null)
		 * Sets or gets the full URL for the request
		 * @param string $url The URL to set, or null to get the current URL
		 * @return string The current URL
		 */
		public function url($url = null) {
			if (isset($url)) {
				if (preg_match('/^(https?)\:\/\/([\w\.\-]+)\:(\d+)(\/[^\?]*)\?(.*)$/',$url,$matches)) {
					$this->_protocol = $matches[1];
					$this->_host = $matches[2];
					$this->_port = $matches[3];
					$this->_uri = $matches[4];
					$this->_query_string = $matches[5];
				}
				elseif (preg_match('/^(https?)\:\/\/([\w\.\-]+)\:(\d+)(\/[^\?]*)$/',$url,$matches)) {
					$this->_protocol = $matches[1];
					$this->_host = $matches[2];
					$this->_port = $matches[3];
					$this->_uri = $matches[4];
					$this->_query_string = null;
				}
				elseif (preg_match('/^(https?)\:\/\/([\w\.\-]+)(\/[^\?]*)\?(.*)$/',$url,$matches)) {
					$this->_protocol = $matches[1];
					$this->_host = $matches[2];
					$this->_port = 80;
					if ($this->_protocol == 'https') $this->_port = "443";
					$this->_uri = $matches[3];
					$this->_query_string = $matches[4];
				}
				elseif (preg_match('/^(https?)\:\/\/([\w\.\-]+)(\/[^\?]*)$/',$url,$matches)) {
					$this->_protocol = $matches[1];
					$this->_host = $matches[2];
					$this->_port = 80;
					if ($this->_protocol == 'https') $this->_port = "443";
					$this->_uri = $matches[3];
					$this->_query_string = null;
				}
				else {
					$this->error("Invalid url '".$url."'");
				}
			}
			$url = $this->_protocol."://".$this->_host;
			if ($this->_port != 80) $url.":".$this->_port.$this->url;
			$url .= $this->_uri;
			if ($this->_query_string) $url .= "?".$this->_query_string;
			return $url;
		}

		/** @method serialize(array $parameters = array())
		 * Serializes the request into a string format
		 * @param array $parameters Optional parameters to set host, method, body, and uri
		 * @return string|null The serialized request string or null on error
		 */
		public function serialize($parameters = array()) {
			$this->clearError();

			if (!empty($parameters['host'])) $this->_host = $parameters['host'];
			if (!empty($parameters['method'])) $this->_method = $parameters['method'];
			if (!empty($parameters['body'])) $this->_body = $parameters['body'];
			if (!empty($parameters['uri'])) $this->_uri = $parameters['uri'];

			if (count($this->_parameters)) {
				$paramArray = array();
				foreach($this->_parameters as $key => $value) array_push($paramArray,"$key=$value");
				$this->_body = join('&',$paramArray);
			}

			if (!isset($this->_host)) {
				$this->error("Host not defined");
				return null;
			}
			if (!isset($this->_uri)) {
				$this->error("Path not defined");
				return null;
			}

			$this->_method = strtoupper($this->_method);
			if (preg_match('/^(GET|PUT|POST|OPTIONS|HEAD)$/',$this->_method)) {
				# We're All Good
			}
			elseif(isset($this->_method)) {
				$this->error("Invalid HTTP method '".$this->_method."'");
				return null;
			}
			elseif (strlen($this->_body)) {
				$this->_method = 'POST';
				$this->contentType("application/x-www-form-urlencoded");
			}
			else {
				$this->_method = 'GET';
			}
			
			if (! isset($this->_headers['Content-Type'])) {
				if ($this->_method == 'POST') {
					$this->contentType("application/x-www-form-urlencoded");
				}
			}

			$string = $this->_method." ".$this->url()." HTTP/1.1\r\n";
			$string .= "Host: ".$this->_host."\r\n";

			foreach ($this->_headers as $header => $value) {
				$string .= $header.": ".$value."\r\n";
			}
			if (strlen($this->_body)) $string .= "Content-Length: ".strlen($this->_body)."\r\n";
			$string .= "\r\n";
			$string .= $this->_body;

			return $string;
		}

		/** @method deconstruct()
		 * Deconstructs the HTTP request from the server variables
		 * Parses the URI, identifies module, view, index, and query variables
		 */
		public function deconstruct() {
			app_log("REQUEST: ".$_SERVER['REQUEST_URI'],'debug',__FILE__,__LINE__);
			# Store User Agent
			$this->user_agent = $_SERVER['HTTP_USER_AGENT'];

			# Strip Path from URI
			$this->_uri = preg_replace('@^'.PATH.'@','',$_SERVER['REQUEST_URI']);

			# Decode URI
			$this->_uri = urldecode($this->_uri);

			# Parse Query String
			if ($this->_uri == "/") {
				// Home Page
				$this->module = "content";
				$this->view = "index";
				$this->index = $GLOBALS['_config']->site->default_index;
			}
			elseif ($this->_uri == "/sitemap.xml") {
				$matches = [];
				$this->module = "site";
				$this->view = "map_xml";
			}
			elseif (preg_match('/api\/([\w\-\_]+)\/([\w\-\_]+)\/([\w\-\_]+)/',$this->_uri,$matches)) {
				// API URIs
				$this->module = $matches[1];
				$this->view = "api";
				$_REQUEST['method'] = $matches[2];
				$_REQUEST['code'] = $matches[3];
			}
			elseif (preg_match('/api\/([\w\-\_]+)\/([\w\-\_]+)\/?([\w\-\_]*)/',$this->_uri,$matches)) {
				// API URIs
				$this->module = $matches[1];
				$this->view = "api";
				$_REQUEST['method'] = $matches[2];
				$this->query_vars = $matches[3];
			}
			elseif (preg_match('/api\/([\w\-\_]+)\/?/',$this->_uri,$matches)) {
				// API URIs
				$this->module = $matches[1];
				$this->view = "api";
			}
			elseif (preg_match('/^\/\_(\w[\w\-\_]*)\/(\w[\w\-\_]*)\/*(.+)*$/',$this->_uri,$matches)) {
				// Full Porkchop URIs
				// Special handling for content module
				if ($matches[1] == 'content') {
					$this->module = $matches[1];
					$this->view = 'index';  // Content pages always use 'index' view
					$this->index = $matches[2];  // The second part becomes the index
				} else {
					$this->module = $matches[1];
					$this->view = $matches[2];
				}
			}
			elseif (preg_match('/^\/([\w\-\_]*)$/',$this->_uri,$matches)) {
				// Short CMS URIs
				if (empty($matches[1])) {
					// 'Home' Page
					$this->module = "content";
					$this->view = "index";
				}
				elseif (! file_exists(HTML."/".$matches[1])) {
					// No Matching Static File, CMS Request
					$this->module = "content";
					$this->view = "index";
					$this->index = $matches[1];
				}
			}
			else {
				// Static Content
				$this->module = "static";
				$this->view = preg_replace('/^\//','',$this->_uri);
			}

			# Identify module, view and index
			if ($this->module == "content") {
				if (isset($this->view)) {
					// Nothing More To Do
				}
				elseif ($matches[2] == 'api') {
					$this->view = 'api';
					$this->query_vars = $matches[3];
				}
				elseif (isset($matches[3])) {
					$this->query_vars = $matches[2]."/".$matches[3];
					$this->view = "index";
					$this->index = $matches[2];
				}
				else {
					$this->query_vars = $matches[2]."/";
					$this->view = "index";
					$this->index = $matches[2];
				}
				if (! isset($this->index)) $this->index = '';
			}
			elseif ($this->module == "static") {
				$this->query_vars = '';
				$this->index = '';
			}
			elseif (! $this->module) {
				$this->module = 'content';
				$this->view = 'index';
				$this->query_vars = $this->_uri;
				$this->index = '';
			}
			elseif ($this->view == 'api') {
				// Nothing More To Do
			}
			else {
				$this->index = '';
				if (count($matches) > 2) $this->view = $matches[2];
				if (count($matches) > 3) $this->query_vars = $matches[3];
				else $this->query_vars = '';
			}

			app_log("Request: ".$this->module."::".$this->view."::".$this->index,'debug',__FILE__,__LINE__);

			# Parse Remainder of Query String into Array
			$parsed_vars = preg_split("@/@",$this->query_vars ?? '');
			$qv_counter = 0;
			foreach ($parsed_vars as $element) {
				if (preg_match('/(.*)\?([\w\.\_\-]+\=.*)/',$element,$matches)) {
					$element = $matches[1];
					$rest = $matches[2];
					$this->query_vars_array[$qv_counter] = $element;
					$qv_counter ++;
					$elements = preg_split('/&/',$rest);
					foreach($elements as $element) {
						if (preg_match("/=/",$element)) {
							list($label,$value) = preg_split("/=/",$element);
							$this->query_vars_array[$label] = $value;
							$this->_parameters[$label] = $value;
						}
					}
				}
				$this->query_vars_array[$qv_counter] = $element;
				if (preg_match("/=/",$element)) {
					list($label,$value) = preg_split("/=/",$element);
					$this->query_vars_array[$label] = $value;
					$this->_parameters[$label] = $value;
				}
				$qv_counter ++;
			}
			$this->_body = file_get_contents('php://input');
		}

		/** @method body(string $body = null)
		 * Sets or gets the body of the request
		 * @param string $body The body to set, or null to get the current body
		 * @return string The current body
		 */
		public function body($content = null): string|null {
			if (isset($content)) {
				$this->_body = $content;
			}
			return $this->_body;
		}

		/** @method method(string $method = null)
		 * Sets or gets the HTTP method for the request
		 * @param string $method The method to set, or null to get the current method
		 * @return string The current HTTP method
		 */
		public function method($method = null): string|null {
			if (isset($method)) {
				$method = strtoupper($method);
				if (in_array($method,array('POST','GET','HEAD','OPTIONS'))) {
					$this->_method = $method;
				}
				else {
					$this->error("Invalid method");
					return null;
				}
			}

			return $this->_method;
		}

		/** @method parameters()
		 * Returns all parameters from the request
		 * @return array An associative array of parameters
		 */
		public function parameters() {
			foreach ($_POST as $label => $value) $this->_parameters[$label] = $value;
			return $this->_parameters;
		}

		/** @method parameter(string $key)
		 * Returns a specific parameter from the request
		 * @param string $key The key of the parameter to retrieve
		 * @return mixed The value of the parameter or null if not found
		 */
		public function parameter($key) {
			return $this->_parameters[$key] ?? null;
		}

		/** @method riskLevel()
		 * Calculates the risk level of the request based on various factors
		 * @return int The calculated risk level
		 */
        public function riskLevel() {
            $risk_level = 0;
            $uri = $this->_uri;
            if (preg_match('/^([\/\w\-\_\.]+)\?(.*)$/',$uri,$matches)) {
                $uri = $matches[1];
                $query_string = $matches[2];
            }
            elseif (preg_match('/^([\/\w\-\_\.]+)$/',$uri,$matches)) {
                $uri = $matches[1];
                $query_string = null;
            }
            else {
                # Unparseable URI
                app_log("WAF RULE: unparseable URI",'trace2');
                $risk_level += 80;
                $uri = null;
            }

            if ($this->module && $this->module != "content") {
                # Proper Porkchop URI
                app_log("WAF RULE: porkchop URI",'trace2');
                $risk_level -= 50;
            }
            elseif (preg_match('/([\w\-\.\_]+)\.([\w\-\_]+)/',$uri,$matches)) {
                $extension = $matches[2];
                $basename = $matches[1].".".$matches[2];
                if ($basename == "wplogin.php") {
                    # We're not WordPress
                    app_log("WAF RULE: wplogin",'trace2');
                    $risk_level += 100;
                }
                elseif (preg_match('/^(php|asp|aspx|jsp|jspx|exe|cgi|pl)$/i',$extension)) {
                    # Porkchop doesn't use engines based on extension
                    app_log("WAF RULE: extension",'trace2');
                    $risk_level += 100;
                }
                elseif (preg_match('/^vendor\/phpunit/',$uri)) {
                    # No php unit test here
                    app_log("WAF RULE: phpunit",'trace2');
                    $risk_level += 100;
                }
                elseif (preg_match('/^\./',$uri)) {
                    # Hidden files or backref
                    app_log("WAF RULE: hidden file",'trace2');
                    $risk_level += 100;
                }
            }

            $contents = array(
                preg_replace('/[\/\\\.\-\_\%\'\"\0\=]/','',$query_string ?? ''),
                preg_replace('/[\/\\\.\-\_\%\'\"\0\=]/','',$this->_body ?? '')
            );

            foreach ($contents as $content) {
                if (preg_match('/select.+from/i',$content)) {
                    # SQL Injection Attempt
                    app_log("WAF RULE: SQL Inject",'trace2');
                    $risk_level += 100;
                }
                elseif (preg_match('/insert.+into/i',$content)) {
                    # SQL Injection Attempt
                    app_log("WAF RULE: SQL Inject",'trace2');
                    $risk_level += 100;
                }
                elseif (preg_match('/update.+set/i',$content)) {
                    # SQL Injection Attempt
                    app_log("WAF RULE: SQL Inject",'trace2');
                    $risk_level += 100;
                }
                elseif (preg_match('/replace.+into/i',$content)) {
                    # SQL Injection Attempt
                    app_log("WAF RULE: SQL Inject",'trace2');
                    $risk_level += 100;
                }
                elseif (preg_match('/delete.+from/i',$content)) {
                    # SQL Injection Attempt
                    app_log("WAF RULE: SQL Inject",'trace2');
                    $risk_level += 100;
                }
                elseif (preg_match('/drop.+database/i',$content)) {
                    # SQL Injection Attempt
                    app_log("WAF RULE: SQL Inject",'trace2');
                    $risk_level += 100;
                }
                elseif (preg_match('/alter.+table/i',$content)) {
                    # SQL Injection Attempt
                    app_log("WAF RULE: SQL Inject",'trace2');
                    $risk_level += 100;
                }
            }
            return $risk_level;
        }
	}
