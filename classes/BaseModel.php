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

	// Name for Unique Object Name Column
	protected $_tableNameColumn = 'name';

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

	// Search Tag Table Info (for unified tag system)
	protected $_searchTagTableName = 'search_tags';
	protected $_searchTagXrefTableName = 'search_tags_xref';
	protected $_tableSearchTagFKColumn = 'instance_id';
	protected $_tableSearchTagKeyColumn = 'tag';

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

	/** @method _getActualClass()
	 * Get the actual class name of the object
	 * @return string
	 */
	public function _getActualClass(): string {
		return get_class($this);
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
				return false;
			}
			return $this->_getObject($parameters[0]);
		}
		elseif ($name == 'addMetadata') {
			if (gettype($parameters[0]) == 'object') return $this->setMetadataObject($parameters[0], $parameters[1]);
			else return $this->setMetadataScalar($parameters[0], $parameters[1]);
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
			$this->error("Invalid method '$name'"); // for ".$this->_getActualClass());
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
			$this->error('ERROR: id is required for ' . $this->_getActualClass() . ' update.');
			return false;
		}

		foreach ($this->_aliasFields as $alias => $real) {
			if (isset($parameters[$alias])) {
				$parameters[$real] = $parameters[$alias];
				unset($parameters[$alias]);
			}
		}

		$audit_message = "";
		$validFieldPattern = '/^[a-z0-9_]+$/';
		foreach ($parameters as $fieldKey => $fieldValue) {
			if ($fieldKey !== '' && is_string($fieldKey) && preg_match($validFieldPattern, $fieldKey) && in_array($fieldKey, $this->_fields)) {
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
		if ($this->_auditEvents && strlen($audit_message) > 0) $this->recordAuditEvent($this->id, $audit_message);

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
		$validFieldPattern = '/^[a-z0-9_]+$/';
		foreach ($parameters as $fieldKey => $fieldValue) {
			if ($fieldKey !== '' && is_string($fieldKey) && preg_match($validFieldPattern, $fieldKey) && in_array($fieldKey, $this->_fields())) {
				array_push($bindFields, $fieldKey);
				$database->AddParam($fieldValue);
			}
		}
		if (empty($bindFields)) {
			$this->error("No valid fields to insert for " . $this->_getActualClass());
			return false;
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
		if ($this->_auditEvents) $this->recordAuditEvent($this->id, 'Added new ' . $this->_getActualClass());

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
		if ($this->_auditEvents) $this->recordAuditEvent($this->id, 'Deleted ' . $this->_getActualClass());

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
		if ($this->_auditEvents) $this->recordAuditEvent($this->id, 'Deleted ' . $this->_getActualClass());

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
			foreach ($fields as $field) {
				if ($field !== '' && is_string($field) && preg_match('/^[a-z0-9_]+$/', $field)) {
					array_push($this->_fields, $field);
				}
			}
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
			app_log("No cache key defined for " . get_class($this),'trace');
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

	/** @method setCacheElement(key, value)
	 * Set a value in the cache for this object
	 * @param string $key
	 * @param mixed $value
	 * @return bool True if set
	 */
	public function setCacheElement(string $key, $value): bool {
		$cache = $this->cache();
		if ($cache) {
			$cachedObject = $cache->get();
			if (!is_array($cachedObject)) $cachedObject = array();
			$cachedObject[$key] = $value;
			$cache->set($cachedObject);
			return true;
		}
		return false;
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

	/** @method getByName(value)
	 * Get Object Record Using Name
	 * @param string $value
	 * @return bool True if object found
	 */
	public function getByName(string $value): bool {
		if (empty($this->_tableNameColumn)) {
			$this->error("No name field defined for " . get_class($this));
			return false;
		}
		// Clear Errors
		$this->clearError();
		$database = new \Database\Service();

		// Prepare Query
		$get_object_query = "
				SELECT	`$this->_tableIDColumn`
				FROM	`$this->_tableName`
				WHERE	`$this->_tableNameColumn` = ?";

		// Bind Code to Query
		$database->AddParam($value);

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
			$this->warn($parts[1] . " with name '" . $value . "' not found");
			return false;
		}
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
		
		// Validate that object has a valid ID before setting metadata
		if (empty($this->id) || !is_numeric($this->id) || $this->id <= 0) {
			$this->error("Cannot set metadata: object ID is invalid or not set");
			return false;
		}
		
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
		
		// Validate that object has a valid ID before setting metadata
		if (empty($this->id) || !is_numeric($this->id) || $this->id <= 0) {
			$this->error("Cannot set metadata: object ID is invalid or not set");
			return false;
		}

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
		if ($this->_auditEvents) $this->recordAuditEvent($this->id, 'Deleted metadata key ' . $key . ' from ' . $this->_getActualClass());

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

	/** @method _normalizeClassName($class)
	 * Convert fully qualified class name to search_tags format (e.g., \Product\Item -> Product::Item)
	 * @param string $class Fully qualified class name or already normalized
	 * @return string Normalized class name
	 */
	protected function _normalizeClassName(string $class): string {
		// If already in Product::Item format, return as-is
		if (preg_match('/^[A-Z][a-zA-Z0-9]*::[A-Z][a-zA-Z0-9]*$/', $class)) {
			return $class;
		}
		
		// Convert \Product\Item to Product::Item
		$class = trim($class, '\\');
		$class = str_replace('\\', '::', $class);
		
		return $class;
	}

	/** @method _getTagClass()
	 * Get normalized class name for current object
	 * @return string Normalized class name (e.g., Product::Item)
	 */
	protected function _getTagClass(): string {
		$class = get_class($this);
		return $this->_normalizeClassName($class);
	}

	/** @method getTagClass()
	 * Get normalized class name for current object (public accessor)
	 * @return string Normalized class name (e.g., Product::Item)
	 */
	public function getTagClass(): string {
		return $this->_getTagClass();
	}

	/** @method validTagValue($value)
	 * Validate tag value format
	 * @param string $value Tag value to validate
	 * @return bool True if valid
	 */
	public function validTagValue(string $value): bool {
		if (preg_match('/^[\w\-\.\_\s]+$/', $value)) return true;
		return false;
	}

	/** @method validTagCategory($category)
	 * Validate tag category format
	 * @param string $category Tag category to validate
	 * @return bool True if valid
	 */
	public function validTagCategory(string $category): bool {
		if (empty($category)) return true; // Empty category is allowed
		if (preg_match('/^[a-zA-Z][a-zA-Z0-9\.\-\_\s]*$/', $category)) return true;
		return false;
	}

	/** @method _getOrCreateTag($value, $category = '')
	 * Get existing tag ID or create new tag in search_tags table
	 * @param string $value Tag value
	 * @param string $category Tag category (optional)
	 * @return int|false Tag ID or false on error
	 */
	protected function _getOrCreateTag(string $value, string $category = ''): int {
		$this->clearError();
		
		// Validate inputs
		if (!$this->validTagValue($value)) {
			$this->error("Invalid tag value format");
			app_log("Invalid tag value format: " . $value, 'warning', __FILE__, __LINE__);
			return false;
		}
		if (!$this->validTagCategory($category)) {
			$this->error("Invalid tag category format");
			app_log("Invalid tag category format: " . $category, 'warning', __FILE__, __LINE__);
			return false;
		}
		
		$class = $this->_getTagClass();
		app_log("Getting or creating tag. Class: $class, Category: '$category', Value: '$value'", 'debug', __FILE__, __LINE__);
		
		$database = new \Database\Service();
		
		// Check if tag already exists
		$get_tag_query = "
			SELECT id
			FROM `{$this->_searchTagTableName}`
			WHERE `class` = ?
			AND `category` = ?
			AND `value` = ?
		";
		$database->AddParam($class);
		$database->AddParam($category);
		$database->AddParam($value);
		
		$rs = $database->Execute($get_tag_query);
		if ($database->ErrorMsg()) {
			$errorMsg = $database->ErrorMsg();
			$this->SQLError($errorMsg);
			app_log("Error checking for existing tag: " . $errorMsg, 'error', __FILE__, __LINE__);
			return false;
		}
		
		if ($rs && $row = $rs->FetchRow()) {
			list($tag_id) = $row;
			app_log("Found existing tag ID: $tag_id", 'debug', __FILE__, __LINE__);
			return intval($tag_id);
		}
		
		// Create new tag
		$create_tag_query = "
			INSERT INTO `{$this->_searchTagTableName}`
			(`class`, `category`, `value`)
			VALUES (?, ?, ?)
		";
		$create_database = new \Database\Service();
		$create_database->AddParam($class);
		$create_database->AddParam($category);
		$create_database->AddParam($value);
		
		$rs = $create_database->Execute($create_tag_query);
		if ($create_database->ErrorMsg()) {
			$errorMsg = $create_database->ErrorMsg();
			$this->SQLError($errorMsg);
			app_log("Error creating tag: " . $errorMsg . " Query: " . $create_tag_query, 'error', __FILE__, __LINE__);
			return false;
		}
		
		$new_tag_id = intval($create_database->Insert_ID());
		app_log("Created new tag ID: $new_tag_id", 'debug', __FILE__, __LINE__);
		return $new_tag_id;
	}

	/** @method addTag($tag, $category = '')
	 * Add a tag to this object
	 * @param string $tag Tag value to add
	 * @param string $category Optional category for the tag
	 * @return bool True if successful
	 */
	public function addTag(string $tag, string $category = ''): bool {
		$this->clearError();
		
		app_log("BaseModel::addTag called. Object ID: " . ($this->id ?? 'NULL') . ", Class: " . get_class($this) . ", Tag: '$tag', Category: '$category'", 'debug', __FILE__, __LINE__);
		
		// Validate object ID
		if (empty($this->id) || !is_numeric($this->id) || $this->id <= 0) {
			$this->error("Cannot add tag: object ID is invalid or not set");
			app_log("Cannot add tag - invalid object ID: " . ($this->id ?? 'NULL'), 'warning', __FILE__, __LINE__);
			return false;
		}
		
		// Get or create tag
		$tag_id = $this->_getOrCreateTag($tag, $category);
		if (!$tag_id) {
			app_log("Failed to get or create tag. Error: " . $this->error(), 'error', __FILE__, __LINE__);
			return false; // Error already set
		}
		
		app_log("Got tag ID: $tag_id, now checking xref", 'debug', __FILE__, __LINE__);
		
		$database = new \Database\Service();
		
		// Check if xref already exists
		$check_xref_query = "
			SELECT id
			FROM `{$this->_searchTagXrefTableName}`
			WHERE `tag_id` = ?
			AND `object_id` = ?
		";
		$database->AddParam($tag_id);
		$database->AddParam($this->id);
		
		$rs = $database->Execute($check_xref_query);
		if ($database->ErrorMsg()) {
			$errorMsg = $database->ErrorMsg();
			$this->SQLError($errorMsg);
			app_log("Error checking xref: " . $errorMsg, 'error', __FILE__, __LINE__);
			return false;
		}
		
		if ($rs && $row = $rs->FetchRow()) {
			// Tag already exists for this object
			app_log("Tag xref already exists, returning true", 'debug', __FILE__, __LINE__);
			return true;
		}
		
		// Create xref entry
		$create_xref_query = "
			INSERT INTO `{$this->_searchTagXrefTableName}`
			(`tag_id`, `object_id`)
			VALUES (?, ?)
		";
		$create_database = new \Database\Service();
		$create_database->AddParam($tag_id);
		$create_database->AddParam($this->id);
		
		app_log("Creating xref entry. Tag ID: $tag_id, Object ID: " . $this->id . ", Table: {$this->_searchTagXrefTableName}", 'debug', __FILE__, __LINE__);
		$rs = $create_database->Execute($create_xref_query);
		
		// Check for errors - Execute can return null on failure
		if (!$rs || $create_database->ErrorMsg()) {
			$errorMsg = $create_database->ErrorMsg() ?: "Query execution returned null/false";
			$this->SQLError($errorMsg);
			app_log("Error creating xref: " . $errorMsg . " Query: " . $create_xref_query . " Tag ID: $tag_id, Object ID: " . $this->id, 'error', __FILE__, __LINE__);
			
			// Also check the underlying connection error
			if ($GLOBALS['_database']->ErrorMsg()) {
				$connError = $GLOBALS['_database']->ErrorMsg();
				app_log("Underlying database connection error: " . $connError, 'error', __FILE__, __LINE__);
				$this->SQLError($connError);
			}
			return false;
		}
		
		$xref_id = $create_database->Insert_ID();
		app_log("Tag added successfully. Xref ID: " . $xref_id, 'info', __FILE__, __LINE__);
		
		// Verify the xref was actually created
		if (empty($xref_id)) {
			app_log("Warning: Insert_ID() returned empty, verifying xref was created...", 'warning', __FILE__, __LINE__);
			$verify_db = new \Database\Service();
			$verify_query = "SELECT id FROM `{$this->_searchTagXrefTableName}` WHERE `tag_id` = ? AND `object_id` = ?";
			$verify_db->AddParam($tag_id);
			$verify_db->AddParam($this->id);
			$verify_rs = $verify_db->Execute($verify_query);
			if ($verify_rs && $verify_row = $verify_rs->FetchRow()) {
				app_log("Xref verified - ID: " . $verify_row[0], 'info', __FILE__, __LINE__);
			} else {
				app_log("Xref NOT found after insert! This is a problem.", 'error', __FILE__, __LINE__);
				$this->error("Xref entry was not created successfully");
				return false;
			}
		}
		
		return true;
	}

	/** @method removeTag($tag, $category = '')
	 * Remove a tag from this object
	 * @param string $tag Tag value to remove
	 * @param string $category Optional category for the tag
	 * @return bool True if successful
	 */
	public function removeTag(string $tag, string $category = ''): bool {
		$this->clearError();
		
		// Validate object ID
		if (empty($this->id) || !is_numeric($this->id) || $this->id <= 0) {
			$this->error("Cannot remove tag: object ID is invalid or not set");
			return false;
		}
		
		// Validate inputs
		if (!$this->validTagValue($tag)) {
			$this->error("Invalid tag value format");
			return false;
		}
		if (!$this->validTagCategory($category)) {
			$this->error("Invalid tag category format");
			return false;
		}
		
		$class = $this->_getTagClass();
		$database = new \Database\Service();
		
		// Find tag ID
		$get_tag_query = "
			SELECT id
			FROM `{$this->_searchTagTableName}`
			WHERE `class` = ?
			AND `category` = ?
			AND `value` = ?
		";
		$database->AddParam($class);
		$database->AddParam($category);
		$database->AddParam($tag);
		
		$rs = $database->Execute($get_tag_query);
		if ($database->ErrorMsg()) {
			$this->SQLError($database->ErrorMsg());
			return false;
		}
		
		if (!$rs || !($row = $rs->FetchRow())) {
			// Tag doesn't exist, nothing to remove
			return true;
		}
		
		list($tag_id) = $row;
		
		// Remove xref entry
		$remove_xref_query = "
			DELETE FROM `{$this->_searchTagXrefTableName}`
			WHERE `tag_id` = ?
			AND `object_id` = ?
		";
		$remove_database = new \Database\Service();
		$remove_database->AddParam($tag_id);
		$remove_database->AddParam($this->id);
		
		$rs = $remove_database->Execute($remove_xref_query);
		if ($remove_database->ErrorMsg()) {
			$this->SQLError($remove_database->ErrorMsg());
			return false;
		}
		
		return true;
	}

	/** @method hasTag($tag, $category = '')
	 * Check if this object has a specific tag
	 * @param string $tag Tag value to check
	 * @param string $category Optional category for the tag
	 * @return bool True if object has the tag
	 */
	public function hasTag(string $tag, string $category = ''): bool {
		$this->clearError();
		
		// Validate object ID
		if (empty($this->id) || !is_numeric($this->id) || $this->id <= 0) {
			return false;
		}
		
		// Validate inputs
		if (!$this->validTagValue($tag)) {
			return false;
		}
		if (!$this->validTagCategory($category)) {
			return false;
		}
		
		$class = $this->_getTagClass();
		$database = new \Database\Service();
		
		// Check if tag exists for this object
		$check_tag_query = "
			SELECT COUNT(*)
			FROM `{$this->_searchTagXrefTableName}` stx
			INNER JOIN `{$this->_searchTagTableName}` st ON stx.tag_id = st.id
			WHERE stx.object_id = ?
			AND st.class = ?
			AND st.category = ?
			AND st.value = ?
		";
		$database->AddParam($this->id);
		$database->AddParam($class);
		$database->AddParam($category);
		$database->AddParam($tag);
		
		$rs = $database->Execute($check_tag_query);
		if ($database->ErrorMsg()) {
			$this->SQLError($database->ErrorMsg());
			return false;
		}
		
		if ($rs && $row = $rs->FetchRow()) {
			list($count) = $row;
			return intval($count) > 0;
		}
		
		return false;
	}

	/** @method getTags($category = null)
	 * Get all tags for this object, optionally filtered by category
	 * @param string|null $category Optional category filter
	 * @return array Array of tag values
	 */
	public function getTags(string $category = null): array {
		$this->clearError();
		
		// Validate object ID
		if (empty($this->id) || !is_numeric($this->id) || $this->id <= 0) {
			return [];
		}
		
		$class = $this->_getTagClass();
		$database = new \Database\Service();
		
		// Build query
		$get_tags_query = "
			SELECT st.value
			FROM `{$this->_searchTagXrefTableName}` stx
			INNER JOIN `{$this->_searchTagTableName}` st ON stx.tag_id = st.id
			WHERE stx.object_id = ?
			AND st.class = ?
		";
		$database->AddParam($this->id);
		$database->AddParam($class);
		
		if ($category !== null) {
			if (!$this->validTagCategory($category)) {
				$this->error("Invalid tag category format");
				return [];
			}
			$get_tags_query .= " AND st.category = ?";
			$database->AddParam($category);
		}
		
		$get_tags_query .= " ORDER BY st.category, st.value";
		
		$rs = $database->Execute($get_tags_query);
		if ($database->ErrorMsg()) {
			$this->SQLError($database->ErrorMsg());
			return [];
		}
		
		$tags = [];
		if ($rs) {
			while ($row = $rs->FetchRow()) {
				list($value) = $row;
				$tags[] = $value;
			}
		}
		
		return $tags;
	}

	/** @method setTags($tags, $category = '')
	 * Replace all tags for this object with a new set (for a specific category)
	 * @param array $tags Array of tag values
	 * @param string $category Optional category (if provided, only replaces tags in that category)
	 * @return bool True if successful
	 */
	public function setTags(array $tags, string $category = ''): bool {
		$this->clearError();
		
		// Validate object ID
		if (empty($this->id) || !is_numeric($this->id) || $this->id <= 0) {
			$this->error("Cannot set tags: object ID is invalid or not set");
			return false;
		}
		
		// Validate category
		if (!$this->validTagCategory($category)) {
			$this->error("Invalid tag category format");
			return false;
		}
		
		// Clear existing tags for this category
		if (!$this->clearTags($category)) {
			return false; // Error already set
		}
		
		// Add new tags
		foreach ($tags as $tag) {
			if (!is_string($tag)) {
				continue; // Skip non-string values
			}
			if (!$this->addTag($tag, $category)) {
				return false; // Error already set
			}
		}
		
		return true;
	}

	/** @method clearTags($category = null)
	 * Remove all tags from this object, optionally filtered by category
	 * @param string|null $category Optional category filter
	 * @return bool True if successful
	 */
	public function clearTags(string $category = null): bool {
		$this->clearError();
		
		// Validate object ID
		if (empty($this->id) || !is_numeric($this->id) || $this->id <= 0) {
			$this->error("Cannot clear tags: object ID is invalid or not set");
			return false;
		}
		
		$class = $this->_getTagClass();
		$database = new \Database\Service();
		
		// Build query
		$clear_tags_query = "
			DELETE stx FROM `{$this->_searchTagXrefTableName}` stx
			INNER JOIN `{$this->_searchTagTableName}` st ON stx.tag_id = st.id
			WHERE stx.object_id = ?
			AND st.class = ?
		";
		$database->AddParam($this->id);
		$database->AddParam($class);
		
		if ($category !== null) {
			if (!$this->validTagCategory($category)) {
				$this->error("Invalid tag category format");
				return false;
			}
			$clear_tags_query .= " AND st.category = ?";
			$database->AddParam($category);
		}
		
		$rs = $database->Execute($clear_tags_query);
		if ($database->ErrorMsg()) {
			$this->SQLError($database->ErrorMsg());
			return false;
		}
		
		return true;
	}

	/** @method findObjectsByTag($tag, $category = '', $class = null)
	 * Find all objects with a specific tag (static method for cross-object queries)
	 * @param string $tag Tag value to search for
	 * @param string $category Optional category filter
	 * @param string|null $class Optional class filter (e.g., 'Product::Item')
	 * @return array Array of object IDs
	 */
	public static function findObjectsByTag(string $tag, string $category = '', string $class = null): array {
		$database = new \Database\Service();
		
		// Validate tag value
		if (!preg_match('/^[\w\-\.\_\s]+$/', $tag)) {
			return [];
		}
		
		// Build query
		$find_query = "
			SELECT DISTINCT stx.object_id
			FROM `search_tags_xref` stx
			INNER JOIN `search_tags` st ON stx.tag_id = st.id
			WHERE st.value = ?
		";
		$database->AddParam($tag);
		
		if (!empty($category)) {
			if (!preg_match('/^[a-zA-Z][a-zA-Z0-9\.\-\_\s]*$/', $category)) {
				return [];
			}
			$find_query .= " AND st.category = ?";
			$database->AddParam($category);
		}
		
		if ($class !== null) {
			// Normalize class name
			$class = trim($class, '\\');
			$class = str_replace('\\', '::', $class);
			$find_query .= " AND st.class = ?";
			$database->AddParam($class);
		}
		
		$rs = $database->Execute($find_query);
		if ($database->ErrorMsg()) {
			return [];
		}
		
		$object_ids = [];
		if ($rs) {
			while ($row = $rs->FetchRow()) {
				list($object_id) = $row;
				$object_ids[] = intval($object_id);
			}
		}
		
		return $object_ids;
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
	public function addImage($image_id, $object_type = null, $label = ''): bool {
		// Clear Previous Errors
		$this->clearError();

		// Initialize Database Service
		$database = new \Database\Service();

		// Validate Inputs
		if (!is_numeric($image_id) || $image_id <= 0) {
			$this->error('Invalid image ID provided');
			return false;
		}
		if (!is_string($label)) {
			$this->error('Label must be a string');
			return false;
		}
		if (empty($label)) $label = 'Image ' . $image_id;
		if (!preg_match('/^[\w\s\-\.\\\\]+$/', $label)) {
			$this->error('Invalid label format "'.$label.'"');
			return false;
		}

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

		// Bind Parameters
		$database->AddParam($this->id);
		$database->AddParam($object_type);
		$database->AddParam($image_id);
		$database->AddParam($label);

		$database->Execute($add_image_query);
		if ($database->ErrorMsg()) {
			$this->SQLError($database->ErrorMsg());
			return false;
		}
		return true;
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

	/** @method recordAuditEvent(id, description, class_name, class_method)
	 * Record an audit event for this object
	 * @param int $id The ID of the object
	 * @param string $description The description of the event
	 * @param string $class_name (Optional) The class name of the object
	 * @param string $class_method (Optional) The class method where the event occurred
	 * @return bool True if successful
	 */
	public function recordAuditEvent(int $id, string $description, string $class_name = '', string $class_method = ''): bool {
		$this->clearError();

		if (empty($class_name)) $class_name = $this->_getActualClass();
		if (empty($class_method)) {
			$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
			if (isset($backtrace[1]['function'])) {
				$class_method = $backtrace[1]['function'];
			} else {
				$class_method = 'unknown';
			}
		}
		$auditLog = new \Site\AuditLog\Event();
		$result = $auditLog->add(array(
			'instance_id' => $id,
			'description' => $description,
			'class_name' => $class_name,
			'class_method' => $class_method
		));
		return $result;
	}

	/** @method recordMyAuditEvent(id, description, class_name, class_method)
	 * Record an audit event for this object using its own ID
	 * @param string $description The description of the event
	 * @param string $class_name (Optional) The class name of the object
	 * @param string $class_method (Optional) The class method where the event occurred
	 * @return bool True if successful
	 */
	public function recordMyAuditEvent(int $id, string $description, string $class_name = '', string $class_method = ''): bool {
		$this->clearError();

		if (empty($class_name)) $class_name = $this->_getActualClass();
		if (empty($class_method)) {
			$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
			if (isset($backtrace[1]['function'])) {
				$class_method = $backtrace[1]['function'];
			} else {
				$class_method = 'unknown';
			}
		}
		$auditLog = new \Site\AuditLog\Event();
		$result = $auditLog->add(array(
			'customer_id' => $id,
			'instance_id' => $id,
			'description' => $description,
			'class_name' => $class_name,
			'class_method' => $class_method
		));
		return $result;
	}

	/** @method public validClassName(string)
	 * Validate a class name
	 * @param string $class_name The class name to validate
	 * @return bool True if valid class name
	 */
	public function validClassName(string $class_name): bool {
		if (preg_match('/^[A-Za-z_][A-Za-z0-9_\\\\]*$/',$class_name)) {
			if (class_exists($class_name)) return true;
			else return false;
		}
		else {
			app_log("Invalid class name: '$class_name'",'info');
			return false;
		}
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

	/** @method validMetadataValue(string)
	 * Validate a metadata value
	 * @param string $value The metadata value to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validMetadataValue($value) {
		return $this->safeString($value);
	}
}
