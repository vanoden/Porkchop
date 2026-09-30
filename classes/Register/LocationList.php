<?php
	namespace Register;

	class LocationList Extends \BaseListClass {
		public function __construct() {
			$this->_tableName = 'register_locations';
			$this->_modelName = '\Register\Location';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			$this->applyControls($controls);

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$find_objects_query = "
				SELECT  rl.id
				FROM    register_locations rl
				LEFT OUTER JOIN register_organization_locations rol
				ON		rl.id = rol.location_id
				WHERE   rl.id = rl.id
			";

			// Search Filters
			if (!empty($parameters['organization_id'])) {
				$organization = new \Register\Organization($parameters['organization_id']);
				if (! $organization->id) {
					$this->error("Organization not found");
				}
				$find_objects_query .= " AND rol.organization_id = ?";
				$database->AddParam($parameters['organization_id']);
			}
			if (!empty($parameters['zip_code'])) {
				// Validate Zip Code
				$zip_code = new \Geography\ZipCode();
				if (! $zip_code->validZipCode($parameters['zip_code'])) {
					$this->error("Invalid zip code");
				}
				$find_objects_query .= " AND rl.zip_code = ?";
				$database->AddParam($parameters['zip_code']);
			}
			if (!empty($parameters['city'])) {
				$find_objects_query .= " AND rl.city = ?";
				$database->AddParam($parameters['city']);
			}
			if (!empty($parameters['state_code'])) {
				// Get Province from state code
				$country = new \Geography\Country();
				$country->get('USA');
				$province = new \Geography\Province();
				if (! $province->getProvince($country->id, $parameters['state'])) {
					$this->error("Invalid state code for USA");
				}
				$find_objects_query .= " AND rl.state = ?";
				$database->AddParam($parameters['state']);
			}
			elseif (!empty($parameters['country_code'])) {
				$country = new \Geography\Country();
				if (! $country->get($parameters['country_code'])) {
					$this->error("Invalid country code");
				}
				if (!empty($parameters['province_id'])) {
					$province = new \Geography\Province($parameters['province_id']);
					if (! $province->id) {
						$this->error("Province not found");
					}
					$find_objects_query .= " AND rl.province_id = ?";
					$database->AddParam($parameters['province_id']);
				}
				elseif (!empty($parameters['province_code'])) {
					print_r("Province code: " . $parameters['province_code']);
					$province = new \Geography\Province();
					if (! $province->getProvince($country->id, $parameters['province_code'])) {
						$this->error("Invalid province code for " . $country->name);
					}
					$find_objects_query .= " AND rl.province_code = ?";
					$database->AddParam($parameters['province_code']);
				}
			}
			elseif (!empty($parameters['province_id'])) {
				$find_objects_query .= " AND rl.province_id = ?";
				$database->AddParam($parameters['province_id']);
			}
			elseif (!empty($parameters['province_code'])) {
				$this->error("Province code provided without a country code");
			}

			// Execute Query
            $rs = $database->Execute($find_objects_query);
            if (! $rs) {
                $this->SQLError($database->ErrorMsg());
                return [];
            }

            $objects = array();
            while (list($id) = $rs->FetchRow()) {
                $objectOptions = array();
                if (isset($parameters['recursive'])) {
                    $objectOptions['recursive'] = $parameters['recursive'];
                }
                $object = new $this->_modelName($id, $objectOptions);
				if ($this->getControl('toArray') ?? false) {
					$object = $object->toArray();
					$object['arrayed'] = true;
				}
                array_push($objects,$object);
                $this->incrementCount();
            }
            return $objects;
		}
	}
