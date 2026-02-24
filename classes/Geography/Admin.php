<?php
	namespace Geography;

	class Admin extends \BaseModel {
		public $country_id;
		public $name;
		public $abbreviation;
		public $code;
		public $type;
		public $label;

		public function __construct($id = 0) {
			$this->_tableName = 'geography_provinces';
			parent::__construct($id);
		}

		/** @method public add(array $parameters = [])
		 * Add New Province
		 * Required Parameters: country_id, name, abbreviation
		 * Optional Parameters: code, type, label
		 * @param array $parameters
		 */
		public function add($parameters = []) {
			// Clear Previous Errors
			$this->clearErrors();

			// Initialize Database Service
			$database = new \Database\Service();

			// Validate Required Parameters
			if (empty($parameters['country_id'])) {
				$this->error("country_id required");
				return false;
			}
			$country = new Country($parameters['country_id']);
			if (! $country->id) {
				$this->error("Country not found");
				return false;
			}
			if (! isset($parameters['name']) || ! preg_match('/^\w.*$/', trim((string) $parameters['name']))) {
				$this->error("Name required");
				return false;
			}
			if (! isset($parameters['abbreviation']) || trim((string) $parameters['abbreviation']) === '') {
				$this->error("Abbreviation required");
				return false;
			}
			$name = trim((string) $parameters['name']);
			$abbreviation = trim((string) $parameters['abbreviation']);
			$code = isset($parameters['code']) && trim((string) $parameters['code']) !== '' ? trim((string) $parameters['code']) : null;
			if ($code === null) {
				$code = ($country->abbreviation ?: 'X') . '-' . preg_replace('/[^a-z0-9]+/i', '_', $name);
			}
			if ($this->getByCode($code)) {
				$this->error("Province with this code already exists");
				return false;
			}
			$existing = new Admin(0);
			if ($existing->getProvince($country->id, $name)) {
				$this->error("Province with this name already exists in this country");
				return false;
			}
			$type = isset($parameters['type']) && $parameters['type'] !== '' ? trim((string) $parameters['type']) : null;
			$label = isset($parameters['label']) && $parameters['label'] !== '' ? trim((string) $parameters['label']) : null;

			// Prepare Query
			$add_object_query = "
				INSERT INTO geography_provinces (code, country_id, name, type, abbreviation, label)
				VALUES (?, ?, ?, ?, ?, ?)
			";
			$database->AddParam($code);
			$database->AddParam($country->id);
			$database->AddParam($name);
			$database->AddParam($type);
			$database->AddParam($abbreviation);
			$database->AddParam($label);
			$database->Execute($add_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			$this->id = (int) $database->Insert_ID();

			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add([
				'instance_id' => $this->id,
				'description' => 'Added new ' . $this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'add',
			]);

			return $this->update($parameters);
		}

		/** @method public update(array $parameters = [])
		 * Update Province
		 * Optional Parameters: country_id, name, abbreviation, code, type, label
		 * @param array $parameters
		 */
		public function update($parameters = []): bool {
			// Clear Previous Errors
			$this->clearErrors();

			// Initialize Database Service
			$database = new \Database\Service();

			// Validate ID
			if (empty($this->id)) {
				$this->error("id required for update");
				return false;
			}

			// Prepare Query
			$update_object_query = "UPDATE geography_provinces SET id = id";

			if (isset($parameters['name'])) {
				$update_object_query .= ", name = ?";
				$database->AddParam(trim((string) $parameters['name']));
			}
			if (isset($parameters['country_id'])) {
				$update_object_query .= ", country_id = ?";
				$database->AddParam((int) $parameters['country_id']);
			}
			if (isset($parameters['abbreviation'])) {
				$update_object_query .= ", abbreviation = ?";
				$database->AddParam(trim((string) $parameters['abbreviation']));
			}
			if (isset($parameters['code'])) {
				$update_object_query .= ", code = ?";
				$database->AddParam(trim((string) $parameters['code']));
			}
			if (array_key_exists('type', $parameters)) {
				$update_object_query .= ", type = ?";
				$database->AddParam($parameters['type'] === '' || $parameters['type'] === null ? null : trim((string) $parameters['type']));
			}
			if (array_key_exists('label', $parameters)) {
				$update_object_query .= ", label = ?";
				$database->AddParam($parameters['label'] === '' || $parameters['label'] === null ? null : trim((string) $parameters['label']));
			}
			$update_object_query .= " WHERE id = ?";
			$database->AddParam($this->id);

			$database->Execute($update_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add([
				'instance_id' => $this->id,
				'description' => 'Updated ' . $this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'update',
			]);

			return $this->details();
		}

		/** @method public __call(string $name, array $arguments)
		 * Magic method to handle dynamic method calls for getting provinces by code or by country and name.
		 * Usage:
		 * - get($code): Get province by unique code.
		 * - get($country_id, $name): Get province by country ID and name.
		 * @param string $name
		 * @param array $arguments
		 * @return bool
		 */
		public function __call($name, $arguments) {
			if ($name === 'get') {
				if (count($arguments) === 1) {
					return $this->getByCode($arguments[0]);
				}
				if (count($arguments) === 2) {
					return $this->getProvince($arguments[0], $arguments[1]);
				}
			}
			$this->error("Method '$name' not found");
			return false;
		}

		/** Load province by unique code. */
		public function getByCode(string $code): bool {
			$code = trim($code);
			if ($code === '') return false;
			$rs = $GLOBALS['_database']->Execute("SELECT id FROM geography_provinces WHERE code = ?", [$code]);
			if (! $rs || ! ($row = $rs->FetchRow())) return false;
			$this->id = (int) (is_array($row) ? $row[0] : $row['id']);
			return $this->details();
		}

		/** @method public getProvince(int $country_id, string $name)
		 * Get province by country ID and name. If name is less than 3 characters, it will attempt to get by abbreviation instead.
		 * @param int $country_id
		 * @param string $name
		 * @return bool
		 */
		public function getProvince($country_id, $name): bool {
			// Clear Previous Errors
			$this->clearErrors();

			// Initialize Database Service
			$database = new \Database\Service();

			// Validate Parameters
            app_log("Country $country_id Name $name");
			if (strlen($name) < 3) return $this->getByAbbreviation($country_id,$name);

			// Prepare Query
			$get_object_query = "
				SELECT	id
				FROM	geography_provinces
				WHERE	country_id = ?
				AND		name = ?
			";

			$rs = $database->Execute($get_object_query,array($country_id,$name));
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			list($id) = $rs->FetchRow();
			if ($id > 0) {
				$this->id = $id;
				app_log("Found province ".$this->id);
				return $this->details();
			}
			return false;
		}

		/** @method public getByAbbreviation(int $country_id, string $abbrev)
		 * Get province by country ID and abbreviation.
		 * @param int $country_id
		 * @param string $abbrev
		 * @return bool
		 */
		public function getByAbbreviation($country_id,$abbrev) {
			// Clear Previous Errors
			$this->clearErrors();

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query
			$get_object_query = "
				SELECT	id
				FROM	geography_provinces
				WHERE	country_id = ?
				AND		abbreviation = ?
			";
			$rs = $database->Execute($get_object_query,array($country_id,$abbrev));
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			list($this->id) = $rs->FetchRow();
			return $this->details();
		}

		/** @method public details()
		 * Load province details by ID.
		 * @return bool
		 */
		public function details(): bool {
			// Clear Previous Errors
			$this->clearErrors();

			// Validate ID
			if (empty($this->id)) return false;

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query
			$query = "
				SELECT id, code, country_id, name, type, abbreviation, label
				FROM geography_provinces
				WHERE id = ?
			";

			// Add Parameters
			$database->AddParam($this->id);

			// Execute Query
			$rs = $database->Execute($query);

			// Check for Errors
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				$this->id = null;
				return false;
			}

			// Populate Object
			if ($object = $rs->FetchNextObject(false)) {
				$this->id = $object->id;
				$this->country_id = (int) $object->country_id;
				$this->name = (string) $object->name;
				$this->type = isset($object->type) ? (string) $object->type : null;
				$this->abbreviation = (string) $object->abbreviation;
				$this->label = isset($object->label) ? (string) $object->label : null;
				$this->code = (string) $object->code;
				return true;
			}

			return false;
		}

		/** @method public country()
		 * Get Country object for this province.
		 * @return Country
		 */
		public function country() {
			return new \Geography\Country($this->country_id);
		}
	}
