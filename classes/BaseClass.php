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

		// Name for Auto-Increment ID Column
		protected $_tableIDColumn = 'id';

	    protected $_fields = array();

		// Name for Cache Key - id appended in square brackets
		protected $_cacheKeyPrefix;

		public $id = 0;

		public function __construct($id = 0) {
			if (is_numeric($id) && $id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		/********************************************/
		/* Track Fields Updateable in Table			*/
		/********************************************/
		protected function _addFields($fields) {
			if (is_array($fields)) {
				foreach ($fields as $field) {
					array_push($this->_fields,$field);
				}
			}
		}

		/********************************************/
		/* Get Object Record Using Unique Code		*/
		/********************************************/
		protected function _ukExists(string $code): bool {
			// Clear Errors
			$this->clearError();

			// Initialize Services
			$database = new \Database\Service();

			// Prepare Query
			$get_object_query = "
				SELECT	`".$this->_tableIDColumn."`
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
			if (is_numeric($id) && $id > 0) return true;
			else {
				$this->error("unique key conflict");
				return false;
			}
		}

		// Polymorphism for Fun and Profit
		public function __call($name,$parameters) {
			if ($name == 'get') return $this->_getObject($parameters[0]);
		}

		/********************************************/
		/* Placeholder update function				*/
		/********************************************/
		public function update($parameters): bool {
			return $this->details();
		}

		/********************************************/
		/* Get Object Record Using Unique Code		*/
		/********************************************/
		public function _getObject(string $code): bool {
			// Clear Errors
			$this->clearError();

			if (gettype($this->_tableUKColumn) == 'NULL') {
				$trace = debug_backtrace()[1];
				$this->error("No surrogate key defined for ".get_class($this)." called from ".$trace['class']."::".$trace['function']." in ".$trace['file']." line ".$trace['line']);
				error_log($this->error());
				return false;
			}
			// Initialize Services
			$database = new \Database\Service();

			// Prepare Query
			$get_object_query = "
				SELECT	`".$this->_tableIDColumn."`
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
				$cls = get_called_class();
				$parts = explode("\\",$cls);
				$this->error($parts[1]." not found");
				return false;
			}
		}

		public function advancedGet() {
			return false;
		}

		// Load Object Attributes from Cache or Database
		public function details(): bool {
			$this->clearError();

			$database = new \Database\Service();
			$cache = $this->cache();
			if (!empty($this->_cacheKeyPrefix)) {
				$fromCache = $cache->get();
				if (isset($fromCache)) {
					foreach ($fromCache as $key => $value) {
						if (property_exists($this,$key)) $this->$key = $value;
					}
					$this->cached(true);
					$this->exists(true);
					return true;
				}
			}

			$get_object_query = "
				SELECT	*
				FROM	`$this->_tableName`
				WHERE	`$this->_tableIDColumn` = ?
			";
			$database->AddParam($this->id);
			$rs = $database->Execute($get_object_query);
			if (!$rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			$object = $rs->FetchNextObject(false);
			$column = $this->_tableIDColumn;
			if (is_object($object) && $object->$column > 0) {
				// Collect all attributes from response record
				foreach ($object as $key => $value) {
					if (property_exists($this,$key)) $this->$key = $value;
				}
				$this->exists(true);
				$this->cached(false);
				if (!empty($this->_cacheKeyPrefix)) $cache->set($object);
			}
			else {
				// Clear all attributes
				foreach ($this as $key => $value) {
					$this->$key = null;
				}
				$this->exists(false);
				$this->cached(false);
			}
			return true;
		}

		// Delete a record from the database using current ID
		public function delete(): bool {
			// Clear Errors
			$this->clearError();

			if (! $this->id) {
				$this->error("No current instance of this object");
				return false;
			}

			// Initialize Services
			$database = new \Database\Service();
			$cache = $this->cache();

			if (isset($cache)) $cache->delete();

			// Prepare Query
			$delete_object_query = "
				DELETE
				FROM	`".$this->_tableName."`
				WHERE	`".$this->_tableIDColumn."` = ?";

			// Bind ID to Query
			$database->AddParam($this->id);

			// Execute Query
			$rs = $database->Execute($delete_object_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			return true;
		}

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

		public function _addStatus($param) {
			if (is_array($param)) $this->_statii = array_merge($this->_statii,$param);
			else array_push($this->_statii,$param);
		}

		public function statii() {
			return $this->_statii;
		}

		public function exists($exists = null) {
			if (is_bool($exists)) $this->_exists = $exists;
			if (is_numeric($this->id) && $this->id > 0) return true;
			return $this->_exists;
		}

		/****************************************/
		/* Reusable Cache Methods				*/
		/****************************************/
		// Get Cache Object using _cacheKeyPrefix and current ID
		public function cache() {
			if (!empty($this->_cacheKeyPrefix) && !empty($this->id)) {
		        // Bust Cache
		        $cache_key = $this->_cacheKeyPrefix."[" . $this->id . "]";
		        return new \Cache\Item($GLOBALS['_CACHE_'], $cache_key);
			}
			return null;
		}

		// Clear Object from Cache
		public function clearCache() {
			$cache = $this->cache();
			if ($cache) $cache->delete();
		}

		// Don't check cache, just see if data came from cache!
		public function cached($cached = null) {
			if (is_bool($cached)) {
				if ($cached) $this->_cached = 1;
				else $this->_cached = 0;
			}
			elseif(is_numeric($cached)) {
				$this->_cached = $cached;
			}
			return $this->_cached;
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
?>