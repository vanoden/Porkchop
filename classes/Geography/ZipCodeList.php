<?php
	/** @class Geography\ZipCodeList
	 * Object Model for a list of Zip Codes within a County within a Province/State/Region within a Country.
	 */
	namespace Geography;

	class ZipCodeList extends \BaseListClass {
		public function __construct() {
			$this->_tableName = "geography_zip_codes";
			$this->_modelName = "\\Geography\\ZipCode";
		}

		public function findAdvanced(array $parameters = [], array $advanced = [], array $controls = []): array{
			// Clear Previous Errors
			$this->clearErrors();

			// Reset Count
			$this->_count = 0;

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$query = "
				SELECT	*
				FROM	`" . $this->_tableName . "`
				WHERE	id = id";

			// Add Parameters
			if (!empty($parameters['id']) && is_int($parameters['id']) && $parameters['id'] > 0) {
				$query .= " AND id = ?";
				$database->AddParam($parameters['id']);
			}

			if (!empty($parameters['code']) && is_string($parameters['code']) && trim($parameters['code']) !== '') {
				$query .= " AND code = ?";
				$database->AddParam(trim($parameters['code']));
			}

			if (!empty($parameters['country_name']) && is_string($parameters['country_name']) && trim($parameters['country_name']) !== '') {
				$country = new Country();
				if (! $country->get(trim($parameters['country_name']))) {
					$this->error("Country not found");
					return [];
				}
				$query .= "
				AND country_id = ?";
				$database->AddParam($country->id);
			}
			elseif (!empty($parameters['country_abbreviation']) && is_string($parameters['country_abbreviation']) && trim($parameters['country_abbreviation']) !== '') {
				$country = new Country();
				if (! $country->getByAbbreviation(trim($parameters['country_abbreviation']))) {
					$this->error("Country not found");
					return [];
				}
				$query .= "
				AND country_id = ?";
				$database->AddParam($country->id);
			}
			if (!empty($parameters['province_abbreviation']) && is_string($parameters['province_abbreviation']) && trim($parameters['province_abbreviation']) !== '') {
				if (empty($country)) {
					$this->error("Country must be specified when filtering by province abbreviation");
					return [];
				}
				$province = new Province();
				if (! $province->getByAbbreviation($country->id,trim($parameters['province_abbreviation']))) {
					$this->error("Province not found");
					return [];
				}
				$query .= "
				AND province_id = ?";
				$database->AddParam($province->id);
			}
			elseif (!empty($parameters['province_name']) && is_string($parameters['province_name']) && trim($parameters['province_name']) !== '') {
				if (empty($country)) {
					$this->error("Country must be specified when filtering by province name");
					return [];
				}
				$province = new Province();
				if (! $province->get(trim($parameters['province_name']), $country->id)) {
					$this->error("Province not found");
					return [];
				}
				$query .= "
				AND province_id = ?";
				$database->AddParam($province->id);
			}

			// Sort Clause
			$query .= " ORDER BY code ASC";

			// Limit Clause
			if (isset($controls['limit']) && is_int($controls['limit']) && $controls['limit'] > 0) {
				$query .= " LIMIT ?";
				$database->AddParam($controls['limit']);
			}

			// Execute Query
			$rs = $database->Execute($query);
			if ($rs === false) {
				$this->SQLError("Database error in " . __METHOD__ . ": " . $database->ErrorMsg());
				return [];
			}

			// Process Results
			$results = [];
			while ($row = $rs->FetchRow()) {
				$zipCode = new ZipCode();
				$zipCode->id = (int) $row['id'];
				$zipCode->code = $row['code'];
				$zipCode->province_id = (int) $row['province_id'];
				$zipCode->county_id = isset($row['county_id']) ? (int) $row['county_id'] : null;
				$results[] = $zipCode;
			}

			return $results;
		}
	}