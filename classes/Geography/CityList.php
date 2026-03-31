<?php
	/** @class Geography\CityList
	 * Object Model for a list of Cities within a County within a Province/State/Region within a Country.
	 */
	namespace Geography;

	class CityList extends \BaseListClass {
		public function __construct() {
			$this->_tableName = "geography_cities";
			$this->_modelName = "Geography\City";
		}

		public function findAdvanced($parameters = [], $advanced = [], $controls = []): array {
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
			if (!empty($parameters['country_id'])) {
				$country = new Country($parameters['country_id']);
				if (! $country->id) {
					$this->invalidRequest("Country with ID '".$parameters['country_id']."' not found");
					return [];
				}
			}
			elseif (!empty($parameters['country_abbreviation'])) {
				$country = new Country();
				if (! $country->getByAbbreviation($parameters['country_abbreviation'])) {
					$this->invalidRequest("Country with abbreviation '".$parameters['country_abbreviation']."' not found");
					return [];
				}
			}
			if (!empty($parameters['province_id'])) {
				$province = new Province($parameters['province_id']);
				if (! $province->id) {
					$this->invalidRequest("Province with ID '".$parameters['province_id']."' not found");
					return [];
				}
				$get_objects_query .= "
				AND province_id = ?";
				$database->AddParam($province->id);
			}
			elseif (!empty($parameters['province_abbreviation'])) {
				$province = new Province();
				if (! $province->getByAbbreviation($country->id,$parameters['province_abbreviation'])) {
					$this->invalidRequest("Province with abbreviation '".$parameters['province_abbreviation']."' not found");
					return [];
				}
				$get_objects_query .= "
				AND province_id = ?";
				$database->AddParam($province->id);
			}
			if (!empty($parameters['county_id'])) {
				$county = new County($parameters['county_id']);
				if (! $county->id) {
					$this->invalidRequest("County with ID '".$parameters['county_id']."' not found");
					return [];
				}
				$get_objects_query .= "
				AND county_id = ?";
				$database->AddParam($county->id);
			}

			if (!empty($parameters['name'])) {
				$get_objects_query .= "
				AND name = ?";
				$database->AddParam($parameters['name']);
			}

			// Add Sorting
			$get_objects_query .= "
				ORDER BY name ASC
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
				$this->SQLError("Error retrieving cities in Geography\\CityList::findAdvanced(): ".$database->ErrorMsg());
				app_log($this->error(), 'error');
				return [];
			}

			// Build Objects
			$objects = [];
			while ($object = $results->FetchNextObject(false)) {
				$objects[] = new City($object->id);
				$this->incrementCount();
			}
			return $objects;
		}
	}