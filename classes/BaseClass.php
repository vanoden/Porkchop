<?php
	class BaseClass {
		protected $_tableName;
		protected $_error;
		protected $_exists = false;
		protected $_cached = false;
		protected $_statii = array();
		protected $_cacheKeyPrefix;

		public function error($value = null,$caller = null) {
			if (isset($value)) {
				if (!isset($caller)) {
					$trace = debug_backtrace();
					$caller = $trace[1];
				}
				$class = $caller['class'];
				$classname = str_replace('\\','::',$class);
				$method = $caller['function'];
				$this->_error = $classname."::".$method."(): ".$value;
				app_log($this->_error,'error');
			}
			return $this->_error;
		}

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

		public function _addStatus($param) {
			if (is_array($param)) $this->_statii = array_merge($this->_statii,$param);
			else array_push($this->_statii,$param);
		}

		public function statii() {
			return $this->_statii;
		}

		public function clearError() {
			$this->_error = null;
		}

		public function exists($exists = null) {
			if (is_bool($exists)) $this->_exists = $exists;
			if (is_numeric($this->id) && $this->id > 0) return true;
			return $this->_exists;
		}

		public function cache() {
			if (!empty($this->_cacheKeyPrefix) && !empty($this->id)) {
		        // Bust Cache
		        $cache_key = $this->_cacheKeyPrefix."[" . $this->id . "]";
		        return new \Cache\Item($GLOBALS['_CACHE_'], $cache_key);
			}
			return null;
		}

		public function cached($cached = null) {
			if (is_bool($cached)) $this->_cached = $cached;
			return $this->_cached;
		}

		public function validCode($string) {
			if (preg_match('/^\w[\w\-\.\_\s]+$/',$string)) return true;
			else return false;
		}

		public function validName($string) {
			if (preg_match('/\w[\w\-\.\_\s\,\!\?\(\)]*$/',$string)) return true;
			else return false;
		}

		public function validStatus($string) {
			if (in_array($string,$this->_statii)) return true;
			else return false;
		}
	}
