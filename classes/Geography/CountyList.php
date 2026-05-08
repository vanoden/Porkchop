<?php
	/** @class Geography\CountyList
	 * Object Model for a list of Counties within a Province/State/Region within a Country.
	 */
	namespace Geography;

	class CountyList extends \BaseListClass {
		public function __construct() {
			$this->_tableName = "geography_counties";
			$this->_modelName = "Geography\County";
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
				if (! $province->getByAbbreviation($parameters['country_id'] ?? null, $parameters['province_abbreviation'])) {
					$this->invalidRequest("Province with abbreviation '".$parameters['province_abbreviation']."' not found");
					return [];
				}
				$get_objects_query .= "
				AND province_id = ?";
				$database->AddParam($province->id);
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
					$this->AddParam($controls['offset']);
					$this->AddParam($controls['limit']);
				} else {
					$get_objects_query .= "
					LIMIT ?";
					$this->AddParam($controls['limit']);
				}
			}

			// Execute Query
			$results = $database->Execute($get_objects_query);
			if (! $results) {
				$this->SQLError("Error retrieving counties in Geography\\CountyList::findAdvanced(): ".$database->ErrorMsg());
				app_log($this->error(), 'error');
				return [];
			}

			// Build Objects
			$objects = [];
			while (list($id) = $results->FetchRow()) {
				$objects[] = new $this->_modelName($id);
			}

			return $objects;
		}
	}