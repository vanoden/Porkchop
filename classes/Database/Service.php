<?php
	namespace Database;

	class Service Extends \BaseModel {
	
		private $_connection;
		private $_params = array();
		public $debug = 'log';
		private $_trace_level = 0;

		public function __construct() {
			$this->_connection = $GLOBALS['_database'];
		}

		public function global($key) {
			if (!preg_match('/^\w[\w\_]+\w$/',$key)) {
				$this->error("Invalid global key");
				return null;
			}
			$rs = $this->Execute("SHOW GLOBAL STATUS LIKE '".$key."'");
			list($label,$value) = $rs->FetchRow();
			if (! preg_match('/^[\w\.]+$/',$value)) {
				$this->error("Invalid global value");
				return null;
			}
			return $value;
		}

		public function AddParam($value) {
			array_push($this->_params,$value);
		}

		public function Parameters() {
			return $this->_params;
		}

		/**
		 * Execute a Query against the database
		 * @param mixed $query 
		 * @param mixed $bind_params 
		 * @return null|RecordSet 
		 */
		public function Execute($query,$bind_params = null) {

			if (is_array($bind_params)) $this->_params = array_merge($this->_params,$bind_params);
			if ($this->debug == 'log' && $this->_trace_level > 0) query_log($query,$this->_params,true);
			elseif ($this->debug == 'screen' && $this->_trace_level > 0) print "<pre>$query</pre>";

			// Execute Query
			try {
				$recordSet = new \Database\RecordSet($this->_connection->Execute($query,$this->_params));
			} catch (\mysqli_sql_exception $e) {
				$this->error($e->getMessage());
				$recordSet = null;
			}

			// Query Counter
			$execCounter = new \Site\Counter("database.sql_execute");
			$execCounter->increment();

			if ($this->_connection->ErrorMsg()) {
				error_log($this->_connection->ErrorMsg());
				$sql_error_counter = new \Site\Counter("database.sql_errors");
				$sql_error_counter->increment();
				return null;
			}

			return $recordSet;
		}

		public function trace($level = null) {
			if (isset($level)) {
				$this->_trace_level = $level;
				if (empty($this->debug)) $this->debug == 'log';
			}
			return $this->_trace_level;
		}

		public function ErrorMsg() {
			return $this->_connection->ErrorMsg();
		}

		public function Insert_ID() {
			return $this->_connection->Insert_ID();
		}

		public function version() {
			// Put Query to Get Version Here
			return $GLOBALS['_database']->_connectionID->server_info;
		}

		public function supports_password() {
			if ($this->version_compare('5.7.5',$this->version())) return false;
			return true;
		}

		public function version_compare($ver1,$ver2) {
			if (preg_match('/^(\d+)\.(\d+)\.(\d+)/',$ver1,$matches)) {
				$major1 = $matches[1];
				$minor1 = $matches[2];
				$patch1 = $matches[3];
			
				if (preg_match('/^(\d+)\.(\d+)\.(\d+)/',$ver2,$matches)) {
					$major2 = $matches[1];
					$minor2 = $matches[2];
					$patch2 = $matches[3];

					if ($major2 > $major1) return true;
					if ($major2 == $major1) {
						if ($minor2 > $minor1) return true;
						if ($minor2 == $minor1) {
							if ($patch2 >= $patch1) return true;
						}
					}
				}
			}
			return false;
		}
	}
