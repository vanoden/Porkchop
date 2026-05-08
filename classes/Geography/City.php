<?php
	/** @class Geography\City
	 * Object Model for a City within a County within a Province/State/Region within a Country.
	 */
	namespace Geography;

	class City extends \BaseModel {
		public ?string $code = null;
		public string $name = "";
		public int $province_id = 0;
		public ?int $county_id = 0;
		public ?float $latitude = 0.0;
		public ?float $longitude = 0.0;

		public function __construct(int $id = 0) {
			$this->_tableName = "geography_cities";
			$this->_tableUKColumn = "code";
			$this->_cacheKeyPrefix = "geography.city";
			parent::__construct($id);
		}

		public function add($parameters = []): bool {
			$this->clearError();

			if (empty($parameters['code'])) {
				$porkchop = new \Porkchop();
				$parameters['code'] = $porkchop->biguuid();
			}
			if (empty($parameters['latitude'])) {
				$parameters['latitude'] = 0;
			}
			if (empty($parameters['longitude'])) {
				$parameters['longitude'] = 0;
			}

			return parent::add($parameters);
		}

		public function get($province_id, $name): bool {
			$this->clearError();
			$database = new \Database\Service();

			$get_object_query = "
				SELECT	id
				FROM	`".$this->_tableName."`
				WHERE	province_id = ?
				AND		name = ?
			";
			
			$database->AddParam($province_id);
			$database->AddParam($name);

			$result = $database->Execute($get_object_query);
			if (! $result) {
				$this->SQLError("Error retrieving city by province ID and name in Geography\\City::get(): ".$database->ErrorMsg());
				app_log($this->error(), 'error');
				return false;
			}
			list($city_id) = $result->FetchRow();
			if ($city_id) {
				$this->id = $city_id;
				$this->details();
				return true;
			}
			else {
				$this->error("City with name '".$name."' not found in province with ID '".$province_id."'");
				app_log($this->error(), 'error');
				return false;
			}
		}

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

	}
