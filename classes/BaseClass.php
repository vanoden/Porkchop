<?php
	class BaseClass {
	
		// Error Message
		protected $_error;

		private $_warning;

		// Possible statuses in enum status table for validation (where applicable)
		protected $_statii = array();

		// Possible types in enum type table for validation (where applicable)
		protected $_types = array();

		/********************************************/
		/* Reusable Error Handling Routines			*/
		/********************************************/
		public function error($value = null,$caller = null) {
			if (isset($value)) {
				if (!isset($caller)) {
					$trace = debug_backtrace();
					$caller = $trace[1];
				}
				$class = $caller['class'];
				$classname = str_replace('\\','::',$class);
				$method = $caller['function'];
				$this->_error = $value;
				app_log(get_called_class()."::".$method."(): ".$this->_error,'error');
			}
			return $this->_error;
		}

		/****************************************/
		/* Recognize Special Error Types 		*/
		/****************************************/
		public function errorType() {
			if (empty($this->_error)) return null;
			if (preg_match('/MySQL server has gone away/',$this->_error)) return 'MySQL Unavailable';
			if (preg_match('/Lost connection to MySQL server/',$this->_error)) return 'MySQL Unavailable';
			if (preg_match('/No database selected/',$this->_error)) return 'MySQL Unavailable';
			if (preg_match('/Table \'(\w+)\' doesn\'t exist/',$this->_error,$matches)) return 'MySQL Query Errord';
			if (preg_match('/Unknown column \'(\w+)\' in \'(\w+)\'/',$this->_error,$matches)) return 'MySQL Query Error';
			if (preg_match('/Duplicate entry \'(\w+)\' for key \'(\w+)\'/',$this->_error,$matches)) return 'MySQL Query Error';
			return 'Common';
		}

		public function warn($value = null, $caller = null) {
			if (isset($value)) {
				if (!isset($caller)) {
					$trace = debug_backtrace();
					$caller = $trace[1];
				}
				$class = $caller['class'];
				$classname = str_replace('\\','::',$class);
				$method = $caller['function'];
				$this->_warning = $value;
				app_log(get_called_class()."::".$method."(): ".$this->_warning,'warn');
			}
			return $this->_warning;
		}

		public function _objectName() {
			if (!isset($caller)) {
				$trace = debug_backtrace();
				$caller = $trace[2];
			}

			$class = isset($caller['class']) ? $caller['class'] : null;
			if (preg_match('/(\w[\w\_]*)$/',$class,$matches)) $classname = $matches[1];
			else $classname = "Object";
			return $classname;
		}

		/********************************************/
		/* SQL Errors - Identified and Formatted	*/
		/* for filtering and reporting				*/
		/********************************************/
		public function SQLError($message = '', $query = null, $bind_params = null) {
			if (empty($message)) $message = $GLOBALS['_database']->ErrorMsg();
			$trace = debug_backtrace();
			$caller = $trace[1];
			$class = $caller['class'];
			$classname = str_replace('\\','::',$class);
			$method = $caller['function'];
			if (!empty($query)) query_log($query,$bind_params,true);
			return $this->error("SQL Error in ".$classname."::".$method."(): ".$message,$caller);
		}

		public function clearError() {
			$this->_error = null;
		}

		public function types() {
			return $this->_types;
		}

		public function statuses() {
			return $this->_statii;
		}

		/********************************************/
		/* Reusable Validation Routines				*/
		/********************************************/
		// Standard 'code' field validation
		public function validCode($string): bool {
			return (preg_match('/^\w[\w\-\.\_\s]*$/',$string));
		}

		// Standard 'name' field validation
		public function validName($string): bool {
			return (preg_match('/\w[\w\-\.\_\s\,\!\?\(\)]*$/',$string));
		}

		// Standard 'status' field validation
		public function validStatus($string): bool {
			return (in_array($string,$this->_statii));
		}

		// Standard 'type' field validation
		public function validType($string): bool {
			return (in_array($string,$this->_types));
		}

        // Standard 'search' field validation
        public function validSearch($string): bool {
            return (preg_match('/^[\*\w\-\_\.\s]*$/',$string));
        }

		// Validate an Address Line
		public function validAddressLine($string): bool {
			return (preg_match('/^[\w? :.-|\'\)]+$/',urldecode($string)));
		}

		// Validate a City Name
		public function validCity($string): bool {
			return (preg_match('/^[\w? :.-|\'\)]+$/',urldecode($string)));
		}

		// Validate a Hostname
		public function validHostname($string): bool {
			return (preg_match('/^\w[\w\.\-]*$/', $string));
		}

		// Validate a URL
		public function validURL($string): bool {
			define('URL_FORMAT', 
			'/^(https?):\/\/'.                                         // protocol
			'(([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+'.         // username
			'(:([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+)?'.      // password
			'@)?(?#'.                                                  // auth requires @
			')((([a-z0-9]\.|[a-z0-9][a-z0-9-]*[a-z0-9]\.)*'.                      // domain segments AND
			'[a-z][a-z0-9-]*[a-z0-9]'.                                 // top level domain  OR
			'|((\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])\.){3}'.
			'(\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])'.                 // IP address
			')(:\d+)?'.                                                // port
			')(((\/+([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)*'. // path
			'(\?([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)'.      // query string
			'?)?)?'.                                                   // path and query string optional
			'(#([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)?'.      // fragment
			'$/i');
			return (preg_match(URL_FORMAT, $string));
		}

		public function safeString($string): bool {
			$string = urldecode($string);
			if (preg_match('/(&#*\w+)[\x00-\x20]+;/u',$string)) return false;
			if (preg_match('/(&#x*[0-9A-F]+);*/iu',$string)) return false;

			if (preg_match('/(\<|&lt;)\s*(script|iframe|object|embed|applet|meta|link)/i',$string)) return false;

			// javascript protocol
			if (preg_match('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu',$string)) return false;
			// vbscript protocol
			if (preg_match('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu',$string)) return false;
			// mozbinding protocol
			if (preg_match('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u',$string)) return false;
			// Attribute starting with "on" or "data" or "xmlns"
			if (preg_match('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns|data)[^>]*+>#iu',$string)) return false;
			// Non alpha-numeric characters in attribute names
			if (preg_match('/[^\w\"\']=/',$string)) return false;
			// javascript: in inline events
			if (preg_match('/alert\(/i',$string)) return false;

			return (preg_match('/^[^\%\<\>]+$/',$string));
		}

		public function _clone() {
			$obj = new \stdClass();
			foreach (get_object_vars($this) as $key => $value) {
				if (preg_match('/^_/',$key)) continue;
				$obj->$key = $value;
			}
			return $obj;
		}

		public function getError() {
			return $this->_error;
        }		
	}
