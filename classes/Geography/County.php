<?php
	/** @class Geography\County
	 * Object Model for a County within a Province/State/Region within a Country.
	 */
	namespace Geography;

	class County extends \BaseModel {
		public ?string $code = null;
		public string $name = "";
		public int $province_id = 0;

		public function __construct(int $id = 0) {
			$this->_tableName = "geography_counties";
			$this->_tableUKColumn = "code";
			$this->_cacheKeyPrefix = "geography.county";
			$this->_addFields('code', 'name', 'province_id');
			parent::__construct($id);
		}

		/** @method public add(parameters)
		 * Add a new County to the database.
		 * Required parameters are 'name' and 'province_id'.
		 * Optional parameter is 'code' (unique abbreviation/code for the county).
		 * If 'code' is not provided, a unique code will be generated automatically.
		 */
		public function add($parameters = []): bool {
			$this->clearError();

			if (empty($parameters['code'])) {
				$porkchop = new \Porkchop();
				$parameters['code'] = $porkchop->biguuid();
			}
			return parent::add($parameters);
		}

		/** @method public get(province_id, name)
		 * Retrieve a County by its province ID and name.
		 * Returns true if the County is found, false otherwise.
		 */
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
				$this->SQLError("Error retrieving county by province ID and name in Geography\\County::get(): ".$database->ErrorMsg());
				app_log($this->error(), 'error');
				return false;
			}

			$object = $result->FetchNextObject(false);
			if ($object) {
				$this->id = $object->id;
				return $this->details();
			}
			else {
				$this->error("County with name '".$name."' not found in province with ID '".$province_id."'");
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
	}