<?php
class BaseModel extends \BaseClass {

	// Primary Key
	public ?int $id = 0;

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
	protected $_metadataKeys = array();

	// Name for Cache Key - id appended in square brackets
	protected $_cacheKeyPrefix;

	// Metadata Table Info
	protected $_metaTableName;
	protected $_tableMetaFKColumn = 'instance_id';
	protected $_tableMetaKeyColumn = 'key';

	// Should we always audit events for this class?
	protected $_auditEvents = false;

	/** @constructor($id = 0)
	 * Load object base on ID if given
	 * @param int $id
	 */
	public function __construct($id = 0) {
		if (empty($this->_tableName)) {
			$calledClass = get_called_class();
			$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
			$callerInfo = '';

			// Get caller information from the backtrace
			if (isset($backtrace[1])) {
				$caller = $backtrace[1];
				$callerInfo = " called from " . 
					(isset($caller['class']) ? $caller['class'] : 'unknown class') . "::" . 
					(isset($caller['function']) ? $caller['function'] : 'unknown function') . 
					"() in " . 
					(isset($caller['file']) ? basename($caller['file']) : 'unknown file') . 
					" line " . 
					(isset($caller['line']) ? $caller['line'] : 'unknown');
			}
			
			app_log("Class " . $calledClass . " constructed w/o table name!" . $callerInfo, 'notice');
		}
		if (is_numeric($id) && $id > 0) {
			$this->id = $id;
			$this->details();
		} else {
			$this->_exists = false;
		}
	}

	/** @method __call($name, $parameters)
	 * Polymorphism for Fun and Profit
	 * @param string $name
	 * @param array $parameters
	 * @return mixed
	 */
	public function __call($name, $parameters) {
		if ($name == 'get' && count($parameters) == 2) $this->error("Too many parameters for 'get'");
		elseif ($name == 'get')  {
			if (!isset($parameters[0]) || $parameters[0] === null || $parameters[0] === '') {
				$this->error("No value provided to get()");
				return false;
			}
			return $this->_getObject($parameters[0]);
		}
		elseif ($name == 'setMetadata') {
			if (gettype($parameters[0]) == 'object') return $this->setMetadataObject($parameters[0], $parameters[1]);
			else return $this->setMetadataScalar($parameters[0], $parameters[1]);
		}
		elseif ($name == 'uploadImage') {
			if (! empty($parameters[1]) && gettype($parameters[1]) == 'integer') {
				return $this->uploadImageToRepoID($parameters[0], $parameters[1], $parameters[2] ?? null, $parameters[3] ?? null);
			}
			elseif (! empty($parameters[1]) && gettype($parameters[1]) == 'string') {
				return $this->uploadImageToRepoCode($parameters[0], $parameters[1], $parameters[2] ?? null, $parameters[3] ?? null);
			}
			else {
				$this->error("Invalid parameters for uploadImage");
				return false;
			}
		}
		else {
			$caller = debug_backtrace()[1];
			$className = get_called_class();
			$callerClass = $caller["class"] ?? 'unknown';
			$callerFunction = $caller["function"] ?? 'unknown';
			$callerLine = $caller["line"] ?? 0;
			app_log("$className: No function '$name' found with ".count($parameters)." parameters. Called by " . $callerClass . "::" . $callerFunction . "() Line " . $callerLine, 'warning');
			$this->error("Invalid method '$name'"); // for ".$this->objectName());
		}
	}

	/** @method _tableName()
	 * Return the name of the table
	 * @return string Name of table
	 */
	public function _tableName() {
		return $this->_tableName;
	}

	/**
	 * Return the name of the table
	 * @return string Name of Primary Key ID Column
	 */
	public function _tableIDColumn() {
		return $this->_tableIDColumn;
	}

	/**
	 * Return List of Object Fields
	 * Auto-populate if not provided by constructor
	 * @return array Names of fields in object
	 */
	public function _fields() {
		if (count($this->_fields) < 1) {
			$properties = get_object_vars($this);
			foreach ($properties as $property => $stuff) {
				if (preg_match('/^_/', $property)) continue;
				array_push($this->_fields, $property);
			}
			return array_keys(get_object_vars($this));
		}
		return $this->_fields;
	}

	/**
	 * Does the table have specified field?
	 * @param string $name
	 * @return bool
	 */
	public function hasField($name): bool {
		return in_array($name, $this->_fields);
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
			$this->error('ERROR: id is required for ' . $this->_objectName() . ' update.');
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
					$audit_message .= $fieldKey . " changed to " . $fieldValue;
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
			'description' => 'Updated ' . $this->_objectName(),
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
			$traceClass = $trace['class'] ?? 'unknown';
			$traceFunction = $trace['function'] ?? 'unknown';
			$traceFile = $trace['file'] ?? 'unknown';
			$traceLine = $trace['line'] ?? 0;
			app_log("No table name defined for " . get_class($this) . " called from " . $traceClass . "::" . $traceFunction . " in " . $traceFile . " line " . $traceLine, 'error');
			return false;
		}
		if (! preg_match('/^[a-z0-9_]+$/', $this->_tableName)) {
			$trace = debug_backtrace()[1];
			$this->error("Invalid table name defined for class");
			$traceClass = $trace['class'] ?? 'unknown';
			$traceFunction = $trace['function'] ?? 'unknown';
			$traceFile = $trace['file'] ?? 'unknown';
			$traceLine = $trace['line'] ?? 0;
			app_log("Invalid table name defined for " . get_class($this) . " called from " . $traceClass . "::" . $traceFunction . " in " . $traceFile . " line " . $traceLine, 'error');
			return false;
		}
		if (! $database->has_table($this->_tableName)) {
			$trace = debug_backtrace()[1];
			$this->error("Table " . $this->_tableName . " does not exist");
			$traceClass = $trace['class'] ?? 'unknown';
			$traceFunction = $trace['function'] ?? 'unknown';
			$traceFile = $trace['file'] ?? 'unknown';
			$traceLine = $trace['line'] ?? 0;
			app_log("Table does not exist for " . get_class($this) . " called from " . $traceClass . "::" . $traceFunction . " in " . $traceFile . " line " . $traceLine, 'error');
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
		$addQuery .= '(`' . implode('`,`', $bindFields) . '`';
		$addQuery .= ") VALUES (" . trim(str_repeat("?,", count($bindFields)), ',') . ")";

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
			'description' => 'Added new ' . $this->_objectName(),
			'class_name' => get_class($this),
			'class_method' => 'add'
		));

		return $this->update($parameters);
	}

	/**
	 * Get Object by ID
	 * @param int $id
	 * @return bool True if object found
	 */
	public function load($id): bool {
		$this->clearError();
		if (!empty($id)) $this->id = $id;
		else return false;
		return $this->details();
	}

	/**
	 * Get Object Record Using Unique Code
	 * @param string $code
	 * @return bool True if object found
	 */
	public function _getObject(string $code): bool {
		// Clear Errors
		$this->clearError();

		// Initialize Database Service
		$database = new \Database\Service();
		if (gettype($this->_tableUKColumn) == 'NULL') {
			$trace = debug_backtrace()[1];
			$this->error("No surrogate key defined for " . get_class($this) . " called from " . ($trace['class'] ?? 'unknown') . "::" . ($trace['function'] ?? 'unknown') . " in " . ($trace['file'] ?? 'unknown') . " line " . ($trace['line'] ?? 0));
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
		} else {
			$cls = get_called_class();
			$parts = explode("\\", $cls);
			$this->warn($parts[1] . " '." . $code . "' not found");
			return false;
		}
	}

	/** @method getCachedObject($cachedObject)
	 * Load key/value pairs from cache into object properties
	 * @param array $cachedObject Associative array of key/value pairs
	 * @return void
	 */
	public function getCachedObject($cachedObject) {
		// Is there a cached version of object?
		if (!empty($cachedObject)) {
			// Loop through and populate properties from cache
			foreach ($cachedObject as $key => $value) {
				// Only if property exists
				if (property_exists($this, $key)) {
					// Get Property Type
					$property = new \ReflectionProperty($this, $key);
					$property_type = $property->getType();

					// Set the value based on type
					if (!is_null($property_type) && $property_type->allowsNull() && is_null($value)) $this->$key = null;
					elseif (gettype($this->$key) == "integer") $this->$key = intval($value);
					elseif (gettype($this->$key) == "float") $this->$key = floatval($value);
					elseif (gettype($this->$key) == "boolean") $this->$key = boolval($value);
					elseif (gettype($this->$key) == "string") $this->$key = strval($value);
					else $this->$key = $value;
				}
			}
			// Let them know the values came from cache
			$this->cached(true);

			// Let them know the object exists
			$this->exists(true);

			// Populate the alias fields
			foreach ($this->_aliasFields as $alias => $real) {
				// Cached values might have alias instead of real field name
				if (isset($this->$alias) && !isset($this->$real)) continue;
				$this->$alias = $this->$real;
			}
		}
	}

	/** @method details()
	 * Load Object Attributes from Cache or Database
	 * @return bool True if object found
	 */
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
						foreach ($this->_aliasFields as $alias => $real) {
							if ($key == $real) $key = $alias;
						}
						if (property_exists($this, $key)) {
							// Get the Variable Type to write the value appropriately
							$property = new \ReflectionProperty($this, $key);
							$property_type = $property->getType();
							if (is_null($property_type)) {
								app_log("Setting key " . $key . " of unspecified type to " . strval($value),'trace');
								$this->$key = strval($value);
							} elseif ($property_type->allowsNull() && is_null($value)) $this->$key = null;
							elseif (gettype($this->$key) == "integer") $this->$key = intval($value);
							elseif (gettype($this->$key) == "?integer") $this->$key = intval($value);
							elseif (gettype($this->$key) == "float") $this->$key = floatval($value);
							elseif (gettype($this->$key) == "boolean") $this->$key = boolval($value);
							elseif (gettype($this->$key) == "string") $this->$key = strval($value);
							else $this->$key = $value;
						} else {
							app_log("Property $key not found in " . get_class($this) . " object", 'warning');
						}
					}
					// Let them know the value is cached
					$this->cached(true);

					// Let them know we found the record
					$this->exists(true);

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
				foreach ($this->_aliasFields as $alias => $real) {
					if ($key == $real) $key = $alias;
				}
				if (property_exists($this, $key)) {
					$property = new \ReflectionProperty($this, $key);
					$property_type = $property->getType();
					if (is_null($property_type)) {
						app_log("Setting key " . $key . " of unspecified type to " . strval($value),'trace');
						$this->$key = strval($value);
					} elseif ($property_type->allowsNull() && is_null($value)) $this->$key = null;
					elseif (gettype($this->$key) == "integer") $this->$key = intval($value);
					elseif (gettype($this->$key) == "?integer") $this->$key = intval($value);
					elseif (gettype($this->$key) == "float") $this->$key = floatval($value);
					elseif (gettype($this->$key) == "boolean") $this->$key = boolval($value);
					elseif (gettype($this->$key) == "string") $this->$key = strval($value);
					else $this->$key = $value;
				} else {
					app_log("Property $key not found in " . get_class($this) . " object", 'warning');
				}
			}
			$this->exists(true);
			$this->cached(false);
			if (!empty($this->_cacheKeyPrefix)) $cache->set($object);
		} else {
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

	/** @method delete()
	 * Delete a record from the database using current ID
	 * @return bool True if object deleted
	 */
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
			'description' => 'Deleted ' . $this->_objectName(),
			'class_name' => get_class($this),
			'class_method' => 'delete'
		));

		return true;
	}

	/** @method deleteByKey(keyname)
	 * Delete a record from the database using a key
	 * @param string $keyName
	 * @return bool True if object deleted
	 */
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
			'description' => 'Deleted ' . $this->_objectName(),
			'class_name' => get_class($this),
			'class_method' => 'deleteByKey'
		));

		return true;
	}

	/** @method maxColumnValue($column = 'id')
	 * get max value from a column in the current DB table
	 */
	public function maxColumnValue($column = 'id') {

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

	/** @method execute($query, $params)
	 * @TODO REMOVE -> move to the recordset Service->Execute()
	 *
	 * get the error that may have happened on the DB level
	 *
	 * @params string $query, prepared statement query
	 * @params array $params, values to populated prepared statement query
	 */
	protected function execute($query, $params) {
		app_log("WHY IS THIS HERE?  Use Database\Service->Execute() instead!", 'warning');
		$rs = $GLOBALS["_database"]->Execute($query, $params);
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
			foreach ($fields as $field) array_push($this->_fields, $field);
		}
	}

	/**
	 * Add a field alias.  This allows standardization of field names where older tables may have different names for the same field.
	 * Used primarily for unique keys such as code, name, etc.
	 * @param mixed $real - Real field name in database
	 * @param mixed $alias - Alias field name in object
	 * @return void
	 */
	protected function _aliasField($real, $alias) {
		$this->_aliasFields[$alias] = $real;
	}

	/**
	 * Add Keys to Metadata Keys Array - Generally called by class constructor with a suggested list of keys for the class
	 * @param mixed $keys
	 * @return void
	 */
	protected function _addMetadataKeys($keys) {
		if (is_array($keys)) {
			foreach ($keys as $key) {
				if (!in_array($key, $this->_metadataKeys)) array_push($this->_metadataKeys, $key);
			}
		} elseif (!in_array($keys, $this->_metadataKeys)) array_push($this->_metadataKeys, $keys);
	}

	/** @method _metadataKeys($keys = null, $value = null)
	 * Return array of keys for metadata
	 * @param mixed $keys
	 * @param mixed $value
	 * @return array
	 */
	protected function _metadataKeys($keys = null, $value = null) {
		if (is_array($keys)) {
			foreach ($keys as $key) {
				array_push($this->_metadataKeys, $key);
			}
		} elseif (!empty($keys)) {
			array_push($this->_metadataKeys, $keys);
		}
		return $this->_metadataKeys;
	}

	/** @method _ukExists($code)
	 * Get Object Record Using Unique Code
	 * and return true if found, false if not
	 * @param string $code
	 * @return bool True if object found
	 */
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

	/** @method _addStatus($param)
	 * Add a status to the list of valid statii for this object
	 * @param mixed $param
	 * @return void
	 */
	public function _addStatus($param) {
		if (is_array($param)) $this->_statii = array_merge($this->_statii, $param);
		else array_push($this->_statii, $param);
	}

	/** @method statii()
	 * Return array of valid statii for this object
	 * @return array 
	 */
	public function statii() {
		return $this->_statii;
	}

	/** @method _addTypes($param)
	 * Add a type to the list of valid types for this object
	 * @param mixed $param 
	 * @return void 
	 */
	public function _addTypes($param) {
		if (is_array($param)) $this->_types = array_merge($this->_types, $param);
		else array_push($this->_types, $param);
	}

	/** @method exists($exists = null)
	 * Get/Set existance of instance.  Was the record found in the database?
	 * @param mixed $exists Tell the object if it exists
	 * @return bool Tell us if the object exists
	 */
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
			$cache_key = $this->_cacheKeyPrefix . "[" . $this->id . "]";
			app_log("Returning ".$cache_key." from cache",'trace');
			return new \Cache\Item($GLOBALS['_CACHE_'], $cache_key);
		} else if (!empty($this->_cacheKeyPrefix)) {
			app_log("No ID defined for " . get_class($this),'debug');
			return null;
		} else {
			app_log("No cache key defined for " . get_class($this),'debug');
			return null;
		}
	}

	/** @method clearCache()
	 * Clear Object from Cache
	 * @return void
	 */
	public function clearCache() {
		$cache = $this->cache();
		if ($cache) {
			$cache->delete();
		}
		return true;
	}

	/** @method cached($cached = null)
	 * Don't check cache, just see if data came from cache!
	 * @param bool $cached
	 * @return bool
	 */
	public function cached($cached = null) {
		if (is_bool($cached)) {
			if ($cached) $this->_cached = true;
			else $this->_cached = false;
		} elseif (is_numeric($cached)) {
			$this->_cached = $cached;
		}
		return $this->_cached;
	}

	/** @method setMetadataScalar(key, value)
	 * Set Metadata Value for Key
	 * @param string $key
	 * @param string $value
	 * @return bool True if set
	 */
	public function setMetadataScalar(string $key, string $value): bool {
		$this->clearError();
		if (! isset($value)) return $this->unsetMetadata($key);
		// Initialize Database Service
		$database = new \Database\Service();

		// Prepare Query
		$set_metadata_query = "
				INSERT INTO `$this->_metaTableName`
				(
					`$this->_tableMetaFKColumn`,
					`$this->_tableMetaKeyColumn`,
					`value`
				)
				VALUES
				(
					?,
					?,
					?
				)
				ON DUPLICATE KEY UPDATE
					`value` = ?
			";

		// Bind Parameters
		$database->AddParam($this->id);
		$database->AddParam($key);
		$database->AddParam($value);
		$database->AddParam($value);

		// Execute Query
		$database->Execute($set_metadata_query);
		if ($database->ErrorMsg()) {
			$this->SQLError($database->ErrorMsg());
			return false;
		}

		return true;
	}

	/** @method setMetadataObject(key, value)
	 * Set Metadata Object
	 * @param string $key
	 * @param stdClass $value
	 * @return bool True if set
	 */
	public function setMetadataObject(string $key, stdClass $value): bool {
		$this->clearError();

		// Initialize Database Service
		$database = new \Database\Service();

		// Prepare Query
		$set_metadata_query = "
				INSERT INTO `$this->_metaTableName`
				(
					`$this->_tableMetaFKColumn`,
					`$this->_tableMetaKeyColumn`,
					`value`
				)
				VALUES
				(
					?,
					?,
					?
				)
				ON DUPLICATE KEY UPDATE
					`value` = ?
			";

		// Bind Parameters
		$database->AddParam($this->id);
		$database->AddParam($key);
		$database->AddParam(json_encode($value));
		$database->AddParam(json_encode($value));

		// Execute Query
		$database->Execute($set_metadata_query);
		if ($database->ErrorMsg()) {
			$this->SQLError($database->ErrorMsg());
			return false;
		}

		return true;
	}

	/** @method unsetMetadata(key)
	 * Unset Metadata Value for Key
	 * @param string $key
	 * @return bool True if unset
	 */
	public function unsetMetadata(string $key): bool {
		$this->clearError();

		// Initialize Database Service
		$database = new \Database\Service();

		// Prepare Query
		$unset_metadata_query = "
				DELETE FROM `$this->_metaTableName`
				WHERE `$this->_tableMetaFKColumn` = ?
				AND `$this->_tableMetaKeyColumn` = ?
			";

		// Bind Parameters
		$database->AddParam($this->id);
		$database->AddParam($key);

		// Execute Query
		$rs = $database->Execute($unset_metadata_query);
		if (! $rs) {
			$this->SQLError($database->ErrorMsg());
			return false;
		}

		// audit the delete event
		$auditLog = new \Site\AuditLog\Event();
		$auditLog->add(array(
			'instance_id' => $this->id,
			'description' => 'Deleted metadata key $key from ' . $this->_objectName() . ' id ' . $this->id,
			'class_name' => get_class($this),
			'class_method' => 'deleteMetadata'
		));

		return true;
	}

	/** @method getImpliedMetadataKeys()
	 * Get Implied Key - Only those set in the object
	 * @return array
	 */
	public function getImpliedMetadataKeys(): array {
		$this->clearError();

		// Fetch Results
		return $this->_metadataKeys();
	}

	/** @method getMetadataKeys()
	 * Get All Metadata Keys for Object
	 * @return array
	 */
	public function getMetadataKeys(): array {
		$this->clearError();

		// Initialize Database Service
		$database = new \Database\Service();

		// Prepare Query
		$get_metadata_keys_query = "
				SELECT	`$this->_tableMetaKeyColumn`
				FROM	`$this->_metaTableName`
				GROUP BY `$this->_tableMetaKeyColumn`
			";

		// Execute Query
		$rs = $database->Execute($get_metadata_keys_query);
		if (! $rs) {
			$this->SQLError($database->ErrorMsg());
			return [];
		}

		// Fetch Results
		$keys = $this->_metadataKeys();
		while (list($key) = $rs->FetchRow()) {
			if (!in_array(strval($key), $keys))	array_push($keys, strval($key));
		}
		return $keys;
	}

	/** @method getInstanceMetadataKeys()
	 * Get All Metadata Keys defined for this Instance
	 * @return array
	 */
	public function getInstanceMetadataKeys(): array {
		$this->clearError();

		// Initialize Database Service
		$database = new \Database\Service();

		// Prepare Query
		$get_metadata_keys_query = "
				SELECT	`$this->_tableMetaKeyColumn`
				FROM	`$this->_metaTableName`
				WHERE	`$this->_tableMetaFKColumn` = ?
			";

		// Bind Parameters
		$database->AddParam($this->id);

		// Execute Query
		$rs = $database->Execute($get_metadata_keys_query);
		if (! $rs) {
			$this->SQLError($database->ErrorMsg());
			return [];
		}

		// Fetch Results
		$keys = $this->_metadataKeys();
		while (list($key) = $rs->FetchRow()) {
			if (!in_array(strval($key), $keys))	array_push($keys, strval($key));
		}

		return $keys;
	}

	/** @method getMetadata(key)
	 * Get Metadata Value for Key
	 * @param key
	 * @return string
	 */
	public function getMetadata(string $key): string {
		$this->clearError();

		// Initialize Database Service
		$database = new \Database\Service();

		// Prepare Query
		$get_metadata_query = "
				SELECT	`value`
				FROM	`$this->_metaTableName`
				WHERE	`$this->_tableMetaFKColumn` = ?
				AND		`$this->_tableMetaKeyColumn` = ?
			";

		// Bind Parameters
		$database->AddParam($this->id);
		$database->AddParam($key);

		// Execute Query
		$rs = $database->Execute($get_metadata_query);
		if (! $rs) {
			$this->SQLError($database->ErrorMsg());
			return false;
		}

		// Fetch Results
		list($value) = $rs->FetchRow();
		return strval($value);
	}

	/** @method getMetadataInt(key)
	 * Get Metadata Value for Key as Integer
	 * @param key
	 * @return int
	 */
	public function getMetadataInt(string $key): int {
		$this->clearError();

		// Initialize Database Service
		$database = new \Database\Service();

		// Prepare Query
		$get_metadata_query = "
				SELECT	`value`
				FROM	`$this->_metaTableName`
				WHERE	`$this->_tableMetaFKColumn` = ?
				AND		`$this->_tableMetaKeyColumn` = ?
			";

		// Bind Parameters
		$database->AddParam($this->id);
		$database->AddParam($key);

		// Execute Query
		$rs = $database->Execute($get_metadata_query);
		if (! $rs) {
			$this->SQLError($database->ErrorMsg());
			return false;
		}

		// Fetch Results
		list($value) = $rs->FetchRow();
		return intval($value);
	}

	/** @method getMetadataFloat(key)
	 * Get Metadata Value for Key as Float
	 * @param key
	 * @return float
	 */
	public function getMetadataFloat(string $key): float {
		$this->clearError();

		// Initialize Database Service
		$database = new \Database\Service();

		// Prepare Query
		$get_metadata_query = "
				SELECT	`value`
				FROM	`$this->_metaTableName`
				WHERE	`$this->_tableMetaFKColumn` = ?
				AND		`$this->_tableMetaKeyColumn` = ?
			";

		// Bind Parameters
		$database->AddParam($this->id);
		$database->AddParam($key);

		// Execute Query
		$rs = $database->Execute($get_metadata_query);
		if (! $rs) {
			$this->SQLError($database->ErrorMsg());
			return false;
		}

		// Fetch Results
		list($value) = $rs->FetchRow();
		return floatval($value);
	}

	/** @method getMetadataBool(key)
	 * Get Metadata Value for Key as Boolean
	 * @param key
	 * @return bool
	 */
	public function getMetadataBool(string $key): bool {
		$this->clearError();

		// Initialize Database Service
		$database = new \Database\Service();

		// Prepare Query
		$get_metadata_query = "
				SELECT	`value`
				FROM	`$this->_metaTableName`
				WHERE	`$this->_tableMetaFKColumn` = ?
				AND		`$this->_tableMetaKeyColumn` = ?
			";

		// Bind Parameters
		$database->AddParam($this->id);
		$database->AddParam($key);

		// Execute Query
		$rs = $database->Execute($get_metadata_query);
		if (! $rs) {
			$this->SQLError($database->ErrorMsg());
			return false;
		}

		// Fetch Results
		list($value) = $rs->FetchRow();
		return boolval($value);
	}

	/** @method getMetadataObject(key)
	 * Get Metadata Record as Object
	 * @param key
	 * @return object
	 */
	public function getMetadataObject(string $key): object {
		$this->clearError();

		// Initialize Database Service
		$database = new \Database\Service();

		// Prepare Query
		$get_metadata_query = "
				SELECT	`value`
				FROM	`$this->_metaTableName`
				WHERE	`$this->_tableMetaFKColumn` = ?
				AND		`$this->_tableMetaKeyColumn` = ?
			";

		// Bind Parameters
		$database->AddParam($this->id);
		$database->AddParam($key);

		// Execute Query
		$rs = $database->Execute($get_metadata_query);
		if (! $rs) {
			$this->SQLError($database->ErrorMsg());
			return new \stdClass();
		}

		// Fetch Results
		list($value) = $rs->FetchRow();
		return json_decode($value);
	}

	/** @method getAllMetadata()
	 * Get All Metadata for Object as a Key/Value Array
	 * @return array 
	 */
	public function getAllMetadata(): array {
		$this->clearError();

		// Initialize Database Service
		$database = new \Database\Service();

		// Prepare Query
		$get_metadata_query = "
				SELECT	`$this->_tableMetaKeyColumn`, `value`
				FROM	`$this->_metaTableName`
				WHERE	`$this->_tableMetaFKColumn` = ?
			";

		// Bind Parameters
		$database->AddParam($this->id);

		// Execute Query
		$rs = $database->Execute($get_metadata_query);
		if (! $rs) {
			$this->SQLError($database->ErrorMsg());
			return [];
		}

		// Fetch Results
		$metadata = $this->_metadataKeys();
		while (list($key, $value) = $rs->FetchRow()) {
			$metadata[$key] = $value;
		}

		return $metadata;
	}

	/** @method public dropAllMetadata()
	 * Drop all metadata for this object
	 * @return bool True if successful
	 */
	public function dropAllMetadata(): bool {
		$this->clearError();

		// Initialize Database Service
		$database = new \Database\Service();

		// Prepare Query
		$drop_metadata_query = "
				DELETE FROM `$this->_metaTableName`
				WHERE `$this->_tableMetaFKColumn` = ?
			";

		// Bind Parameters
		$database->AddParam($this->id);

		// Execute Query
		$rs = $database->Execute($drop_metadata_query);
		if (! $rs) {
			$this->SQLError($database->ErrorMsg());
			return false;
		}

		return true;
	}

	/** @method searchMeta(key, value)
	 * Get Metadata for Object as a Key/Value Array
	 * @param string $key The metadata key to search for
	 * @param string $value The value to search for in the metadata
	 * @return array 
	 */
	public function searchMeta($key, $value): array {
		$this->clearError();

		// Validata Input
		if (!preg_match('/^[\w_\-\.]+$/', $key)) {
			$this->error("Invalid key search string");
			return [];
		}
		if (!preg_match('/^[\w_\-\.]+$/', $value)) {
			$this->error("Invalid value search string");
			return [];
		}

		// Initialize Database Service
		$database = new \Database\Service();

		// Prepare Query
		$get_results_query = "
				SELECT	`" . $this->_tableMetaFKColumn . "`
				FROM	" . $this->_metaTableName . "
				WHERE	`" . $this->_tableMetaKeyColumn . "` = ?
				AND		value like '%?%'";

		$database->AddParam($key);
		$database->AddParam($value);

		$rs = $database->Execute($get_results_query);
		if (!$rs) {
			$this->SQLError($database->ErrorMsg());
			return [];
		}
		$objects = array();
		$class = get_class();
		while (list($id) = $rs->FetchRow()) {
			$object = new $class($id);
			array_push($objects, $object);
		}
		return $objects;
	}

	/** @method images(object_type = null)
	 * Get all images associated with the product
	 * @param string|null $object_type The type of object to retrieve images for, defaults to class name
	 * @return array|null Array of Storage\File objects or null on error
	 */
	public function images($object_type = null) {

		$database = new \Database\Service();
		if (!$object_type) $object_type = get_class($this);

		$get_images_query = "
				SELECT i.image_id, i.view_order, i.label
				FROM object_images i
				WHERE i.object_id = ?
				AND i.object_type = ?
				ORDER BY i.view_order ASC
			";
		$database->AddParam($this->id);
		$database->AddParam($object_type);

		$rs = $database->Execute($get_images_query);

		if (!$rs) {
			$this->SQLError($database->ErrorMsg());
			return null;
		}

		$images = array();
		while ($row = $rs->FetchRow()) {
			$file = new \Storage\File($row['image_id']);
			if ($file->id) {
				$file->view_order = $row['view_order'];
				$file->label = $row['label'];
				$images[] = $file;
			}
		}

		return $images;
	}

	/** @method addImage(image_id, object_type, label)
	 * Add an image to the product
	 * 
	 * @param int $image_id The image ID to add
	 * @param string|null $object_type The type of object to associate the image with, defaults to class name
	 * @param string $label Optional label for the image
	 * @return int 1 if successful, 0 otherwise
	 */
	public function addImage($image_id, $object_type = null, $label = '') {

		// Prepare Query to Tie Object to Image
		$add_image_query = "
				INSERT
				INTO	object_images
				(		object_id,
						object_type,
						image_id,
						label
				)
				VALUES
				(?,?,?,?)
			";

		if (!$object_type) $object_type = get_class($this);
		$GLOBALS['_database']->Execute($add_image_query, array($this->id, $object_type, $image_id, $label));
		if ($GLOBALS['_database']->ErrorMsg()) {
			$this->SQLError($GLOBALS['_database']->ErrorMsg());
			return 0;
		}
		return 1;
	}

	/** @method dropImage(image_id, object_type)
	 * Remove an image from the product
	 * 
	 * @param int $image_id The image ID to remove
	 * @return int 1 if successful, 0 otherwise
	 */
	public function dropImage($image_id, $object_type = null) {
		if (!$object_type) $object_type = get_class($this);

		# Prepare Query to Drop Image from Object
		$drop_image_query = "
				DELETE
				FROM	object_images
				WHERE	object_id = ?
				AND		object_type = ?
				AND		image_id = ?
			";
		$GLOBALS['_database']->Execute($drop_image_query, array($this->id, $object_type, $image_id));
		if ($GLOBALS['_database']->ErrorMsg()) {
			$this->SQLError($GLOBALS['_database']->ErrorMsg());
			return 0;
		}
		return 1;
	}

	/** @method hasImage(int, string|null)
	 * Check if the product has a specific image
	 * 
	 * @param int $image_id The image ID to check
	 * @return int|null 1 if has image, 0 if not, null on error
	 */
	public function hasImage($image_id, $object_type = null) {

		if (!$object_type) $object_type = get_class($this);

		# Prepare Query to Get Image
		$get_image_query = "
				SELECT	1
				FROM	object_images
				WHERE	object_id = ?
				AND		object_type = ?
				AND		image_id = ?
			";
		$rs = $GLOBALS['_database']->Execute($get_image_query, array($this->id, $object_type, $image_id));
		if (! $rs) {
			$this->SQLError($GLOBALS['_database']->ErrorMsg());
			return null;
		}
		list($found) = $rs->FetchRow();
		if (! $found) $found = 0;
		return $found;
	}

	/** @method uploadImageToRepoCode(array, string, string, string, string|null)
	 * Upload an image for a specific Repository by Code and associate it with the object
	 * @param array $fileData The uploaded file data from $_FILES
	 * @param string $repository_code The Code of the repository where the file should be stored
	 * @param string $path Optional path within the repository to store the file
	 * @param string $label Optional label for the image
	 * @param string|null $object_type Optional object type to associate the image with
	 * @return bool True if upload and association are successful, false otherwise
	 */
	public function uploadImageToRepoCode(array $fileData, string $repository_code, string $path = '', string $label = '', $object_type = null): bool {
		$this->clearError();

		if (! preg_match('/^\//', $path)) $path = '/' . $path;

		// Check if the file data is valid
		if (empty($fileData) || !isset($fileData['tmp_name']) || !is_uploaded_file($fileData['tmp_name'])) {
			$this->error('Invalid file data');
			return false;
		}

		// Initialize the repository factory and load the repository
		$repository = new \Storage\Repository();
		$repository->get($repository_code);

		if ($repository->error()) {
			$this->error('Error loading repository: ' . $repository->error());
			return false;
		}

		if (! $repository->id) {
			$this->error('Repository not found with code ' . $repository_code);
			return false;
		}

		return ($this->uploadImageToRepo($fileData, $repository, $path, $label, $object_type));
	}

	/** @method uploadImageToRepoID(array, int, string, string, string|null)
	 * Upload an image and associate it with the object
	 * 
	 * @param array $fileData The uploaded file data from $_FILES
	 * @param int $repository_id The ID of the repository where the file should be stored
	 * @param string $path Optional path within the repository to store the file
	 * @param string $label Optional label for the image
	 * @return bool True if upload and association are successful, false otherwise
	 */
	public function uploadImageToRepoID(array $fileData, int $repository_id, string $path = '', string $label = '', $object_type = null): bool {
		$this->clearError();

		if (! preg_match('/^\//', $path)) $path = '/' . $path;

		// Check if the file data is valid
		if (empty($fileData) || !isset($fileData['tmp_name']) || !is_uploaded_file($fileData['tmp_name'])) {
			$this->error('Invalid file data: '. json_encode($fileData));
			return false;
		}

		// Initialize the repository factory and load the repository
		$repository = new \Storage\Repository($repository_id);
		if (! $repository->exists()) {
			$this->error('Repository not found with ID ' . $repository_id);
			return false;
		}

		return ($this->uploadImageToRepo($fileData, $repository, $path, $label, $object_type));
	}

	/** @method uploadImageToRepo(array, \Storage\Repository, string, string, string|null)
	 * Upload an image to a specified repository and associate it with the object.
	 * Uploads an image to a specified repository and associates it with the object.
	 * @param array $fileData The uploaded file data from $_FILES
	 * @param \Storage\Repository $repository The repository where the file should be stored
	 * @param string $path Optional path within the repository to store the file
	 * @param string $label Optional label for the image
	 * @param string|null $object_type Optional object type to associate the image with
	 * @return bool True if upload and association are successful, false otherwise
	 */
	public function uploadImageToRepo(array $fileData, \Storage\Repository $repository, string $path = '', string $label = '', $object_type = null): bool {
		if (! $repository->id) {
			$this->error('Repository not found!');
			return false;
		}

		if (! $repository->writable()) {
			$this->error('Permission Denied');
			return false;
		}

		// Get Instance of the repository
		$instance = $repository->getInstance();

		app_log("Identified repo '" . $repository->name . "'");

		// Upload the file
		$uploadedFile = $instance->uploadFile($fileData, $path);

		if (!$uploadedFile) {
			$this->error('Error uploading file: ' . $instance->error());
			return false;
		}

		// Associate the uploaded image with the object
		if (!$object_type) $object_type = get_class($this);
		if (!$this->addImage($uploadedFile->id, $object_type, $label)) {
			$this->error('Error associating image with object');
			return false;
		}

		return true;
	}

	/** @method validMetadataKey(string)
	 * Validate a metadata key
	 * @param string $key The metadata key to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validMetadataKey($key) {
		if (!preg_match('/^[\w_\-\.\s\:]+$/', $key)) {
			$this->error("Invalid metadata key");
			return false;
		}
		return true;
	}
}