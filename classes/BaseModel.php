<?php
	class BaseModel Extends \BaseClass {
	
		// Primary Key
		public int $id = 0;

		// Was data found in db or cache
		protected $_exists = false;

		// Did data come from cache?
		public bool $_cached = false;

		// Name of Table Holding This Class
		protected $_tableName;

		// Name for Unique Surrogate Key Column (for get)
		protected $_tableUKColumn = 'code';

		// Name for Auto-Increment ID Column
		protected $_tableIDColumn = 'id';

		// Name for Software Incrementing Number Field
		protected $_tableNumberColumn;

		// field names for columns in database tables
		protected $_fields = array();
		protected $_aliasFields = array();

		// Name for Cache Key - id appended in square brackets
		protected $_cacheKeyPrefix;

		// Should we always audit events for this class?
		protected static $_auditEvents = false;

		// Load object base on ID if given
		public function __construct($id = 0) {
			if (empty($this->_tableName)) app_log("Class ".get_called_class()." constructed w/o table name!",'notice');
			if (is_numeric($id) && $id > 0) {
				$this->id = $id;
				$this->details();
			}
			else {
				$this->_exists = false;
			}
		}

		// Polymorphism for Fun and Profit
		public function __call($name,$parameters) {
			if ($name == 'get' && count($parameters) == 2) $this->error("Too many parameters for 'get'");
			elseif ($name == 'get')  return $this->_getObject($parameters[0]);
			else {
				$caller = debug_backtrace()[1];
				$className = get_called_class();
				app_log("$className: No function '$name' found.  Called by ".$caller["class"]."::".$caller["function"]."() Line ".$caller["line"],'warning');
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
		public function hasField($name) {
			return in_array($name,$this->_fields);
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
			
			// unique id is required to perform an update
			if (!$this->id) {
				$this->error('ERROR: id is required for '.$this->_objectName().' update.');
				return false;
			}

			foreach ($this->_aliasFields as $alias => $real) {
				if (isset($parameters[$alias])) {
					$parameters[$real] = $parameters[$alias];
					unset($parameters[$alias]);
				}
			}

			$audit_message = "";
			foreach ($parameters as $fieldKey => $fieldValue) {
				if (in_array($fieldKey, $this->_fields)) {
					if ($this->$fieldKey != $fieldValue) {
						$updateQuery .= ", `$fieldKey` = ?";
						$database->AddParam($fieldValue);
						if (strlen($audit_message) > 0) $audit_message .= ", ";
						$audit_message .= $fieldKey." changed to ".$fieldValue;
					}
				}
			}

			$updateQuery .= " WHERE	`$this->_tableIDColumn` = ?";

			$database->AddParam($this->id);
			$database->Execute($updateQuery);

			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// Clear Cache to Allow Update
			$cache = $this->cache();
			if (isset($cache)) $cache->delete();

			// audit the update event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Updated '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'update'
			));

			return $this->details();

		}

		/**
		 * Get ID of Object
		 * @return int
		 */
		public function id() {
			return $this->id;
		}

		/**
		 * add by params
		 * 
		 * @param array $parameters, name value pairs to add and populate new object by
		 */
		public function add($parameters = []) {
			$database = new \Database\Service();

			if (empty($this->_tableName)) {
				$trace = debug_backtrace()[1];
				$this->error("No table name defined for class");
				app_log("No table name defined for ".get_class($this)." called from ".$trace['class']."::".$trace['function']." in ".$trace['file']." line ".$trace['line'],'error');
				return false;
			}
			if (! preg_match('/^[a-z0-9_]+$/',$this->_tableName)) {
				$trace = debug_backtrace()[1];
				$this->error("Invalid table name defined for class");
				app_log("Invalid table name defined for ".get_class($this)." called from ".$trace['class']."::".$trace['function']." in ".$trace['file']." line ".$trace['line'],'error');
				return false;
			}
			if (! $database->has_table($this->_tableName)) {
				$trace = debug_backtrace()[1];
				$this->error("Table ".$this->_tableName." does not exist");
				app_log("Table does not exist for ".get_class($this)." called from ".$trace['class']."::".$trace['function']." in ".$trace['file']." line ".$trace['line'],'error');
				return false;
			}
	
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

			// audit the add event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Added new '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'add'
			));

			return $this->update($parameters);
		}

		// Get Object by ID
		public function load($id): bool {
			$this->clearError();
			if (!empty($id)) $this->id = $id;
			else return false;
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
				$this->warn($parts[1]." not found");
				return false;
			}
		}

		// Get Cached Object
		public function getCachedObject($fromCache) {
			if (!empty($fromCache)) {
				foreach ($fromCache as $key => $value) {
					if (property_exists($this,$key)) {
						$property = new \ReflectionProperty($this, $key);
						if (is_null($property->getType())) continue;
						$property_type = $property->getType();
						if (!is_null($property_type) && $property_type->allowsNull() && is_null($value)) $this->$key = null;
						elseif (gettype($this->$key) == "integer") $this->$key = intval($value);
						elseif (gettype($this->$key) == "float") $this->$key = floatval($value);
						elseif (gettype($this->$key) == "boolean") $this->$key = boolval($value);
						elseif (gettype($this->$key) == "string") $this->$key = strval($value);
						else $this->$key = $value;
					}
				}
				$this->cached(true);
				$this->exists(true);
				foreach ($this->_aliasFields as $alias => $real) {
					// Cached values might have alias instead of real field name
					if (isset($this->$alias) && !isset($this->$real)) continue;
					$this->$alias = $this->$real;
				}
			}
		}

		// Load Object Attributes from Cache or Database
		public function details(): bool {
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Initialize Cache Service
			if (!empty($this->_cacheKeyPrefix)) {
				$cache = $this->cache();
				if (isset($cache)) {
					$fromCache = $cache->get();
					if (!empty($fromCache)) {
						// Populate Properties from Cache
						foreach ($fromCache as $key => $value) {
							if (property_exists($this,$key)) {
								// Get the Variable Type to write the value appropriately
								$property = new \ReflectionProperty($this, $key);
								$property_type = $property->getType();

								// Variable type is not set
								if (!is_null($property_type) && $property_type->allowsNull() && is_null($value)) $this->$key = null;

								// Variable type is set
								elseif (gettype($this->$key) == "integer") $this->$key = intval($value);
								elseif (gettype($this->$key) == "float") $this->$key = floatval($value);
								elseif (gettype($this->$key) == "boolean") $this->$key = boolval($value);
								elseif (gettype($this->$key) == "string") $this->$key = strval($value);

								// Variable type is set, but none of the above
								else $this->$key = $value;
							}
						}
						// Let them know the value is cached
						$this->cached(true);

						// Let them know we found the record
						$this->exists(true);

						// Populate the alias fields
						foreach ($this->_aliasFields as $alias => $real) {
							// Cached values might have alias instead of real field name
							if (isset($this->$alias) && !isset($this->$real)) continue;
							$this->$alias = $this->$real;
						}
						return true;
					}
				}
			}

			// Build the Query
			$get_object_query = "
				SELECT	*
				FROM	`$this->_tableName`
				WHERE	`$this->_tableIDColumn` = ?
			";

			// Add Query Parameters
			$database->AddParam($this->id);

			// Execute The Query
			$rs = $database->Execute($get_object_query);

			// Check for SQL Errors
			if (!$rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// Fetch results and populate properties from database
			$object = $rs->FetchNextObject(false);
			$column = $this->_tableIDColumn;
			if (is_object($object) && $object->$column > 0) {
				// Collect all attributes from response record
				foreach ($object as $key => $value) {
					if (property_exists($this,$key)) {
						$property = new \ReflectionProperty($this, $key);
						$property_type = $property->getType();
						if (is_null($property_type)) {
							app_log("Setting key ".$this->$key." of unspecified type to ".strval($value));
							$this->$key = strval($value);
						}
						elseif ($property_type->allowsNull() && is_null($value)) $this->$key = null;
						elseif (gettype($this->$key) == "integer") $this->$key = intval($value);
						elseif (gettype($this->$key) == "?integer") $this->$key = intval($value);
						elseif (gettype($this->$key) == "float") $this->$key = floatval($value);
						elseif (gettype($this->$key) == "boolean") $this->$key = boolval($value);
						elseif (gettype($this->$key) == "string") $this->$key = strval($value);
						else $this->$key = $value;
					}
					else {
						app_log("Property $key not found in ".get_class($this)." object",'warning');
					}
				}
				$this->exists(true);
				$this->cached(false);
				if (!empty($this->_cacheKeyPrefix)) $cache->set($object);
				foreach ($this->_aliasFields as $alias => $real) {
					$this->$alias = $this->$real;
				}
			}
			else {
				// Clear all attributes
				foreach ($this as $key => $value) {
					// Cannot nullify ID
					if ($key == 'id') $this->$key = 0;
					if (gettype($this->$key) == "integer") $this->$key = 0;
					elseif (gettype($this->$key) == "float") $this->$key = 0.0;
					elseif (gettype($this->$key) == "boolean") $this->$key = false;
					elseif (gettype($this->$key) == "string") $this->$key = '';
					else $this->$key = null;
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

			// audit the delete event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Deleted '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'delete'
			));

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

			// audit the update event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Deleted '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'deleteByKey'
			));

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

		protected function _aliasField($real,$alias) {
			$this->_aliasFields[$alias] = $real;
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
			else $this->_exists = false;
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
			else if (!empty($this->_cacheKeyPrefix)) {
				$this->debug("No ID defined for ".get_class($this));
				return null;
			}
			else {
				$this->debug("No cache key defined for ".get_class($this));
				return null;
			}
		}

		// Clear Object from Cache
		public function clearCache() {
			$cache = $this->cache();
			if ($cache) $cache->delete();
		}

		// Don't check cache, just see if data came from cache!
		public function cached($cached = null) {
			if (is_bool($cached)) {
				if ($cached) $this->_cached = true;
				else $this->_cached = false;
			}
			elseif(is_numeric($cached)) {
				$this->_cached = $cached;
			}
			return $this->_cached;
		}

		public function getError() {
			return $this->_error;
		}	
	}
