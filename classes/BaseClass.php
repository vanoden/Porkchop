<?php
	class BaseClass {
		// Error Message
		private $_error;

		// Was data found in db or cache
		private $_exists = false;

		// Did data come from cache?
		private $_cached = false;

		// Possible statuses in enum status table for validation (where applicable)
		protected $_statii = array();

		// Possible types in enum type table for validation (where applicable)
		protected $_types = array();

		// Name of Table Holding This Class
		protected $_tableName;

		// Name for Unique Surrogate Key Column (for get)
		protected $_tableUKColumn = 'code';

		// Name for Cache Key - id appended in square brackets
		protected $_cacheKeyPrefix;

		/********************************************/
		/* Get Object Record Using Unique Code		*/
		/********************************************/
		public function ukExists(string $code): bool {
			// Clear Errors
			$this->clearError();

			// Initialize Services
			$database = new \Database\Service();

			// Prepare Query
			$get_object_query = "
				SELECT	id
				FROM	`".$this->_tableName."`
				WHERE	`".$this->_tableUKName."` = ?";

			// Bind Code to Query
			$database->AddParam($code);

			// Execute Query
			$rs = $database->Execute($get_object_code);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// Fetch Results
			list($id) = $rs->FetchRow();
			if (is_numeric($id) && $id > 0) return true;
			else {
				$this->error("unique key conflict");
				return false;
			}
		}

		/********************************************/
		/* Get Object Record Using Unique Code		*/
		/********************************************/
		public function get(string $code): bool {
			// Clear Errors
			$this->clearError();

			// Initialize Services
			$database = new \Database\Service();

			// Prepare Query
			$get_object_query = "
				SELECT	id
				FROM	`".$this->_tableName."`
				WHERE	`".$this->_tableUKColumn."` = ?";

			// Bind Code to Query
			$database->AddParam($code);

			// Execute Query
			$rs = $database->Execute($get_object_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// Fetch Results
			list($id) = $rs->FetchRow();
			if (is_numeric($id) && $id > 0) {
				$this->id = $id;
				return $this->details();
			}
			else {
				$this->error("Record not found");
				return false;
			}
		}

		public function delete(): bool {
			// Clear Errors
			$this->clearError();

			// Initialize Services
			$database = new \Database\Service();
			$cache = $this->cache();

			$cache->delete();
	
			// Prepare Query
			$delete_object_query = "
				DELETE
				FROM	`".$this->_tableName."`
				WHERE	id = ?";

			// Bind ID to Query
			$database->AddParam($this->id);

			// Execute Query
			$rs = $database->Execute($delete_object_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
		}
	
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

		public function clearCache() {
			$cache = $this->cache();
			if ($cache) $cache->delete();
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

		public function validType($string) {
			if (in_array($string,$this->_types)) return true;
			else return false;
		}
	}
