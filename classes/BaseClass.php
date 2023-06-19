<?php
	class BaseClass {
	
		// Error Message
		private $_error;

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
			$class = $caller['class'];
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

		/********************************************/
		/* Reusable Validation Routines				*/
		/********************************************/
		// Standard 'code' field validation
		public function validCode($string): bool {
			if (preg_match('/^\w[\w\-\.\_\s]*$/',$string)) return true;
			else return false;
		}

		// Standard 'name' field validation
		public function validName($string): bool {
			if (preg_match('/\w[\w\-\.\_\s\,\!\?\(\)]*$/',$string)) return true;
			else return false;
		}

		// Standard 'status' field validation
		public function validStatus($string): bool {
			if (in_array($string,$this->_statii)) return true;
			else return false;
		}

		// Standard 'type' field validation
		public function validType($string): bool {
			if (in_array($string,$this->_types)) return true;
			else return false;
		}
	}
