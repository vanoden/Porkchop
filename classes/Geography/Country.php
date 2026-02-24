<?php
	namespace Geography;

	class Country extends \BaseModel {

		public string $name = "";
		public string $abbreviation = "";
		public int $view_order = 500;

		public function __construct(int $id = 0) {
			$this->_tableName = "geography_countries";
			$this->_tableUKColumn = "name";
			$this->_cacheKeyPrefix = "geography.country";
			parent::__construct($id);
		}

		/** @method public add(array $parameters = [])
		 * Add new country.
		 * Required Parameters: name
		 * Optional Parameters: abbreviation, view_order
		 * @param array $parameters
		 * @return bool
		 */
		public function add($parameters = []) {
			// Clear Previous Errors
			$this->clearErrors();

			// Initialize Database Service
			$database = new \Database\Service();

			// Validate Required Parameters
			if (! isset($parameters['name']) || ! is_string($parameters['name']) || trim($parameters['name']) === '') {
				$this->error("Country name required");
				return false;
			}
			if (! preg_match('/^\w[\w\.\-\_\s\,]*$/', trim($parameters['name']))) {
				$this->error("Invalid country name '" . $parameters['name'] . "'");
				return false;
			}
			$name = trim($parameters['name']);
			$abbreviation = isset($parameters['abbreviation']) && $parameters['abbreviation'] !== '' ? trim((string) $parameters['abbreviation']) : null;
			$view_order = isset($parameters['view_order']) ? (int) $parameters['view_order'] : 500;

			$add_object_query = "
				INSERT INTO geography_countries (name, abbreviation, view_order)
				VALUES (?, ?, ?)
			";
			$database->AddParam($name);
			$database->AddParam($abbreviation);
			$database->AddParam($view_order);
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
		 * Update country.
		 * Optional Parameters: name, abbreviation, view_order
		 * @param array $parameters
		 * @return bool
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
			$update_object_query = "UPDATE geography_countries SET id = id";

			if (isset($parameters['name'])) {
				$update_object_query .= ", name = ?";
				$database->AddParam(trim((string) $parameters['name']));
			}
			if (array_key_exists('abbreviation', $parameters)) {
				$update_object_query .= ", abbreviation = ?";
				$database->AddParam($parameters['abbreviation'] === '' || $parameters['abbreviation'] === null ? null : trim((string) $parameters['abbreviation']));
			}
			if (isset($parameters['view_order'])) {
				$update_object_query .= ", view_order = ?";
				$database->AddParam((int) $parameters['view_order']);
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

			$this->clearCache();
			return $this->details();
		}

		/** Load country by id, name, or abbreviation. */
		public function get($idOrNameOrAbbrev) {
			// Clear Previous Errors
			$this->clearErrors();

			// Initialize Database Service
			$database = new \Database\Service();

			// Check if input is numeric ID
			if (is_numeric($idOrNameOrAbbrev) && (int) $idOrNameOrAbbrev > 0) {
				$this->id = (int) $idOrNameOrAbbrev;
				return $this->details();
			}
			$s = trim((string) $idOrNameOrAbbrev);
			if ($s === '') return false;
			$get_query = "
				SELECT	id
				FROM	geography_countries
				WHERE	name = ?
				OR		abbreviation = ?
				LIMIT 1";
			$database->AddParam($s);
			$database->AddParam($s);
			$rs = $database->Execute($get_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			if (! ($row = $rs->FetchRow())) return false;
			$row = (array) $row;
			$this->id = (int) ($row['id'] ?? $row[0]);
			return $this->details();
		}

		/** @method public details()
		 * Load country details by ID.
		 * @return bool
		 */
		public function details(): bool {
			// Clear Previous Errors
			$this->clearErrors();

			// Initialize Database Service
			$database = new \Database\Service();

			// Validate ID
			if (empty($this->id)) return false;

			// Prepare Query
			$query = "
				SELECT	id,
						name,
						abbreviation,
						view_order
				FROM	geography_countries
				WHERE	id = ?
			";
			$database->AddParam($this->id);
			$rs = $database->Execute($query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			if (! ($row = $rs->FetchRow())) {
				$this->id = null;
				return false;
			}
			$row = (array) $row;
			$this->id = (int) ($row['id'] ?? $row[0]);
			$this->name = (string) ($row['name'] ?? $row[1]);
			$abbrev = $row['abbreviation'] ?? $row[2] ?? null;
			$this->abbreviation = $abbrev !== null ? (string) $abbrev : '';
			$this->view_order = (int) ($row['view_order'] ?? $row[3] ?? 500);
			return true;
		}

		/** @method public provinces()
		 * Get list of provinces for this country.
		 * @return Province[]
		 */
		public function provinces() {
			$provinceList = new \Geography\ProvinceList();
			return $provinceList->find(array('country_id' => $this->id));
		}
	}
