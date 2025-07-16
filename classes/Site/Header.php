<?php
	namespace Site;

	/** @class Header
	 * Represents an HTTP header for the site.
	 * Provides methods to add, update, and validate headers.
	 */
	class Header Extends \BaseModel {
        public $name;
        public $value;

		/** @constructor
		 * Initializes a new instance of the Header class.
		 * @param int|null $id The ID of the header. If null, a new header is created.
		 * @return void
		 */
		public function __construct($id = null) {
			$this->_tableName = "site_headers";
			$this->_tableUKColumn = "name";
			$this->_cacheKeyPrefix = "site.header";
    		parent::__construct($id);
		}

		/** @method public add(params)
		 * Adds a new HTTP header to the site.
		 * @param array $params Associative array containing 'name' and 'value' of the header.
		 * @return bool Returns true on success, false on failure.
		 */
		public function add($params = array()): bool {
			// Clear previous errors
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();
	
			// Validate parameters
			if (empty($params['name'])) {
				$this->error("name required for header");
				return false;
			}
			if (! $this->validName($params['name'])) {
				$this->error("Invalid header name");
				return false;
			}
			if (empty($params['value'])) {
				$this->error("value required for header");
				return false;
			}
			if (! $this->validContent($params['value'])) {
				$this->error("Invalid header value");
				return false;
			}

			// Prepare Query to check for existing header
			$add_object_query = "
				INSERT
				INTO	site_headers
				(		`name`,
						`value`
				)
				VALUES
				(		?,?)
			";

			// Bind Parameters
			$database->AddParam($params['name']);
			$database->AddParam($params['value']);

			// Execute Query
			$database->Execute($add_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			// Get the last inserted ID
			$this->id = $database->Insert_ID();
			
            // audit the add event
            $auditLog = new \Site\AuditLog\Event();
            $auditLog->add(array(
                'instance_id' => $this->id,
                'description' => 'Added new '.$this->_objectName(),
                'class_name' => get_class($this),
                'class_method' => 'add'
            ));

			return $this->update($params);
		}

		/** @method public update(params)
		 * Updates an existing HTTP header.
		 * @param array $params Associative array containing 'name' and 'value' of the header.
		 * @return bool Returns true on success, false on failure.
		 */
		public function update($params = []): bool {
			// Clear previous errors
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Validate parameters
			if (!empty($params['name']) && !$this->validName($params['name'])) {
				$this->error("Invalid header name");
				return false;
			}
			if (!empty($params['value']) && !$this->validContent($params['value'])) {
				$this->error("Invalid header value");
				return false;
			}

			// Prepare Query to update header
			$update_object_query = "
				UPDATE	site_headers
				SET		id = id";

			// Bind parameters
			if (isset($params['value'])) {
				$update_object_query .= ",
						value = ?";
				$database->AddParam($params['value']);
			}
			$update_object_query .= "
				WHERE	id = ?";
			$database->AddParam($this->id);

			// Clear Existing Cache
			$this->clearCache();

			// Execute Query
			$database->Execute($update_object_query);

			// Check for errors
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

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

		/** @method public details()
		 * Retrieves the details of the HTTP header.
		 * @return bool Returns true on success, false on failure.
		 */
		public function details(): bool {
			// Clear previous errors
			$this->clearError();

			$cache = $this->cache();
			$cachedData = $cache->get();
			if (!empty($cachedData)) {
				app_log("Loading header ".$this->id." from cache",'trace');
				foreach ($cachedData as $key => $value) {
					$this->$key = $value;
				}
				$this->cached(true);
				$this->exists(true);
				return true;
			}
			app_log("Loading header ".$this->id." from database",'trace');
			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query to get header details
			$get_object_query = "
				SELECT	*
				FROM	site_headers
				WHERE	id = ?";

			// Bind Parameters
			$database->AddParam($this->id);

			// Execute Query
			$rs = $database->Execute($get_object_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if ($object->id) {
				$this->id = $object->id;
				$this->name = $object->name;
				$this->value = $object->value;

				app_log("Storing header ".$this->id." in cache",'notice');
				// Write To Cache
				$cache->set(array(
					'id' => $this->id,
					'name' => $this->name,
					'value' => $this->value
				));
			}
			else {
				$this->id = null;
				$this->name = null;
				$this->value = null;
			}
			return true;
		}

		/** @method public name(string|null)
		 * Gets or sets the name of the header.
		 * @param string|null $name The name to set. If null, returns the current name.
		 * @return string|null Returns the current name if no parameter is provided, otherwise returns void.
		 */
		public function name(string|null $name = null): ?string {
			if ($name !== null) {
				$this->name = $name;
			}
			return $this->name;
		}

		/** @method public value(string|null)
		 * Gets or sets the value of the header.
		 * @param string|null $value The value to set. If null, returns the current value.
		 * @return string|null Returns the current value if no parameter is provided, otherwise returns void.
		 */
		public function value(string|null $value = null): ?string {
			if ($value !== null) {
				$this->value = $value;
			}
			return $this->value;
		}

		/** @method public validContent(string)
		 * Validates the content of the header.
		 * @param string $string The content to validate.
		 * @return bool Returns true if valid, false otherwise.
		 */
		public function validContent($string): bool {
			if (preg_match('/^[\w\s\-\.\,\;\:\'\"\/\?\!\@\#\$\%\^\&\*\(\)\=\+\[\]\{\}\<>\|\\\`~]+$/', $string)) return true;
			else return false;
		}

		/** @method public validName(string)
		 * Validates the name of the header.
		 * @param string $string The name to validate.
		 * @return bool Returns true if valid, false otherwise.
		 */
		public function validName($string): bool {
			if (preg_match('/^\w[\w\-]*$/',$string)) return true;
			else return false;
		}
	}
?>
