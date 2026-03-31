<?php
	/** @class Geography\ZipCode
	 * Object Model for a Zip Code within a County within a Province/State/Region within a Country.
	 */
	namespace Geography;

	class ZipCode extends \BaseModel {
		public ?string $code = null;
		public ?int $county_id = 0;
		public ?int $city_id = 0;
		public ?int $province_id = 0;
		public ?float $latitude = 0.0;
		public ?float $longitude = 0.0;

		public function __construct(int $id = 0) {
			$this->_tableName = "geography_zip_codes";
			$this->_cacheKeyPrefix = "geography.zip_code";
			parent::__construct($id);
		}

		/** @method add(parameters)
		 * Add new zip code.
		 * Required Parameters: code, country_id (associated with province_id), province_id
		 * Optional Parameters: county_id, city_id, latitude, longitude
		 * @param array $parameters
		 * @return bool
		 */
		public function add($parameters = []) {
			// Clear Previous Errors
			$this->clearErrors();

			// Initialize Database Service
			$database = new \Database\Service();

			// Validate Required Parameters
			if (! isset($parameters['code']) || ! is_string($parameters['code']) || trim($parameters['code']) === '') {
				$this->error("Zip code code required");
				return false;
			}
			if (! isset($parameters['province_id']) || ! is_int($parameters['province_id']) || $parameters['province_id'] <= 0) {
				$this->error("Province ID required");
				return false;
			}
			$code = trim($parameters['code']);
			$province = new Province($parameters['province_id']);
			if (! $province->id) {
				$this->error("Invalid province ID");
				return false;
			}
			$county = new County(isset($parameters['county_id']) ? (int) $parameters['county_id'] : 0);
			if (isset($parameters['county_id']) && $parameters['county_id'] > 0 && ! $county->id) {
				$this->error("Invalid county ID");
				return false;
			}
			$city = new City(isset($parameters['city_id']) ? (int) $parameters['city_id'] : 0);
			if (isset($parameters['city_id']) && $parameters['city_id'] > 0 && ! $city->id) {
				$this->error("Invalid city ID");
				return false;
			}
			$latitude = isset($parameters['latitude']) ? (float) $parameters['latitude'] : 0.0;
			$longitude = isset($parameters['longitude']) ? (float) $parameters['longitude'] : 0.0;

			$add_object_query = "
				INSERT INTO geography_zip_codes (code, province_id, county_id, city_id, latitude, longitude)
				VALUES (?, ?, ?, ?, ?, ?)
			";
			$database->AddParam($code);
			$database->AddParam($province->id);
			$database->AddParam($county->id);
			$database->AddParam($city->id);
			$database->AddParam($latitude);
			$database->AddParam($longitude);
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

		/** @method public update(parameters)
		 * Update zip code.
			 * Optional Parameters: code, province_id, county_id, city_id, latitude, longitude
			 * @param array $parameters
			 * @return bool
		 */
		public function update($parameters = []): bool {
			// Clear Previous Errors
			$this->clearErrors();

			// Initialize Database Service
			$database = new \Database\Service();

			$code = isset($parameters['code']) && is_string($parameters['code']) && trim($parameters['code']) !== '' ? trim($parameters['code']) : null;
			if (!empty($parameters['province_id']) && is_int($parameters['province_id']) && $parameters['province_id'] > 0) {
				$province = new Province($parameters['province_id']);
				if (! $province->id) {
					$this->error("Invalid province ID");
					return false;
				}
			}
			if (!empty($parameters['county_id']) && is_int($parameters['county_id']) && $parameters['county_id'] > 0) {
				$county = new County($parameters['county_id']);
				if (! $county->id) {
					$this->error("Invalid county ID");
					return false;
				}
			}
			if (!empty($parameters['city_id']) && is_int($parameters['city_id']) && $parameters['city_id'] > 0) {
				$city = new City($parameters['city_id']);
				if (! $city->id) {
					$this->error("Invalid city ID");
					return false;
				}
			}

			$update_object_query = "
				UPDATE	geography_zip_codes
				SET		id = id";

			$auditMessages = [];
			if (!empty($code)) {
				$update_object_query .= ",
				code = ?";
				$database->AddParam($code);
				$auditMessages[] = "Code changed to '" . $code . "'";
			}
			if (!empty($province_id)) {
				$update_object_query .= ",
				province_id = ?";
				$database->AddParam($province->id);
				$auditMessages[] = "Province changed to '" . $province->name . "'";
			}
			if (!empty($county_id)) {
				$update_object_query .= ",
				county_id = ?";
				$database->AddParam($county->id);
				$auditMessages[] = "County changed to '" . $county->name . "'";
			}
			if (!empty($city_id)) {
				$update_object_query .= ",
				city_id = ?";
				$database->AddParam($city->id);
				$auditMessages[] = "City changed to '" . $city->name . "'";

			}
			if (!empty($latitude)) {
				$update_object_query .= ",
				latitude = ?";
				$database->AddParam($latitude);
				$auditMessages[] = "Latitude changed to '" . $latitude . "'";

			}
			if (!empty($longitude)) {
				$update_object_query .= ",
				longitude = ?";
				$database->AddParam($longitude);
				$auditMessages[] = "Longitude changed to '" . $longitude . "'";
			}

			$update_object_query .= "
				WHERE id = ?";
			$database->AddParam($this->id);

			// Execute Query
			$database->Execute($update_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			$this->recordAuditEvent($this->id, implode("; ", $auditMessages));
			return $this->details();
		}

		/** @method public get(province_id, code) 
		 * Retrieve a Zip Code by its province ID and code.
		 * Returns true if the Zip Code is found, false otherwise.
		*/
		public function get($province_id, $code): bool {
			$this->clearError();
			$database = new \Database\Service();

			$get_object_query = "
				SELECT	id
				FROM	`".$this->_tableName."`
				WHERE	province_id = ?
				AND		code = ?
			";

			$database->AddParam($province_id);
			$database->AddParam($code);
			$database->trace(9);
			$database->debug = 'log';
			$result = $database->Execute($get_object_query);
			if (! $result) {
				$this->SQLError("Error retrieving zip code by province ID and code in Geography\\ZipCode::get(): ".$database->ErrorMsg());
				app_log($this->error(), 'error');
				return false;
			}
			list($zip_code_id) = $result->FetchRow();
			if ($zip_code_id) {
				$this->id = $zip_code_id;
				return $this->details();
			}
			else {
				$this->error("Zip Code with code '".$code."' not found in province with ID '".$province_id."'");
				app_log($this->error(), 'error');
				return false;
			}
		}

		 /** @method public findAdvanced(parameters, controls)
		 * Retrieve Zip Codes matching specified parameters.
		 * Parameters: country_id, province_id, province_abbreviation, name
		 * Controls: limit, offset
		 * Returns array of Zip Code objects matching criteria.
		 */

		public function country(): ?Country {
			if ($this->province_id) {
				$province = new Province($this->province_id);
				if ($province->id) {
					return new Country($province->country_id);
				}
			}
			return null;
		}

		public function province(): ?Province {
			if ($this->province_id) {
				return new Province($this->province_id);
			}
			return null;
		}

		public function county(): ?County {
			if ($this->county_id) {
				return new County($this->county_id);
			}
			return null;
		}

		public function city(): ?City {
			if ($this->city_id) {
				return new City($this->city_id);
			}
			return null;
		}

	}