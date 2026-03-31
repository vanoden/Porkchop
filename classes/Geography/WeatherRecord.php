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

		/** @method public add(parameters)
		 * Add new weather record.
		 * Required Parameters: zip_code_id, date_record (or timestamp or date_time)
		 * Optional Parameters: temperature (or temperature_celsius or temperature_fahrenheit), pressure, humidity, wind_speed (or wind_speed_kph or wind_speed_mps or wind_speed_mph),
		 */
		public function add($parameters = []) {
			// Clear Previous Errors
			$this->clearErrors();

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query
			$add_record_query = "
				INSERT INTO `" . $this->_tableName . "`
				(zip_code_id, date_record";

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

			if (!empty($_REQUEST['conditions'])) {
				$add_record_query .= ", conditions";
				$database->AddParam(trim($_REQUEST['conditions']));
			}

			if (!empty($_REQUEST['temperature_celsius'])) {
				$add_record_query .= ", temperature";
				$database->AddParam((float) $_REQUEST['temperature_celsius']);
			}
			else if (!empty($_REQUEST['temperature_fahrenheit'])) {
				$add_record_query .= ", temperature";
				$database->AddParam(((float) $_REQUEST['temperature_fahrenheit'] - 32) * 5/9);
			}

			if (!empty($_REQUEST['humidity'])) {
				$add_record_query .= ", humidity";
				$database->AddParam((float) $_REQUEST['humidity']);
			}

			if (!empty($_REQUEST['pressure'])) {
				$add_record_query .= ", pressure";
				$database->AddParam((float) $_REQUEST['pressure']);
			}

			if (!empty($_REQUEST['wind_speed_mps'])) {
				$add_record_query .= ", wind_speed";
				$database->AddParam((float) $_REQUEST['wind_speed_mps'] * 3.6);
			}
			else if (!empty($_REQUEST['wind_speed_kph'])) {
				$add_record_query .= ", wind_speed";
				$database->AddParam((float) $_REQUEST['wind_speed_kph']);
			}
			else if (!empty($_REQUEST['wind_speed_mph'])) {
				$add_record_query .= ", wind_speed";
				$database->AddParam((float) $_REQUEST['wind_speed_mph'] * 1.60934);
			}

			if (!empty($_REQUEST['wind_gust_mps'])) {
				$add_record_query .= ", wind_gust";
				$database->AddParam((float) $_REQUEST['wind_gust_mps'] * 3.6);
			}
			else if (!empty($_REQUEST['wind_gust_kph'])) {
				$add_record_query .= ", wind_gust";
				$database->AddParam((float) $_REQUEST['wind_gust_kph']);
			}
			else if (!empty($_REQUEST['wind_gust_mph'])) {
				$add_record_query .= ", wind_gust";
				$database->AddParam((float) $_REQUEST['wind_gust_mph'] * 1.60934);
			}

			if (!empty($_REQUEST['wind_direction'])) {
				$add_record_query .= ", wind_direction";
				$database->AddParam((float) $_REQUEST['wind_direction']);
			}

			if (!empty($_REQUEST['visibility_km'])) {
				$add_record_query .= ", visibility";
				$database->AddParam((float) $_REQUEST['visibility_km'] * 1000);
			}
			else if (!empty($_REQUEST['visibility_miles'])) {
				$add_record_query .= ", visibility";
				$database->AddParam((float) $_REQUEST['visibility_miles'] * 1609.34);
			}
			else if (!empty($_REQUEST['visibility_meters'])) {
				$add_record_query .= ", visibility";
				$database->AddParam((float) $_REQUEST['visibility_meters']);
			}

			if (!isset($_REQUEST['precipitation_mm'])) {
				$add_record_query .= ", precipitation";
				$database->AddParam((float) $_REQUEST['precipitation_mm']);
			}
			else if (!isset($_REQUEST['precipitation_inches'])) {
				$add_record_query .= ", precipitation";
				$database->AddParam((float) $_REQUEST['precipitation_inches'] * 25.4);
			}

			$add_record_query .= ") VALUES (";
			$paramCount = count($database->Parameters());
			$add_record_query .= rtrim(str_repeat("?, ", $paramCount), ", ") . ")";

			// Execute Query
			if (! $database->Execute($add_record_query)) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			list($id) = $database->Insert_ID();
			$this->id = $id;

			return true;
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