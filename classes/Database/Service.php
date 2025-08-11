<?php
	namespace Database;

	class Service Extends \BaseClass {
	
		private $_connection;
		private $_params = array();
		public $debug = 'log';
		private $_trace_level = 0;
		private $_query;

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->_connection = $GLOBALS['_database'];
		}

		/**
		 * Get a GLOBAL STATUS value from the database
		 * @param string $key
		 * @return string|NULL value
		 */
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

		/**
		 * Does Schema have specified table?
		 * @param string $table_name
		 * @return bool
		 */
		public function has_table($table_name) {
			$database = new \Database\Schema();
			$table = $database->table($table_name);
			return $table->load();
		}

		/**
		 * Prepare a Query for Execution
		 * @param mixed $query
		 */
		public function Prepare($query) {
			$this->_params = array();
			$this->_query = $query;
		}

		/** @method AddParam(value)
		 * Apply a Bind Parameter to the Query.  Parameters are applied to parameter marker in order of addition.
		 * @param mixed $value
		 * @return void
		 */
		public function AddParam($value) {
			array_push($this->_params,$value);
		}

		/** @method AddParams(values)
		 * Apply an array of Bind Parameters to the Query
		 * Loops through array and calls AddParam() method for each one in order
		 * @param mixed $values
		 * @return void
		 */
		public function AddParams($values) {
			if (is_array($values)) $this->_params = array_merge($this->_params,$values);
			else array_push($this->_params,$values);
		}

		/** @method resetParams()
		 * Reset the Bind Parameters.  Empties the stored array for re-use
		 * @return void
		 */
		public function resetParams() {
			$this->_params = array();
		}

		/** @method Parameters()
		 * Get the contents of the existing bind parameter array
		 * @return array
		 */
		public function Parameters() {
			return $this->_params;
		}

		/** @method Execute(query, parameters|null)
		 * Execute a Query against the database.  If provided, the parameters will be appended to the existing bind parameter array.
		 * @param mixed $query 
		 * @param array|null $bind_params 
		 * @return null|RecordSet 
		 */
		public function Execute($query = "",$bind_params = null) {
			if (!empty($query)) {
				$this->_query = $query;
			}
			if (is_array($bind_params)) $this->_params = array_merge($this->_params,$bind_params);
			if ($this->debug == 'log' && $this->_trace_level > 0) query_log($query,$this->_params,true);
			elseif ($this->debug == 'screen' && $this->_trace_level > 0) {
				print "<pre>$query</pre>";
				$count = 0;
				foreach ($this->_params as $param) {
					print "<pre>param[$count] = $param</pre>";
					$count ++;
				}
			}

			// Execute Query
			try {
				$recordSet = new \Database\RecordSet($this->_connection->Execute($this->_query,$this->_params));
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

		/** @method BeginTrans()
		 * Begin a Database SQL Transaction
		 * @return bool
		 */
		public function BeginTrans() {
			return $this->_connection->BeginTrans();
		}

		/** @method CommitTrans()
		 * Commit a Database SQL Transaction
		 * @return bool
		 */
		public function CommitTrans() {
			return $this->_connection->CommitTrans();
		}

		/** @method RollbackTrans()
		 * Rollback a Database SQL Transaction
		 * @return bool
		 */
		public function RollbackTrans() {
			return $this->_connection->RollbackTrans();
		}

		/** @method trace(level)
		 * Set the database debug level.  Must set debug to 'log' or 'screen'
		 * using debugOutput() method
		 * @param string $level - Optional, defaults to null
		 * @return int
		 */
		public function trace($level = null) {
			if (isset($level)) {
				$this->_trace_level = $level;
				if (empty($this->debug)) $this->debug == 'log';
			}
			return $this->_trace_level;
		}

		/** @method debugOutput(string)
		 * Set the output for trace logging to screen or log
		 * @param string $output (screen or log)
		 */
		public function debugOutput($output) {
			if (strtolower($output) != 'screen' && strtolower($output) != 'log') {
				$this->error("Invalid debug output type: $output");
				return;
			}
			$this->debug = strtolower($output);
		}

		/**
		 * Get the Error Message from the database
		 * @return string 
		 */
		public function ErrorMsg() {
			return $this->_connection->ErrorMsg();
		}

		/**
		 * Override the BaseClass error method to expose ADODB ErrorMsg
		 * @param string $message 
		 * @param string $caller 
		 * @return mixed 
		 */
		public function error($message = "", $caller = "") {
			$this->_error = $message;
			if (empty($this->_error) && !empty($this->ErrorMsg())) $this->_error = $this->ErrorMsg();
			return $this->_error;
		}

		/**
		 * Get the ID of the last inserted record
		 * @return int 
		 */
		public function Insert_ID() {
			return $this->_connection->Insert_ID();
		}

		/**
		 * Get the number of rows affected by the last query
		 * @return int 
		 */
		public function affected_rows() {
			return $this->_connection->Affected_Rows();
		}

		/**
		 * Get the MySQL Database Version
		 * @return int 
		 */
		public function version() {
			// Put Query to Get Version Here
			return $GLOBALS['_database']->_connectionID->server_info;
		}

		/**
		 * Does the database support the PASSWORD() function?
		 * @return bool 
		 */
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
