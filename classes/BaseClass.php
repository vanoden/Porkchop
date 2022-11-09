<?php
	class BaseClass {
		protected $_error;
		protected $_exists = false;
		protected $_cached = false;
		protected $_statii = array();

		public function error($value = null) {
			if (isset($value)) {
				$this->_error = $value;
				app_log($value,'error');
			}
			return $this->_error;
		}

		public function SQLError($message = '') {
			if (empty($message)) $message = $GLOBALS['_database']->ErrorMsg();
			$trace = debug_backtrace();
			$caller = $trace[1];
			$class = $caller['class'];
			$classname = str_replace('\\','::',$class);
			$method = $caller['function'];
			return $this->error("SQL Error in ".$classname."::".$method."(): ".$message);
		}

		public function _addStatus($param) {
			if (is_array($param)) $this->_statii = array_merge($this->_statii,$param);
			else array_push($this->_statii,$param);
		}

		public function clearError() {
			$this->_error = null;
		}

		public function exists($exists = null) {
			if (is_bool($exists)) $this->_exists = $exists;
			return $this->_exists;
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
