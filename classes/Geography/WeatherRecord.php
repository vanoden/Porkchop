<?php
	/** @class Geography\WeatherRecord
	 * @brief A record of weather data for a given location and time
	 */
	namespace Geography;

	class WeatherRecord extends \BaseModel {
		public ?int $id = null;
		public ?int $zip_code_id = null;
		public ?string $date_record = null;		// Date and time of the weather record (in MySQL DATETIME format)
		public ?float $temperature = null;				// Temperature in Celsius
		public ?float $pressure = null;					// Pressure in hPa
		public ?float $humidity = null;					// Relative Humidity in percentage
		public ?float $wind_speed = null;				// Wind speed in km/h
		public ?float $wind_direction = null;			// Wind direction in degrees
		public ?float $wind_gust = null;				// Wind gust in km/h
		public ?float $visibility = null;				// Visibility in meters
		public ?string $conditions = null;				// Weather condition description (e.g. "Clear", "Rain", etc.)
		public ?bool $forecast = false;			// Boolean indicating if this record is a forecast

		public function __construct($parameters = array()) {
			$this->_tableName = "geography_weather";
			$this->_tableUKColumn = "id";
			$this->_cacheKeyPrefix = "geography.weather_record";
			$this->_addFields(
				"zip_code_id",
				"date_record",
				"temperature",
				"pressure",
				"humidity",
				"wind_speed",
				"wind_direction",
				"wind_gust",
				"visibility",
				"conditions",
				"forecast"
			);
			parent::__construct($parameters);
		}

		/** @method public get(zip_code_id, date_record)
		 * Get weather record for a given zip code and date. Date can be provided as a timestamp, date_time string, or date_record string.
		 * @param int zip_code_id ID of the zip code to get weather for
		 * @param string|int date_record Date of the weather record to get (can be a timestamp, date_time string, or date_record string)
		 * @return WeatherRecord object if found, null if not found, false if error
		 */
		public function get(int $zip_code_id, string|int $date_record) {
			// Clear Previous Errors
			$this->clearErrors();
			if (! is_int($zip_code_id) || $zip_code_id <= 0) {
				$this->error("Valid zip code ID required");
				return false;
			}
			$zipCode = new ZipCode($zip_code_id);
			if (! $zipCode->id) {
				$this->error("Invalid zip code ID");
				return false;
			}

			if (is_int($date_record)) {
				$date_record = get_mysql_date($date_record);
			}
			elseif (strtotime($date_record) !== false) {
				$date_record = get_mysql_date(strtotime($date_record));
			}
			else {
				$this->error("Valid date_record required (timestamp or date_time string)");
				return false;
			}

			// Round to the hour for comparison
			$date = new \DateTime($date_record);
			$date_record = $date->format('Y-m-d H').":00:00";

			$get_object_query = "
				SELECT	id
				FROM	`" . $this->_tableName . "`
				WHERE	zip_code_id = ?
				AND		date_record >= ?
				AND		date_record < DATE_ADD(?, INTERVAL 1 HOUR)";	// Allow for some flexibility in the date_record to account for differences in how the date might be provided (e.g. with or without time, etc.)

			// Initialize Database Service
			$database = new \Database\Service();

			// Bind Parameters
			$database->AddParam($zipCode->id);
			$database->AddParam($date_record);
			$database->AddParam($date_record);

			// Execute Query
			$result = $database->Execute($get_object_query);
			if (! $result) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			list($id) = $result->FetchRow();
			if (! $id) return false;

			$this->id = $id;
			return true;
		}

		/** @method public set(parameters)
		 * See if there is an existing weather record for the given zip code and date
		 * if so, call update, otherwise call add.
		 * @param array parameters Associative array of parameters to set. See add() for details.
		 * Required Parameters: zip_code_id, date_record (or timestamp or date_time)
		 * @return bool true if successful, false if error
		 */
		public function set($parameters = []): bool {
			// Clear Previous Errors
			$this->clearErrors();

			if (! isset($parameters['zip_code_id']) || ! is_int($parameters['zip_code_id']) || $parameters['zip_code_id'] <= 0) {
				$this->error("Zip code ID required");
				return false;
			}
			$zipCode = new ZipCode($parameters['zip_code_id']);
			if (! $zipCode->id) {
				$this->error("Invalid zip code ID");
				return false;
			}

			if (! empty($parameters['timestamp'])) {
				$date_record = get_mysql_date($parameters['timestamp']);
			}
			elseif (!empty($parameters['date_time'])) {
				$date_record = get_mysql_date($parameters['date_time']);
			}
			else {
				$date_record = get_mysql_date(time());
			}

			// Check for existing record
			if ($this->get($zipCode->id, $date_record)) {
				app_log("Existing weather record found for zip code ID {$zipCode->id} and date {$date_record}, updating record ID {$this->id}");
				return $this->update($parameters);
			}
			else {
				app_log("No existing weather record found for zip code ID {$zipCode->id} and date {$date_record}, adding new record");
				return $this->add($parameters);
			}
		}

		/** @method public add(parameters)
		 * Add new weather record.
		 * Required Parameters: zip_code_id, date_record (or timestamp or date_time)
		 * Optional Parameters: temperature (or temperature_celsius or temperature_fahrenheit), pressure, humidity, wind_speed (or wind_speed_kph or wind_speed_mps or wind_speed_mph),
		 */
		public function add($parameters = []) {
			app_log("Adding weather record with parameters: " . print_r($parameters, true));
			// Clear Previous Errors
			$this->clearErrors();

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query
			$add_record_query = "
				INSERT INTO `" . $this->_tableName . "`
				(zip_code_id, date_record)";

			// Validate Required Parameters
			if (! isset($parameters['zip_code_id']) || ! is_int($parameters['zip_code_id']) || $parameters['zip_code_id'] <= 0) {
				$this->error("Zip code ID required");
				return false;
			}
			$zipCode = new ZipCode($parameters['zip_code_id']);
			if (! $zipCode->id) {
				$this->error("Invalid zip code ID");
				return false;
			}
			$database->AddParam($zipCode->id);

			if (! empty($parameters['timestamp'])) {
				$database->AddParam(get_mysql_date($parameters['timestamp']));
			}
			elseif (!empty($parameters['date_time'])) {
				$database->AddParam(get_mysql_date($parameters['date_time']));
			}
			else {
				$database->AddParam(get_mysql_date(time()));
			}

			$add_record_query .= " VALUES (?,?)";

			// Execute Query
			if (! $database->Execute($add_record_query)) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			$this->id = $database->Insert_ID();
			app_log("Weather record added with ID {$this->id}");

			return $this->update($parameters);
		}

		/** @method public update(parameters)
		 * Update existing weather record. ID must be set.
		 * @param array parameters Associative array of parameters to update. See add() for details.
		 * @return bool true if successful, false if error
		 */
		public function update($parameters = []): bool {
			app_log("Updating weather record ID {$this->id} with parameters: " . print_r($parameters, true));
			// Clear Previous Errors
			$this->clearErrors();

			// Initialize Database Service
			$database = new \Database\Service();

			// Validate Parameters
			if (! $this->id) {
				$this->error("ID required for update");
				return false;
			}
			if (!empty($parameters['temperature_celsius'])) {
				$this->temperature = (float) $parameters['temperature_celsius'];
			}
			else if (!empty($parameters['temperature_fahrenheit'])) {
				$this->temperature = ((float) $parameters['temperature_fahrenheit'] - 32) * 5/9;
			}
			else if (!empty($parameters['temperature_kelvin'])) {
				$this->temperature = (float) $parameters['temperature_kelvin'] - 273.15;
			}
			else if (!empty($parameters['temperature'])) {
				$this->temperature = (float) $parameters['temperature'];
			}

			if (!empty($parameters['pressure'])) {
				$this->pressure = (float) $parameters['pressure'];
			}
			if (!empty($parameters['humidity'])) {
				$this->humidity = (float) $parameters['humidity'];
			}
			if (!empty($parameters['wind_speed_mps'])) {
				$this->wind_speed = (float) $parameters['wind_speed_mps'] * 3.6;
			}
			else if (!empty($parameters['wind_speed_kph'])) {
				$this->wind_speed = (float) $parameters['wind_speed_kph'];
			}
			else if (!empty($parameters['wind_speed_mph'])) {
				$this->wind_speed = (float) $parameters['wind_speed_mph'] * 1.60934;
			}
			else if (!empty($parameters['wind_speed'])) {
				$this->wind_speed = (float) $parameters['wind_speed'];
			}
			if (!empty($parameters['wind_gust_mps'])) {
				$this->wind_gust = (float) $parameters['wind_gust_mps'] * 3.6;
			}
			else if (!empty($parameters['wind_gust_kph'])) {
				$this->wind_gust = (float) $parameters['wind_gust_kph'];
			}
			else if (!empty($parameters['wind_gust_mph'])) {
				$this->wind_gust = (float) $parameters['wind_gust_mph'] * 1.60934;
			}
			else if (!empty($parameters['wind_gust'])) {
				$this->wind_gust = (float) $parameters['wind_gust'];
			}
			if (!empty($parameters['wind_direction'])) {
				$this->wind_direction = (float) $parameters['wind_direction'];
			}
			if (!empty($parameters['visibility_km'])) {
				$this->visibility = (float) $parameters['visibility_km'] * 1000;
			}
			else if (!empty($parameters['visibility_miles'])) {
				$this->visibility = (float) $parameters['visibility_miles'] * 1609.34;
			}
			else if (!empty($parameters['visibility_meters'])) {
				$this->visibility = (float) $parameters['visibility_meters'];
			}
			else if (!empty($parameters['visibility'])) {
				$this->visibility = (float) $parameters['visibility'];
			}
			if (!empty($parameters['conditions'])) {
				$this->conditions = trim($parameters['conditions']);
			}
			if (isset($parameters['forecast'])) {
				$this->forecast = (bool) $parameters['forecast'];
			}

			// Prepare Query
			$update_query = "
				UPDATE `" . $this->_tableName . "`
				SET		temperature = ?,
						pressure = ?,
						humidity = ?,
						wind_speed = ?,
						wind_direction = ?,
						wind_gust = ?,
						visibility = ?,
						conditions = ?,
						forecast = ?
				WHERE 	id = ?";

			// Bind Parameters
			$database->AddParam($this->temperature);
			$database->AddParam($this->pressure);
			$database->AddParam($this->humidity);
			$database->AddParam($this->wind_speed);
			$database->AddParam($this->wind_direction);
			$database->AddParam($this->wind_gust);
			$database->AddParam($this->visibility);
			$database->AddParam($this->conditions);
			$database->AddParam($this->forecast ? 1 : 0);
			$database->AddParam($this->id);

			// Execute Query
			if (! $database->Execute($update_query)) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			return $this->details();
		}

		public function temperatureFahrenheit(): ?float {
			if ($this->temperature === null) return null;
			return round(($this->temperature * 9/5) + 32, 2);
		}

		public function temperatureCelsius(): ?float {
			if ($this->temperature === null) return null;
			return round($this->temperature, 2);
		}

		public function temperatureKelvin(): ?float {
			if ($this->temperature === null) return null;
			return round($this->temperature + 273.15, 2);
		}

		public function windSpeedMPH(): ?float {
			if ($this->wind_speed === null) return null;
			return round($this->wind_speed * 0.621371, 2);
		}

		public function windSpeedKPH(): ?float {
			if ($this->wind_speed === null) return null;
			return round($this->wind_speed, 2);
		}

		public function windSpeedMPS(): ?float {
			if ($this->wind_speed === null) return null;
			return round($this->wind_speed * 0.277778, 2);
		}

		public function windGustMPH(): ?float {
			if ($this->wind_gust === null) return null;
			return round($this->wind_gust * 0.621371, 2);
		}

		public function windGustKPH(): ?float {
			if ($this->wind_gust === null) return null;
			return round($this->wind_gust, 2);
		}

		public function windGustMPS(): ?float {
			if ($this->wind_gust === null) return null;
			return round($this->wind_gust * 0.277778, 2);
		}

		public function visibilityKM(): ?float {
			if ($this->visibility === null) return null;
			return round($this->visibility / 1000, 2);
		}

		public function visibilityMiles(): ?float {
			if ($this->visibility === null) return null;
			return round($this->visibility * 0.000621371, 2);
		}

		public function visibilityMeters(): ?float {
			if ($this->visibility === null) return null;
			return round($this->visibility, 2);
		}

		public function dateLocal($timezone = 'UTC'): ?string {
			if ($this->date_record === null) return null;
			$date = new \DateTime($this->date_record, new \DateTimeZone('UTC'));
			$date->setTimezone(new \DateTimeZone($GLOBALS['_SESSION_']->timezone));
			return $date->format('Y-m-d H:i:s');
		}

		public function dateAtSite(): ?string {
			$timeinfo = new \Geography\TimesRecord($this->zip_code_id);
			if (! $timeinfo->id) return null;
			$date = new \DateTime($this->date_record, new \DateTimeZone('UTC'));
			$date->setTimezone(new \DateTimeZone($timeinfo->timezone));
			return $date->format('Y-m-d H:i:s');
		}
	}	