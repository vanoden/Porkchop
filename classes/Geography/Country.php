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

		public function add($parameters = []) {
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
			$GLOBALS['_database']->Execute($add_object_query, [$name, $abbreviation, $view_order]);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			$this->id = (int) $GLOBALS['_database']->Insert_ID();

			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add([
				'instance_id' => $this->id,
				'description' => 'Added new ' . $this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'add',
			]);

			return $this->update($parameters);
		}

		public function update($parameters = []): bool {
			$update_object_query = "UPDATE geography_countries SET id = id";
			$bind_params = [];
			if (isset($parameters['name'])) {
				$update_object_query .= ", name = ?";
				$bind_params[] = trim((string) $parameters['name']);
			}
			if (array_key_exists('abbreviation', $parameters)) {
				$update_object_query .= ", abbreviation = ?";
				$bind_params[] = $parameters['abbreviation'] === '' || $parameters['abbreviation'] === null ? null : trim((string) $parameters['abbreviation']);
			}
			if (isset($parameters['view_order'])) {
				$update_object_query .= ", view_order = ?";
				$bind_params[] = (int) $parameters['view_order'];
			}
			$update_object_query .= " WHERE id = ?";
			$bind_params[] = $this->id;

			$GLOBALS['_database']->Execute($update_object_query, $bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
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
			if (is_numeric($idOrNameOrAbbrev) && (int) $idOrNameOrAbbrev > 0) {
				$this->id = (int) $idOrNameOrAbbrev;
				return $this->details();
			}
			$s = trim((string) $idOrNameOrAbbrev);
			if ($s === '') return false;
			$get_query = "SELECT id FROM geography_countries WHERE name = ? OR abbreviation = ? LIMIT 1";
			$rs = $GLOBALS['_database']->Execute($get_query, [$s, $s]);
			if (! $rs || ! ($row = $rs->FetchRow())) return false;
			$row = (array) $row;
			$this->id = (int) ($row['id'] ?? $row[0]);
			return $this->details();
		}

		public function details(): bool {
			if (empty($this->id)) return false;
			$rs = $GLOBALS['_database']->Execute("SELECT id, name, abbreviation, view_order FROM geography_countries WHERE id = ?", [$this->id]);
			if (! $rs || ! ($row = $rs->FetchRow())) {
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

		public function provinces() {
			$provinceList = new \Geography\ProvinceList();
			return $provinceList->find(array('country_id' => $this->id));
		}
	}
