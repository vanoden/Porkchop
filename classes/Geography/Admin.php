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
			$database->AddParam($country_id);
			$database->AddParam(trim((string) $abbrev));
			$rs = $database->Execute($get_object_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			list($this->id) = $rs->FetchRow();
			if (empty($this->id)) return false;
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

		public function getByZipCode(string $zip_code, $countryAbbrev = ''): bool {
			if (empty($countryAbbrev)) {
				if (preg_match('/^(\d{5})$/', $zip_code, $matches)) {
					$countryAbbrev = 'US';
				}
				elseif (preg_match('/^(\d{5}-\d{4})$/', $zip_code, $matches)) {
					$countryAbbrev = 'US';
				}
				else {
					return false;
				}
			}
			if ($countryAbbrev !== 'US') {
				return false;
			}
			$zip_code = (int) $zip_code;
			if ($zip_code >= 99501 && $zip_code <= 99950) {
				$provinceAbbrev = 'AK';
			}
			elseif ($zip_code >= 35004 && $zip_code <= 36925) {
				$provinceAbbrev = 'AL';
			}
			elseif ($zip_code >= 71601 && $zip_code <= 72959) {
				$provinceAbbrev = 'AR';
			}
			elseif ($zip_code >= 75502 && $zip_code <= 75502) {
				$provinceAbbrev = 'AR';
			}
			elseif ($zip_code >= 85001 && $zip_code <= 86556) {
				$provinceAbbrev = 'AZ';
			}
			elseif ($zip_code >= 90001 && $zip_code <= 96162) {
				$provinceAbbrev = 'CA';
			}
			elseif ($zip_code >= 80001 && $zip_code <= 81658) {
				$provinceAbbrev = 'CO';
			}
			elseif ($zip_code >= 6001 && $zip_code <= 6389) {
				$provinceAbbrev = 'CT';
			}
			elseif ($zip_code >= 6401 && $zip_code <= 6928) {
				$provinceAbbrev = 'CT';
			}
			elseif ($zip_code >= 20001 && $zip_code <= 20039) {
				$provinceAbbrev = 'DC';
			}
			elseif ($zip_code >= 20042 && $zip_code <= 20599) {
				$provinceAbbrev = 'DC';
			}
			elseif ($zip_code >= 20799 && $zip_code <= 20799) {
				$provinceAbbrev = 'DC';
			}
			elseif ($zip_code >= 19701 && $zip_code <= 19980) {
				$provinceAbbrev = 'DE';
			}
			elseif ($zip_code >= 32004 && $zip_code <= 34997) {
				$provinceAbbrev = 'FL';
			}
			elseif ($zip_code >= 30001 && $zip_code <= 31999) {
				$provinceAbbrev = 'GA';
			}
			elseif ($zip_code >= 39901 && $zip_code <= 39901) {
				$provinceAbbrev = 'GA';
			}
			elseif ($zip_code >= 96701 && $zip_code <= 96898) {
				$provinceAbbrev = 'HI';
			}
			elseif ($zip_code >= 50001 && $zip_code <= 52809) {
				$provinceAbbrev = 'IA';
			}
			elseif ($zip_code >= 68119 && $zip_code <= 68120) {
				$provinceAbbrev = 'IA';
			}
			elseif ($zip_code >= 83201 && $zip_code <= 83876) {
				$provinceAbbrev = 'ID';
			}
			elseif ($zip_code >= 60001 && $zip_code <= 62999) {
				$provinceAbbrev = 'IL';
			}
			elseif ($zip_code >= 46001 && $zip_code <= 47997) {
				$provinceAbbrev = 'IN';
			}
			elseif ($zip_code >= 66002 && $zip_code <= 67954) {
				$provinceAbbrev = 'KS';
			}
			elseif ($zip_code >= 40003 && $zip_code <= 42788) {
				$provinceAbbrev = 'KY';
			}
			elseif ($zip_code >= 70001 && $zip_code <= 71232) {
				$provinceAbbrev = 'LA';
			}
			elseif ($zip_code >= 71234 && $zip_code <= 71497) {
				$provinceAbbrev = 'LA';
			}
			elseif ($zip_code >= 1001 && $zip_code <= 2791) {
				$provinceAbbrev = 'MA';
			}
			elseif ($zip_code >= 5501 && $zip_code <= 5544) {
				$provinceAbbrev = 'MA';
			}
			elseif ($zip_code >= 20331 && $zip_code <= 20331) {
				$provinceAbbrev = 'MD';
			}
			elseif ($zip_code >= 20335 && $zip_code <= 20797) {
				$provinceAbbrev = 'MD';
			}
			elseif ($zip_code >= 20812 && $zip_code <= 21930) {
				$provinceAbbrev = 'MD';
			}
			elseif ($zip_code >= 3901 && $zip_code <= 4992) {
				$provinceAbbrev = 'ME';
			}
			elseif ($zip_code >= 48001 && $zip_code <= 49971) {
				$provinceAbbrev = 'MI';
			}
			elseif ($zip_code >= 55001 && $zip_code <= 56763) {
				$provinceAbbrev = 'MN';
			}
			elseif ($zip_code >= 63001 && $zip_code <= 65899) {
				$provinceAbbrev = 'MO';
			}
			elseif ($zip_code >= 38601 && $zip_code <= 39776) {
				$provinceAbbrev = 'MS';
			}
			elseif ($zip_code >= 71233 && $zip_code <= 71233) {
				$provinceAbbrev = 'MS';
			}
			elseif ($zip_code >= 59001 && $zip_code <= 59937) {
				$provinceAbbrev = 'MT';
			}
			elseif ($zip_code >= 27006 && $zip_code <= 28909) {
				$provinceAbbrev = 'NC';
			}
			elseif ($zip_code >= 58001 && $zip_code <= 58856) {
				$provinceAbbrev = 'ND';
			}
			elseif ($zip_code >= 68001 && $zip_code <= 68118) {
				$provinceAbbrev = 'NE';
			}
			elseif ($zip_code >= 68122 && $zip_code <= 69367) {
				$provinceAbbrev = 'NE';
			}
			elseif ($zip_code >= 3031 && $zip_code <= 3897) {
				$provinceAbbrev = 'NH';
			}
			elseif ($zip_code >= 7001 && $zip_code <= 8989) {
				$provinceAbbrev = 'NJ';
			}
			elseif ($zip_code >= 87001 && $zip_code <= 88441) {
				$provinceAbbrev = 'NM';
			}
			elseif ($zip_code >= 88901 && $zip_code <= 89883) {
				$provinceAbbrev = 'NV';
			}
			elseif ($zip_code >= 6390 && $zip_code <= 6390) {
				$provinceAbbrev = 'NY';
			}
			elseif ($zip_code >= 10001 && $zip_code <= 14975) {
				$provinceAbbrev = 'NY';
			}
			elseif ($zip_code >= 43001 && $zip_code <= 45999) {
				$provinceAbbrev = 'OH';
			}
			elseif ($zip_code >= 73001 && $zip_code <= 73199) {
				$provinceAbbrev = 'OK';
			}
			elseif ($zip_code >= 73401 && $zip_code <= 74966) {
				$provinceAbbrev = 'OK';
			}
			elseif ($zip_code >= 97001 && $zip_code <= 97920) {
				$provinceAbbrev = 'OR';
			}
			elseif ($zip_code >= 15001 && $zip_code <= 19640) {
				$provinceAbbrev = 'PA';
			}
			elseif ($zip_code >= 2801 && $zip_code <= 2940) {
				$provinceAbbrev = 'RI';
			}
			elseif ($zip_code >= 29001 && $zip_code <= 29948) {
				$provinceAbbrev = 'SC';
			}
			elseif ($zip_code >= 57001 && $zip_code <= 57799) {
				$provinceAbbrev = 'SD';
			}
			elseif ($zip_code >= 37010 && $zip_code <= 38589) {
				$provinceAbbrev = 'TN';
			}
			elseif ($zip_code >= 73301 && $zip_code <= 73301) {
				$provinceAbbrev = 'TX';
			}
			elseif ($zip_code >= 75001 && $zip_code <= 75501) {
				$provinceAbbrev = 'TX';
			}
			elseif ($zip_code >= 75503 && $zip_code <= 79999) {
				$provinceAbbrev = 'TX';
			}
			elseif ($zip_code >= 88510 && $zip_code <= 88589) {
				$provinceAbbrev = 'TX';
			}
			elseif ($zip_code >= 84001 && $zip_code <= 84784) {
				$provinceAbbrev = 'UT';
			}
			elseif ($zip_code >= 20040 && $zip_code <= 20041) {
				$provinceAbbrev = 'VA';
			}
			elseif ($zip_code >= 20040 && $zip_code <= 20167) {
				$provinceAbbrev = 'VA';
			}
			elseif ($zip_code >= 20042 && $zip_code <= 20042) {
				$provinceAbbrev = 'VA';
			}
			elseif ($zip_code >= 22001 && $zip_code <= 24658) {
				$provinceAbbrev = 'VA';
			}
			elseif ($zip_code >= 5001 && $zip_code <= 5495) {
				$provinceAbbrev = 'VT';
			}
			elseif ($zip_code >= 5601 && $zip_code <= 5907) {
				$provinceAbbrev = 'VT';
			}
			elseif ($zip_code >= 98001 && $zip_code <= 99403) {
				$provinceAbbrev = 'WA';
			}
			if ($provinceAbbrev) {
				$country = new \Geography\Country();
				if ($country->getByAbbreviation($countryAbbrev)) {
					$province = new \Geography\Province();
					if ($this->getByAbbreviation($country->id, $provinceAbbrev)) {
						return true;
					}
					else {
						$this->error("Province with abbreviation $provinceAbbrev not found in country $countryAbbrev");
						return false;
					}
				}
				else {
					$this->error("Country with abbreviation $countryAbbrev not found");
					return false;
				}
			}
			return false;
		}
	}
