<?php
	/** @class Geography\WeatherRecordList
	 * @brief Object Model for a list of weather records for a given location and time.
	 */
	namespace Geography;

	class WeatherRecordList extends \BaseListClass {
		public function __construct() {
			$this->_tableName = "geography_weather";
			$this->_modelName = "\\Geography\\WeatherRecord";
		}

		public function findAdvanced(array $parameters = [], array $advanced = [], array $controls = []): array {
			$this->clearError();
			$this->resetCount();
			$database = new \Database\Service();

			// Create Query
			$get_objects_query = "
				SELECT	id
				FROM	`".$this->_tableName."`
				WHERE	1=1
			";

			// Add Parameters
			if (!empty($parameters['zip_code_id'])) {
				$zip_code = new ZipCode($parameters['zip_code_id']);
				if (! $zip_code->id) {
					$this->invalidRequest("Zip Code with ID '".$parameters['zip_code_id']."' not found");
					return [];
				}
				$get_objects_query .= "
				AND zip_code_id = ?";
				$database->AddParam($zip_code->id);
			}

			if (!empty($parameters['timestamp_after'])) {
				$timestamp_after = get_mysql_date($parameters['timestamp_after']);
				$get_objects_query .= "
				AND date_record > ?";
				$database->AddParam($timestamp_after);
			}

			if (!empty($parameters['timestamp_before'])) {
				$timestamp_before = get_mysql_date($parameters['timestamp_before']);
				$get_objects_query .= "
				AND date_record < ?";
				$database->AddParam($timestamp_before);
			}

			// Add Sorting
			$get_objects_query .= "
				ORDER BY `date_record` DESC
			";

			// Add Limit
			if (!empty($controls['limit'])) {
				if (!empty($controls['offset'])) {
					$get_objects_query .= "
					LIMIT ?, ?";
					$database->AddParam($controls['offset']);
					$database->AddParam($controls['limit']);
				} else {
					$get_objects_query .= "
					LIMIT ?";
					$database->AddParam($controls['limit']);
				}
			}

			// Execute Query
			$results = $database->Execute($get_objects_query);
			if (! $results) {
				$this->SQLError("Error retrieving weather records: ".$database->ErrorMsg());
				app_log($this->error(), 'error');
				return [];
			}

			// Build Objects
			$objects = [];
			while ($row = $results->FetchRow()) {
				$object = new WeatherRecord($row[0]);
				if ($object->id) {
					$objects[] = $object;
					$this->incrementCount();
				}
			}
			return $objects;

		}
	}