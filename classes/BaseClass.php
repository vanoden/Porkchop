<?php
	class BaseClass {
		private $_error;

		public function error($value = null) {
			if (isset($value)) {
				$this->_error = $value;
				app_log($value,'error');
			}
			return $this->_error;
		}

		public function SQLError($message) {
			$trace = debug_backtrace();
			$caller = $trace[1];
			$class = $caller['class'];
			$classname = str_replace('\\','::',$class);
			$method = $caller['function'];
			return $this->error("SQL Error in ".$classname."::".$method."(): ".$message);
		}
	}
