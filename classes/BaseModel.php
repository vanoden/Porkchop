<?php
	class BaseModel Extends \BaseClass {
	
		// Primary Key
		public $id = 0;

		// Was data found in db or cache
		private $_exists = false;

		// Did data come from cache?
		private $_cached = false;

		// Name of Table Holding This Class
		public $_tableName;

		// Name for Unique Surrogate Key Column (for get)
		protected $_tableUKColumn = 'code';

		// Name for Auto-Increment ID Column
		protected $_tableIDColumn = 'id';

		// Name for Software Incrementing Number Field
		public $_tableNumberColumn;

        // field names for columns in database tables
	    protected $_fields = array();

		// Name for Cache Key - id appended in square brackets
		protected $_cacheKeyPrefix;

		// Load object base on ID if given
		public function __construct($id = 0) {
			if (is_numeric($id) && $id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		// Polymorphism for Fun and Profit
		public function __call($name,$parameters) {
			if ($name == 'get' && count($parameters) == 2) $this->error("Too many parameters for 'get'");
			elseif ($name == 'get')  return $this->_getObject($parameters[0]);
			else {
				app_log("No function '$name' found",'warning');
				$this->error("Invalid method '$name'"); // for ".$this->objectName());
			}
		}

		public function _tableName(){
			return $this->_tableName;
		}
		public function _tableIDColumn() {
			return $this->_tableIDColumn;
		}

		/************************************************/
		/* Return List of Object Fields					*/
		/* Auto-populate if not provided by constructor	*/
		/************************************************/
		public function _fields() {
			if (count($this->_fields) < 1) {
				$properties = get_object_vars($this);
				foreach ($properties as $property => $stuff) {
					if (preg_match('/^_/',$property)) continue;
					array_push($this->_fields,$property);
				}
				return array_keys(get_object_vars($this));
			}
			return $this->_fields;
		}
        /**
         * update by params
         * 
         * @param array $parameters, name value pairs to update object by
         */
        public function update($parameters = []): bool {
			$this->clearError();
			$database = new \Database\Service();

            $updateQuery = "UPDATE `$this->_tableName` SET `$this->_tableIDColumn` = `$this->_tableIDColumn` ";
		    
    	    // unique id is required to perform an updat
    	    if (!$this->id) {
        	    $this->error('ERROR: id is required for '.$this->_objectName().' update.');
        	    return false;
    	    }

	        foreach ($parameters as $fieldKey => $fieldValue) {
	            if (in_array($fieldKey, $this->_fields)) {
	               $updateQuery .= ", `$fieldKey` = ?";
	               $database->AddParam($fieldValue);
	            }
	        }
	        
            $updateQuery .= " WHERE	`$this->_tableIDColumn` = ?";
            $database->AddParam($this->id);
            $database->Execute($updateQuery);

			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
            return $this->details();
		}
		
        /**
         * add by params
         * 
         * @param array $parameters, name value pairs to add and populate new object by
         */
		public function add($parameters = []) {
			$database = new \Database\Service();
	
    		$addQuery = "INSERT INTO `$this->_tableName` ";
			$bindFields = array();
	        foreach ($parameters as $fieldKey => $fieldValue) {
	            if (in_array($fieldKey, $this->_fields())) {
    	            array_push($bindFields, $fieldKey);
					$database->AddParam($fieldValue);
	            }
	        }
	        $addQuery .= '(`'.implode('`,`',$bindFields).'`';
            $addQuery .= ") VALUES (" . trim ( str_repeat("?,", count($bindFields)) ,',') . ")";

            // Execute DB Query
            $database->Execute($addQuery);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			
			// get recent added row id to return update() and details()
			$this->id = $database->Insert_ID();
			return $this->update($parameters);
		}

		// Get Object by ID
		public function load($id): bool {
			$this->clearError();
			$this->id = $id;
			return $this->details();
		}

		/********************************************/
		/* Get Object Record Using Unique Code		*/
		/********************************************/
		public function _getObject(string $code): bool {
			// Clear Errors
			$this->clearError();

			$database = new \Database\Service();
			if (gettype($this->_tableUKColumn) == 'NULL') {
				$trace = debug_backtrace()[1];
				$this->error("No surrogate key defined for ".get_class($this)." called from ".$trace['class']."::".$trace['function']." in ".$trace['file']." line ".$trace['line']);
				error_log($this->error());
				return false;
			}

			// Prepare Query
			$get_object_query = "
				SELECT	`$this->_tableIDColumn`
				FROM	`$this->_tableName`
				WHERE	`$this->_tableUKColumn` = ?";
				
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
				$this->error($parts[1]." '".$code."' not found");
				return false;
			}
		}

		// Load Object Attributes from Cache or Database
		public function details(): bool {
			$this->clearError();
			$database = new \Database\Service();

			$cache = $this->cache();
			if (isset($cache) && !empty($this->_cacheKeyPrefix)) {
				$fromCache = $cache->get();
				if (isset($fromCache) && !empty($fromCache)) {
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
				foreach ($this as $key => $value) $this->$key = null;
				$this->exists(false);
				$this->cached(false);
			}
			return true;
		}

		// Delete a record from the database using current ID
		public function delete(): bool {
			// Clear Errors
			$this->clearError();
			$database = new \Database\Service();

			if (! $this->id) {
				$this->error("No current instance of this object");
				return false;
			}

			// Initialize Services
			$cache = $this->cache();
			if (isset($cache)) $cache->delete();

			// Prepare Query
			$delete_object_query = "
				DELETE
				FROM	`$this->_tableName`
				WHERE	`$this->_tableIDColumn` = ?";

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
		
		public function deleteByKey($keyName) {
		
            // Clear Errors
			$this->clearError();
			$database = new \Database\Service();

			if (! $this->id) {
				$this->error("No current instance of this object");
				return false;
			}

			// Initialize Services
			$cache = $this->cache();
			if (isset($cache)) $cache->delete();

			// Prepare Query
			$delete_object_query = "DELETE FROM `$this->_tableName` WHERE `$this->_tableUKColumn` = ?";

			// Bind ID to Query
			$database->AddParam($keyName);

			// Execute Query
			$rs = $database->Execute($delete_object_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			return true;
		}		
		
        /**
         * get max value from a column in the current DB table
         */
		public function maxColumnValue($column='id') {
		
		    $this->clearError();
			$database = new \Database\Service();
			$get_object_query = "SELECT MAX(`$column`) FROM `$this->_tableName`";

			$rs = $database->Execute($get_object_query);
			if (!$rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			list($value) = $rs->FetchRow();
			return $value;
		}
		
        /**
         * @TODO REMOVE -> move to the recordset Service->Execute()
         *
         * get the error that may have happened on the DB level
         *
         * @params string $query, prepared statement query
         * @params array $params, values to populated prepared statement query
         */		
		protected function execute($query, $params) {
			$rs = $GLOBALS["_database"]->Execute($query,$params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
            return $rs;
		}

		/********************************************/
		/* Track Fields Updateable in Table			*/
		/********************************************/
		protected function _addFields($fields) {
			if (is_array($fields)) {
				foreach ($fields as $field) array_push($this->_fields,$field);
			}
		}

		/********************************************/
		/* Get Object Record Using Unique Code		*/
		/********************************************/
		protected function _ukExists(string $code): bool {
			
			// Clear Errors
			$this->clearError();
			$database = new \Database\Service();

			// Prepare Query
			$get_object_query = "
				SELECT	`$this->_tableIDColumn`
				FROM	`$this->_tableName`
				WHERE	`$this->_tableUKColumn` = ?";

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

		public function _addStatus($param) {
			if (is_array($param)) $this->_statii = array_merge($this->_statii,$param);
			else array_push($this->_statii,$param);
		}

		public function _addTypes($param) {
			if (is_array($param)) $this->_types = array_merge($this->_types,$param);
			else array_push($this->_types,$param);
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
	}
