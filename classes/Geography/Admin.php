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

		public function add($parameters = []) {
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

			$add_object_query = "
				INSERT INTO geography_provinces (code, country_id, name, type, abbreviation, label)
				VALUES (?, ?, ?, ?, ?, ?)
			";
			$GLOBALS['_database']->Execute($add_object_query, [$code, $country->id, $name, $type, $abbreviation, $label]);
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
			if (empty($this->id)) {
				$this->error("id required for update");
				return false;
			}

			$update_object_query = "UPDATE geography_provinces SET id = id";
			$bind_params = [];
			if (isset($parameters['name'])) {
				$update_object_query .= ", name = ?";
				$bind_params[] = trim((string) $parameters['name']);
			}
			if (isset($parameters['country_id'])) {
				$update_object_query .= ", country_id = ?";
				$bind_params[] = (int) $parameters['country_id'];
			}
			if (isset($parameters['abbreviation'])) {
				$update_object_query .= ", abbreviation = ?";
				$bind_params[] = trim((string) $parameters['abbreviation']);
			}
			if (isset($parameters['code'])) {
				$update_object_query .= ", code = ?";
				$bind_params[] = trim((string) $parameters['code']);
			}
			if (array_key_exists('type', $parameters)) {
				$update_object_query .= ", type = ?";
				$bind_params[] = $parameters['type'] === '' || $parameters['type'] === null ? null : trim((string) $parameters['type']);
			}
			if (array_key_exists('label', $parameters)) {
				$update_object_query .= ", label = ?";
				$bind_params[] = $parameters['label'] === '' || $parameters['label'] === null ? null : trim((string) $parameters['label']);
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

			return $this->details();
		}

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

		public function getProvince($country_id, $name): bool {
            app_log("Country $country_id Name $name");
			if (strlen($name) < 3) return $this->getByAbbreviation($country_id,$name);
			$get_object_query = "
				SELECT	id
				FROM	geography_provinces
				WHERE	country_id = ?
				AND		name = ?
			";

			$rs = $GLOBALS['_database']->Execute($get_object_query,array($country_id,$name));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
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

		public function getByAbbreviation($country_id,$abbrev) {
			$get_object_query = "
				SELECT	id
				FROM	geography_provinces
				WHERE	country_id = ?
				AND		abbreviation = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_object_query,array($country_id,$abbrev));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			list($this->id) = $rs->FetchRow();
			return $this->details();
		}
		public function details(): bool {
			if (empty($this->id)) return false;
			$rs = $GLOBALS['_database']->Execute("SELECT id, code, country_id, name, type, abbreviation, label FROM geography_provinces WHERE id = ?", [$this->id]);
			if (! $rs || ! ($row = $rs->FetchRow())) {
				$this->id = null;
				$this->country_id = null;
				$this->name = null;
				$this->abbreviation = null;
				$this->code = null;
				$this->type = null;
				$this->label = null;
				return false;
			}
			$row = (array) $row;
			$this->id = (int) ($row['id'] ?? $row[0]);
			$this->country_id = (int) ($row['country_id'] ?? $row[1]);
			$this->name = (string) ($row['name'] ?? $row[2]);
			$this->type = isset($row['type']) ? (string) $row['type'] : null;
			$this->abbreviation = (string) ($row['abbreviation'] ?? $row[5]);
			$this->label = isset($row['label']) ? (string) $row['label'] : null;
			$this->code = (string) ($row['code'] ?? $row[1]);
			return true;
		}

		public function country() {
			return new \Geography\Country($this->country_id);
		}
	}
