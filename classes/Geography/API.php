<?php
	namespace Geography;

	/* Base Class for APIs */
	class API extends \API {

		public function __construct() {
			$this->_name = 'geography';
			$this->_version = '0.3.3';
			$this->_release = '2026-03-19';
			$this->_schema = new Schema();
			parent::__construct();
		}

		###################################################
		### Add a Country								###
		###################################################
		public function addCountry() {
			$country = new \Geography\Country();
			if ($country->get($_REQUEST['name'])) $this->error("Country already exists");

			$parameters = array();
			if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			if (isset($_REQUEST['abbreviation'])) $parameters['abbreviation'] = $_REQUEST['abbreviation'];
			if (! $country->add($parameters)) $this->error("Error adding country: ".$country->error());
	
			$parameters = [];
			if (isset($_REQUEST['name'])) $parameters['name'] = trim((string) $_REQUEST['name']);
			if (isset($_REQUEST['abbreviation'])) $parameters['abbreviation'] = trim((string) $_REQUEST['abbreviation']);
			if (isset($_REQUEST['view_order'])) $parameters['view_order'] = (int) $_REQUEST['view_order'];
			$wasExisting = false;
			// Check if country already exists (by name or abbreviation) so we can return existing: true
			if (! empty($parameters['name'])) {
				if ($country->get($parameters['name'])) {
					$wasExisting = true;
				} elseif (! empty($parameters['abbreviation']) && $country->get($parameters['abbreviation'])) {
					$wasExisting = true;
				}
			}
			if (! $wasExisting && ! $country->add($parameters)) {
				$err = $country->error();
				$isDuplicate = $err && (stripos($err, 'duplicate') !== false || stripos($err, 'uk_name') !== false);
				if ($isDuplicate && ! empty($parameters['name'])) {
					$existing = new \Geography\Country();
					if ($existing->get($parameters['name']) || (! empty($parameters['abbreviation']) && $existing->get($parameters['abbreviation']))) {
						$country = $existing;
						$wasExisting = true;
					} else {
						$this->error("Error adding country: " . $err);
					}
				} else {
					$this->error("Error adding country: " . $err);
				}
			}
			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('country', $country);
			if ($wasExisting) $response->AddElement('existing', true);
			$response->print();
		}

		###################################################
		### Update a Country							###
		###################################################
		public function updateCountry() {
			$idOrNameOrAbbrev = isset($_REQUEST['id']) ? $_REQUEST['id'] : (isset($_REQUEST['name']) ? $_REQUEST['name'] : (isset($_REQUEST['abbreviation']) ? $_REQUEST['abbreviation'] : (isset($_REQUEST['code']) ? $_REQUEST['code'] : null)));
			if ($idOrNameOrAbbrev === null || $idOrNameOrAbbrev === '') $this->incompleteRequest("id, name, abbreviation, or code required");
			$country = new \Geography\Country();
			if (! $country->get($idOrNameOrAbbrev)) $this->notFound("Country not found");
			$parameters = [];
			if (isset($_REQUEST['name'])) $parameters['name'] = trim((string) $_REQUEST['name']);
			if (isset($_REQUEST['abbreviation'])) $parameters['abbreviation'] = trim((string) $_REQUEST['abbreviation']);
			if (isset($_REQUEST['view_order'])) $parameters['view_order'] = (int) $_REQUEST['view_order'];
			if (! empty($parameters) && ! $country->update($parameters)) $this->error("Error updating country: " . $country->error());

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('country', $country);
			$response->print();
		}

		###################################################
		### Get Specified Country						###
		###################################################
		public function getCountry() {
			if (!empty($_REQUEST['abbreviation'])) {
				$country = new \Geography\Country();
				$country->get(trim((string) $_REQUEST['abbreviation']));
				if ($country->error()) $this->error($country->error());
			}
			elseif (!empty($_REQUEST['code'])) {
				$country = new \Geography\Country();
				$country->get(trim((string) $_REQUEST['code']));
				if ($country->error()) $this->error($country->error());
			}
			elseif(isset($_REQUEST['id'])) {
				$country = new \Geography\Country($_REQUEST['id']);
			}
			else {
				$this->incompleteRequest("Not enough parameters");
			}

			if (! $country->exists()) $this->notFound();

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('country',$country);
			$response->print();
		}

		###################################################
		### Find matching Countrys						###
		###################################################
		public function findCountries() {
			$countryList = new \Geography\CountryList();

			$parameters = array();
			if (isset($_REQUEST['status']) && $_REQUEST['status']) $parameters['status'] = $_REQUEST['status'];
			
			$countries = $countryList->find($parameters);
			if ($countryList->error()) $this->error("Error finding countries: ".$countryList->error());
	
			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('country',$countries);
			$response->print();
		}

		###################################################
		### Add a Province or State						###
		###################################################
		public function addProvince() {
			if ($_REQUEST['country_id']) {
				$country = new \Geography\Country($_REQUEST['country_id']);
				if (! $country->id) $this->error("Country not found");				
			}
			elseif (isset($_REQUEST['country_name'])) {
				$country = new \Geography\Country();
				if (! $country->get($_REQUEST['country_name'])) $this->error("Country not found");
			}
			else {
				$this->incompleteRequest("Not enough parameters");
			}

			$province = new \Geography\Province();
			$parameters = ['country_id' => $country->id];
			if (isset($_REQUEST['name'])) $parameters['name'] = trim((string) $_REQUEST['name']);
			if (isset($_REQUEST['abbreviation'])) $parameters['abbreviation'] = trim((string) $_REQUEST['abbreviation']);
			if (isset($_REQUEST['code'])) $parameters['code'] = trim((string) $_REQUEST['code']);
			if (isset($_REQUEST['type'])) $parameters['type'] = trim((string) $_REQUEST['type']);
			if (isset($_REQUEST['label'])) $parameters['label'] = trim((string) $_REQUEST['label']);
			$wasExisting = false;
			// Check if province already exists so we can return existing: true
			if (! empty($parameters['code']) && $province->getByCode($parameters['code'])) {
				$wasExisting = true;
			} elseif (! empty($parameters['name']) && $province->getProvince($country->id, $parameters['name'])) {
				$wasExisting = true;
			} elseif (! empty($parameters['abbreviation']) && $province->getByAbbreviation($country->id, $parameters['abbreviation'])) {
				$wasExisting = true;
			}
			if (! $wasExisting && ! $province->add($parameters)) {
				$err = $province->error();
				$isDuplicate = $err && (stripos($err, 'duplicate') !== false || stripos($err, 'already exists') !== false || stripos($err, 'uk_') !== false);
				if ($isDuplicate) {
					$existing = new \Geography\Province();
					$found = false;
					if (! empty($parameters['code']) && $existing->getByCode($parameters['code'])) $found = true;
					elseif (! empty($parameters['name']) && $existing->getProvince($country->id, $parameters['name'])) $found = true;
					elseif (! empty($parameters['abbreviation']) && $existing->getByAbbreviation($country->id, $parameters['abbreviation'])) $found = true;
					if ($found) {
						$province = $existing;
						$wasExisting = true;
					} else {
						$this->error("Error adding province: " . $err);
					}
				} else {
					$this->error("Error adding province: " . $err);
				}
			}
			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('province', $province);
			if ($wasExisting) $response->AddElement('existing', true);
			$response->print();
		}

		###################################################
		### Update a Province							###
		###################################################
		public function updateProvince() {
			if (empty($_REQUEST['code']) && empty($_REQUEST['id'])) $this->incompleteRequest("code or id required");
			$province = new \Geography\Province();
			if (! empty($_REQUEST['id'])) {
				$province->id = (int) $_REQUEST['id'];
				if (! $province->details()) $this->notFound("Province not found");
			} else {
				if (! $province->getByCode(trim((string) $_REQUEST['code']))) $this->notFound("Province not found");
			}
			$parameters = [];
			if (isset($_REQUEST['name'])) $parameters['name'] = trim((string) $_REQUEST['name']);
			if (isset($_REQUEST['abbreviation'])) $parameters['abbreviation'] = trim((string) $_REQUEST['abbreviation']);
			if (isset($_REQUEST['code'])) $parameters['code'] = trim((string) $_REQUEST['code']);
			if (isset($_REQUEST['country_id'])) $parameters['country_id'] = (int) $_REQUEST['country_id'];
			if (isset($_REQUEST['type'])) $parameters['type'] = trim((string) $_REQUEST['type']);
			if (isset($_REQUEST['label'])) $parameters['label'] = trim((string) $_REQUEST['label']);
			if (! empty($parameters) && ! $province->update($parameters)) $this->error("Error updating province: " . $province->error());

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('province', $province);
			$response->print();
		}

		###################################################
		### Get Specified Province						###
		###################################################
		public function getProvince() {
			if (!empty($_REQUEST['id'])) {
				$province = new \Geography\Province($_REQUEST['id']);
				if (! $province->id) $this->error("Province not found");
			}
			elseif (!empty($_REQUEST['country_id'])) {
				$_REQUEST['country_id'] = (int) $_REQUEST['country_id'];
				$country = new \Geography\Country($_REQUEST['country_id']);
				if (! $country->id) $this->error("Country id ".$_REQUEST['country_id']." not found");
			}
			elseif (!empty($_REQUEST['country_name'])) {
				$country = new \Geography\Country();
				if (! $country->get($_REQUEST['country_name'])) $this->error("Country name '".$_REQUEST['country_name']."' not found");
			}
			elseif (!empty($_REQUEST['country_abbreviation'])) {
				$country = new \Geography\Country();
				if (! $country->getByAbbreviation($_REQUEST['country_abbreviation'])) $this->error("Country abbreviation '".$_REQUEST['country_abbreviation']."' not found");
			}
			else {
				$this->incompleteRequest("Not enough parameters");
			}

			if (!$province) {
				if (!empty($_REQUEST['name'])) {
					$province = new \Geography\Province();
					if (!$province->getProvince($country->id,$_REQUEST['name'])) $this->notFound("Province not found");
				}
				elseif (!empty($_REQUEST['abbreviation'])) {
					$province = new \Geography\Province();
					if (!$province->getByAbbreviation($country->id,$_REQUEST['abbreviation'])) $this->notFound("Province not found");
				}
				else {
					$this->incompleteRequest("province id, name, or abbreviation required");
				}
			}

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('province',$province);
			$response->print();
		}

		###################################################
		### Find matching Provinces						###
		###################################################
		public function findProvinces() {
			$provinceList = new \Geography\ProvinceList();
			
			$parameters = array();
			if (!empty($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
			if (!empty($_REQUEST['country_name'])) {
				$country = new \Geography\Country();
				if (! $country->get($_REQUEST['country_name'])) $this->error("Country name '".$_REQUEST['country_name']."' not found");
				$parameters['country_id'] = $country->id;
			}
			elseif (!empty($_REQUEST['country_abbreviation'])) {
				$country = new \Geography\Country();
				if (! $country->getByAbbreviation($_REQUEST['country_abbreviation'])) $this->error("Country abbreviation '".$_REQUEST['country_abbreviation']."' not found");
				$parameters['country_id'] = $country->id;
			}
			elseif (!empty($_REQUEST['country_id'])) {
				$country = new \Geography\Country($_REQUEST['country_id']);
				if (! $country->id) $this->error("Country id '".$_REQUEST['country_id']."' not found");
				$parameters['country_id'] = $country->id;
			}
			if (!empty($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			
			$provinces = $provinceList->find($parameters);
			if ($provinceList->error()) $this->error("Error finding provinces: ".$provinceList->error());
	
			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('province',$provinces);
			$response->print();
		}

		/** @apiMethod addCounty(parameters)
		 * Add a county with associated country and province/state.
		 * Required parameters: country_id or country_name or country_abbreviation, province_id or province_name or province_abbreviation, name
		 */
		public function addCounty() {
			$county = new \Geography\County();
			$parameters = [];

			// Locate Country
			if (!empty($_REQUEST['country_name'])) {
				$country = new \Geography\Country();
				if (! $country->get($_REQUEST['country_name'])) $this->error("Country name '".$_REQUEST['country_name']."' not found");
				$parameters['country_id'] = $country->id;
			}
			elseif (!empty($_REQUEST['country_abbreviation'])) {
				$country = new \Geography\Country();
				if (! $country->getByAbbreviation(trim((string) $_REQUEST['country_abbreviation']))) $this->error("Country abbreviation '".$_REQUEST['country_abbreviation']."' not found");
				$parameters['country_id'] = $country->id;
			}
			elseif (!empty($_REQUEST['country_id'])) {
				$country = new \Geography\Country($_REQUEST['country_id']);
				if (! $country->id) $this->error("Country id '".$_REQUEST['country_id']."' not found");
				$parameters['country_id'] = $country->id;
			}
			else {
				$this->incompleteRequest("country_name, country_abbreviation, or country_id required");
			}

			// Locate Province
			if (!empty($_REQUEST['province_name'])) {
				$province = new \Geography\Province();
				if (! $province->getProvince($country->id, trim((string) $_REQUEST['province_name']))) $this->error("Province name '".$_REQUEST['province_name']."' not found");
				$parameters['province_id'] = $province->id;
			}
			elseif (!empty($_REQUEST['province_abbreviation'])) {
				$province = new \Geography\Province();
				if (! $province->getByAbbreviation($country->id, trim((string) $_REQUEST['province_abbreviation']))) $this->error("Province abbreviation '".$_REQUEST['province_abbreviation']."' not found");
				$parameters['province_id'] = $province->id;
			}
			elseif (!empty($_REQUEST['province_id'])) {
				$province = new \Geography\Province($_REQUEST['province_id']);
				if (! $province->id) $this->error("Province id '".$_REQUEST['province_id']."' not found");
				$parameters['province_id'] = $province->id;
			}
			else {
				$this->incompleteRequest("province_name, province_abbreviation, or province_id required");
			}
			if (isset($_REQUEST['name'])) $parameters['name'] = trim((string) $_REQUEST['name']);
			$wasExisting = false;

			// Check if county already exists so we can return existing: true
			if (! empty($parameters['name']) && $county->getByName($parameters['province_id'], $parameters['name'])) {
				$wasExisting = true;
			}
			if (! $wasExisting && ! $county->add($parameters)) {
				$err = $county->error();
				$isDuplicate = $err && (stripos($err, 'duplicate') !== false || stripos($err, 'already exists') !== false || stripos($err, 'uk_') !== false);
				if ($isDuplicate) {
					$existing = new \Geography\County();
					if ($existing->getByName($parameters['province_id'], $parameters['name'])) {
						$county = $existing;
						$wasExisting = true;
					} else {
						$this->error("Error adding county: " . $err);
					}
				} else {
					$this->error("Error adding county: " . $err);
				}
			}

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('county', $county);
			if ($wasExisting) $response->AddElement('existing', true);
			$response->print();
		}

		/** @apiMethod updateCounty(parameters)
		 * Update a county's details.
		 * Required parameter: id or (province_id and name)
		 * Optional parameters: name, province_id, country_id
		 */
		public function updateCounty() {
			$county = new \Geography\County();
			if (isset($_REQUEST['id'])) {
				$county->id = (int) $_REQUEST['id'];
				if (! $county->details()) $this->notFound("County not found");
			} elseif (isset($_REQUEST['province_id']) && isset($_REQUEST['name'])) {
				if (! $county->getByName($_REQUEST['province_id'], $_REQUEST['name'])) $this->notFound("County not found");
			} else {
				$this->incompleteRequest("id or (province_id and name) required");
			}

			$parameters = [];
			if (isset($_REQUEST['name'])) $parameters['name'] = trim((string) $_REQUEST['name']);
			if (isset($_REQUEST['province_id'])) $parameters['province_id'] = (int) $_REQUEST['province_id'];
			if (isset($_REQUEST['country_id'])) $parameters['country_id'] = (int) $_REQUEST['country_id'];
			if (! empty($parameters) && ! $county->update($parameters)) $this->error("Error updating county: " . $county->error());

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('county', $county);
			$response->print();
		}

		/** @apiMethod getCounty
		 * Find counties matching specified parameters (country code, province code, county name)
		 * Optional parameters: country_id, country_name, or country_abbreviation (to validate that county belongs to specified country)
		 *		province_id, province_name, or province_abbreviation (to validate that county belongs to specified province)
		 */
		public function getCounty() {
			$parameters = array();
			if (!empty($_REQUEST['country_name'])) {
				$country = new \Geography\Country();
				if (! $country->get($_REQUEST['country_name'])) $this->error("Country name '".$_REQUEST['country_name']."' not found");
			}
			elseif (!empty($_REQUEST['country_abbreviation'])) {
				$country = new \Geography\Country();
				if (! $country->getByAbbreviation(trim((string) $_REQUEST['country_abbreviation']))) $this->error("Country abbreviation '".$_REQUEST['country_abbreviation']."' not found");
			}
			else {
				$this->incompleteRequest("country_name or country_abbreviation required");
			}
			if (! $country->id) $this->invalidRequest("Country not found");

			if (!empty($_REQUEST['province_name'])) {
				$province = new \Geography\Province();
				if (! $province->getProvince($country->id, trim((string) $_REQUEST['province_name']))) $this->error("Province name '".$_REQUEST['province_name']."' not found");
				$parameters['province_id'] = $province->id;
			}
			elseif (!empty($_REQUEST['province_abbreviation'])) {
				$province = new \Geography\Province();
				if (! $province->getByAbbreviation($country->id, trim((string) $_REQUEST['province_abbreviation']))) $this->error("Province abbreviation '".$_REQUEST['province_abbreviation']."' not found");
				$parameters['province_id'] = $province->id;
			}
			else {
				$this->incompleteRequest("province_name or province_abbreviation required");
			}
			if (! $province->id) $this->invalidRequest("Province not found");

			if (empty($_REQUEST['name'])) $this->incompleteRequest("County name required");
			$parameters['name'] = trim((string) $_REQUEST['name']);

			$county = new \Geography\County();
			if (! $county->get($parameters['province_id'], $parameters['name'])) $this->invalidRequest("County not found");

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('county',$county);
			$response->print();
		}

		/** @apiMethod findCounties()
		 * Find counties matching specified parameters (country code, province code, county name)
		 * Optional parameters: country_id, country_name, or country_abbreviation (to filter counties by specified country)
		 */
		public function findCounties() {
			$parameters = array();
			if (!empty($_REQUEST['country_name'])) {
				$country = new \Geography\Country();
				if (! $country->get($_REQUEST['country_name'])) $this->error("Country name '".$_REQUEST['country_name']."' not found");
				$parameters['country_id'] = $country->id;
			}
			elseif (!empty($_REQUEST['country_abbreviation'])) {
				$country = new \Geography\Country();
				if (! $country->getByAbbreviation(trim((string) $_REQUEST['country_abbreviation']))) $this->error("Country abbreviation '".$_REQUEST['country_abbreviation']."' not found");
				$parameters['country_id'] = $country->id;
			}
			elseif (!empty($_REQUEST['country_id'])) {
				$country = new \Geography\Country($_REQUEST['country_id']);
				if (! $country->id) $this->error("Country id '".$_REQUEST['country_id']."' not found");
				$parameters['country_id'] = $country->id;
			}

			if (!empty($_REQUEST['province_name'])) {
				$province = new \Geography\Province();
				if (! $province->getProvince($country->id, trim((string) $_REQUEST['province_name']))) $this->error("Province name '".$_REQUEST['province_name']."' not found");
				$parameters['province_id'] = $province->id;
			}
			elseif (!empty($_REQUEST['province_abbreviation'])) {
				$province = new \Geography\Province();
				if (! $province->getByAbbreviation($country->id, trim((string) $_REQUEST['province_abbreviation']))) $this->error("Province abbreviation '".$_REQUEST['province_abbreviation']."' not found");
				$parameters['province_id'] = $province->id;
			}
			elseif (!empty($_REQUEST['province_id'])) {
				$province = new \Geography\Province($_REQUEST['province_id']);
				if (! $province->id) $this->error("Province id '".$_REQUEST['province_id']."' not found");
				$parameters['province_id'] = $province->id;
			}
			if (!empty($_REQUEST['name'])) $parameters['name'] = trim((string) $_REQUEST['name']);

			$countyList = new \Geography\CountyList();
			$counties = $countyList->find($parameters);
			if ($countyList->error()) $this->error("Error finding counties: ".$countyList->error());

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('county',$counties);
			$response->print();
		}

		/** @apiMethod addCity()
		 * Add a city with associated province/state and country.
		 * Required parameters: country_id or country_name or country_abbreviation, province_id or province_name or province_abbreviation, name
		 */
		public function addCity() {
			$city = new \Geography\City();
			$parameters = [];

			// Locate Province By ID if Available
			if (!empty($_REQUEST['province_id'])) {
				$province = new \Geography\Province($_REQUEST['province_id']);
				if (! $province->id) $this->error("Province id '".$_REQUEST['province_id']."' not found");
				$parameters['province_id'] = $province->id;
				$parameters['country_id'] = $province->country_id;
			}

			// Locate Country
			if (!empty($_REQUEST['country_name'])) {
				$country = new \Geography\Country();
				if (! $country->get($_REQUEST['country_name'])) $this->error("Country name '".$_REQUEST['country_name']."' not found");
				$parameters['country_id'] = $country->id;
			}
			elseif (!empty($_REQUEST['country_abbreviation'])) {
				$country = new \Geography\Country();
				if (! $country->getByAbbreviation(trim((string) $_REQUEST['country_abbreviation']))) $this->error("Country abbreviation '".$_REQUEST['country_abbreviation']."' not found");
				$parameters['country_id'] = $country->id;
			}
			elseif (!empty($_REQUEST['country_id'])) {
				$country = new \Geography\Country($_REQUEST['country_id']);
				if (! $country->id) $this->error("Country id '".$_REQUEST['country_id']."' not found");
				$parameters['country_id'] = $country->id;
			}
			else {
				$this->incompleteRequest("country_name, country_abbreviation, or country_id required");
			}

			if (!empty($_REQUEST['province_name'])) {
				$province = new \Geography\Province();
				if (! $province->getProvince($country->id, trim((string) $_REQUEST['province_name']))) $this->error("Province name '".$_REQUEST['province_name']."' not found");
				$parameters['province_id'] = $province->id;
			}
			elseif (!empty($_REQUEST['province_abbreviation'])) {
				$province = new \Geography\Province();
				if (! $province->getByAbbreviation($country->id, trim((string) $_REQUEST['province_abbreviation']))) $this->error("Province abbreviation '".$_REQUEST['province_abbreviation']."' not found");
				$parameters['province_id'] = $province->id;
			}

			if (empty($province) || ! $province->id) $this->incompleteRequest("province_name or province_abbreviation required");

			if (isset($_REQUEST['name'])) $parameters['name'] = trim((string) $_REQUEST['name']);
			$wasExisting = false;

			// Check if city already exists so we can return existing: true
			if (! empty($parameters['name']) && $city->getByName($parameters['province_id'], $parameters['name'])) {
				$wasExisting = true;
			}
			if (! $wasExisting && ! $city->add($parameters)) {
				$this->error("Error adding city: " . $city->error());
			}

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('city', $city);
			if ($wasExisting) $response->AddElement('existing', true);
			$response->print();
		}

		/** @apiMethod getCity()
		 * Get city details by id or (province and name)
		 * Required parameters: country_abbreviation and province_abbreviation and name
		 * Optional parameters: country_id, country_abbreviation (to validate that city belongs to specified country)
		 */
		public function getCity() {
			$parameters = array();
			if (!empty($_REQUEST['country_name'])) {
				$country = new \Geography\Country();
				if (! $country->get($_REQUEST['country_name'])) $this->error("Country name '".$_REQUEST['country_name']."' not found");
			}
			elseif (!empty($_REQUEST['country_abbreviation'])) {
				$country = new \Geography\Country();
				if (! $country->getByAbbreviation(trim((string) $_REQUEST['country_abbreviation']))) $this->error("Country abbreviation '".$_REQUEST['country_abbreviation']."' not found");
			}
			else {
				$this->incompleteRequest("country_name or country_abbreviation required");
			}
			if (! $country->id) $this->invalidRequest("Country not found");

			if (!empty($_REQUEST['province_name'])) {
				$province = new \Geography\Province();
				if (! $province->getProvince($country->id, trim((string) $_REQUEST['province_name']))) $this->error("Province name '".$_REQUEST['province_name']."' not found");
				$parameters['province_id'] = $province->id;
			}
			elseif (!empty($_REQUEST['province_abbreviation'])) {
				$province = new \Geography\Province();
				if (! $province->getByAbbreviation($country->id, trim((string) $_REQUEST['province_abbreviation']))) $this->error("Province abbreviation '".$_REQUEST['province_abbreviation']."' not found");
				$parameters['province_id'] = $province->id;
			}	
			else {
				$this->incompleteRequest("province_name or province_abbreviation required");
			}
			if (! $province->id) $this->invalidRequest("Province not found");

			if (empty($_REQUEST['name'])) $this->incompleteRequest("City name required: 'name': ".$_REQUEST['name']." province: ".$province->name." country: ".$country->name);
			$parameters['name'] = trim((string) $_REQUEST['name']);
			$city = new \Geography\City();
			if (! $city->get($parameters['province_id'], $parameters['name'])) $this->invalidRequest("City '".$parameters['name']."' not found in province '".$province->name."'");
			$parameters['city_id'] = $city->id;

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('city',$city);
			$response->print();
		}

		/** @apiMethod findCities
		 * Find cities matching specified criteria
		 */
		public function findCities() {
			$parameters = array();
			if (!empty($_REQUEST['country_name'])) {
				$country = new \Geography\Country();
				if (! $country->get($_REQUEST['country_name'])) $this->error("Country name '".$_REQUEST['country_name']."' not found");
				$parameters['country_id'] = $country->id;
			}
			elseif (!empty($_REQUEST['country_abbreviation'])) {
				$country = new \Geography\Country();
				if (! $country->getByAbbreviation(trim((string) $_REQUEST['country_abbreviation']))) $this->error("Country abbreviation '".$_REQUEST['country_abbreviation']."' not found");
				$parameters['country_id'] = $country->id;
			}
			elseif (!empty($_REQUEST['country_id'])) {
				$country = new \Geography\Country($_REQUEST['country_id']);
				if (! $country->id) $this->error("Country id '".$_REQUEST['country_id']."' not found");
				$parameters['country_id'] = $country->id;
			}

			if (!empty($_REQUEST['province_name'])) {
				$province = new \Geography\Province();
				if (! $province->getProvince($country->id, trim((string) $_REQUEST['province_name']))) $this->error("Province name '".$_REQUEST['province_name']."' not found");
				$parameters['province_id'] = $province->id;
			}
			elseif (!empty($_REQUEST['province_abbreviation'])) {
				$province = new \Geography\Province();
				if (! $province->getByAbbreviation($country->id, trim((string) $_REQUEST['province_abbreviation']))) $this->error("Province abbreviation '".$_REQUEST['province_abbreviation']."' not found");
				$parameters['province_id'] = $province->id;
			}

			if (!empty($_REQUEST['name'])) $parameters['name'] = trim((string) $_REQUEST['name']);

			$cityList = new \Geography\CityList();
			$cities = $cityList->find($parameters);
			if ($cityList->error()) $this->error("Error finding cities: ".$cityList->error());

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('city',$cities);
			$response->print();
		}

		/** @apiMethod getStateByZipCode
		 * Get administrative details (country, province/state, county, city) for a given zip code
		 */
		public function getStateByZipCode() {
			if (empty($_REQUEST['zip_code'])) $this->incompleteRequest("zip_code required");
			$state = new \Geography\State();
			if (! $state->getByZipCode(trim((string) $_REQUEST['zip_code']), 'US')) $this->notFound("Zip code not found");

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('state', $state);
			$response->print();
		}

		/** @apiMethod addZipCode()
		 * Add a zip code with associated country, province/state, county, and city.
		 */
		public function addZipCode() {
			$zipCode = new \Geography\ZipCode();
			$parameters = [];
			if (isset($_REQUEST['code'])) $parameters['code'] = trim((string) $_REQUEST['code']);

			// Locate Country
			if (!empty($_REQUEST['country_name'])) {
				$country = new \Geography\Country();
				if (! $country->get($_REQUEST['country_name'])) $this->error("Country name '".$_REQUEST['country_name']."' not found");
				$parameters['country_id'] = $country->id;
			}
			elseif (!empty($_REQUEST['country_abbreviation'])) {
				$country = new \Geography\Country();
				if (! $country->getByAbbreviation($_REQUEST['country_abbreviation'])) $this->error("Country abbreviation '".$_REQUEST['country_abbreviation']."' not found");
				$parameters['country_id'] = $country->id;
			}
			else {
				$this->incompleteRequest("country_name or country_abbreviation required");
			}

			// Locate Province
			$province = new \Geography\Province();
			if (!empty($_REQUEST['province_name'])) {
				if (! $province->getProvince($country->id, trim((string) $_REQUEST['province_name']))) $this->error("Province name '".$_REQUEST['province_name']."' not found");
				$parameters['province_id'] = $province->id;
			}
			elseif (!empty($_REQUEST['province_abbreviation'])) {
				if (! $province->getByAbbreviation($country->id, trim((string) $_REQUEST['province_abbreviation']))) $this->error("Province abbreviation '".$_REQUEST['province_abbreviation']."' not found");
				$parameters['province_id'] = $province->id;
			}

			if (!empty($_REQUEST['county_name'])) {
				$county = new \Geography\County();
				if (! $county->getByName(trim((string) $_REQUEST['county_name']))) $this->error("County name '".$_REQUEST['county_name']."' not found");
				$parameters['county_id'] = $county->id;
			}
			if (!empty($_REQUEST['city_name'])) {
				$city = new \Geography\City();
				if (! $city->getByName(trim((string) $_REQUEST['city_name']))) $this->error("City name '".$_REQUEST['city_name']."' not found");
				$parameters['city_id'] = $city->id;
			}
			if (isset($_REQUEST['latitude'])) $parameters['latitude'] = (float) $_REQUEST['latitude'];
			if (isset($_REQUEST['longitude'])) $parameters['longitude'] = (float) $_REQUEST['longitude'];
			if (! $zipCode->add($parameters)) {
				$this->error("Error adding zip code: " . $zipCode->error());
				return;
			}

			$zipCodeObj = $zipCode->_clone();
			$zipCodeObj->country_abbreviation = $zipCode->country()->abbreviation;
			$zipCodeObj->country_name = $zipCode->country()->name;
			$zipCodeObj->province_abbreviation = $zipCode->province()->abbreviation;
			$zipCodeObj->province_name = $zipCode->province()->name;

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('zip_code', $zipCodeObj);
			$response->print();
		}

		/** @apiMethod updateZipCode()
		 * Update a zip code's details.
		 * Required parameter: code or id
		 * Optional parameters: province_id, county_id, city_id, latitude, longitude
		 * Note: code cannot be updated (as it's the unique identifier) but can be used to identify which zip code to update.
		 */
		public function updateZipCode() {
			$zipCode = new \Geography\ZipCode();
			if (isset($_REQUEST['id'])) {
				$zipCode->id = (int) $_REQUEST['id'];
				if (! $zipCode->details()) $this->notFound("Zip code not found");
			} elseif (isset($_REQUEST['code'])) {
				if (! $zipCode->getByCode(trim((string) $_REQUEST['code']))) $this->notFound("Zip code not found");
			} else {
				$this->incompleteRequest("id or code required");
			}

			$parameters = [];
			if (isset($_REQUEST['province_id'])) {
				$province = new \Geography\Province($_REQUEST['province_id']);
				if (! $province->id) $this->invalidRequest("Province not found");
				$parameters['province_id'] = $province->id;
			}
			if (isset($_REQUEST['county_id'])) {
				$county = new \Geography\County($_REQUEST['county_id']);
				if (! $county->id) $this->invalidRequest("County not found");
				$parameters['county_id'] = $county->id;
			}
			if (isset($_REQUEST['city_id'])) {
				$city = new \Geography\City($_REQUEST['city_id']);
				if (! $city->id) $this->invalidRequest("City not found");
				$parameters['city_id'] = $city->id;
			}
			if (isset($_REQUEST['latitude'])) $parameters['latitude'] = (float) $_REQUEST['latitude'];
			if (isset($_REQUEST['longitude'])) $parameters['longitude'] = (float) $_REQUEST['longitude'];
			if (! empty($parameters) && ! $zipCode->update($parameters)) {
				$this->error("Error updating zip code: " . $zipCode->error());
				return;
			}

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('zip_code', $zipCode);
			$response->print();
		}

		/** @method public getZipCode()
		 * Get details regarding specified zip code (identify by code or id)
		 * Required parameter: code or id
		 * Optional parameters: country_id, country_abbreviation, country_name (to validate that zip code belongs to specified country)
		 */
		public function getZipCode() {
			if (!empty($_REQUEST['id'])) {
				$zipCode = new \Geography\ZipCode($_REQUEST['id']);
				if (! $zipCode->id) $this->notFound("Zip code not found");
			}
			elseif (!empty($_REQUEST['code'])) {
				$zipCode = new \Geography\ZipCode();
				if (!empty($_REQUEST['country_id'])) {
					$country = new \Geography\Country($_REQUEST['country_id']);
					if (! $country->id) $this->invalidRequest("Country not found");
				} elseif (isset($_REQUEST['country_abbreviation'])) {
					$country = new \Geography\Country();
					if (! $country->getByAbbreviation(trim((string) $_REQUEST['country_abbreviation']))) $this->invalidRequest("Country not found");
				} elseif (isset($_REQUEST['country_name'])) {
					$country = new \Geography\Country();
					if (! $country->get(trim((string) $_REQUEST['country_name']))) $this->invalidRequest("Country not found");
				}
				else {
					$this->incompleteRequest("country_id, country_abbreviation, or country_name required");
				}

				if (!empty($_REQUEST['province_abbreviation'])) {
					$province = new \Geography\Province();
					if (! $province->getByAbbreviation($country->id, trim((string) $_REQUEST['province_abbreviation']))) $this->invalidRequest("Province not found");
				}

				if (empty($province) || ! $province->id) {
					$this->incompleteRequest("Province not found for country '".$country->name."'. province '".$_REQUEST['province_abbreviation']."'.");
				}

				if (! $zipCode->get($province->id, trim((string) $_REQUEST['code']))) $this->notFound("Zip code not found");
			}
			else {
				$this->incompleteRequest("id or code required");
			}

			$zipCodeObj = $zipCode->_clone();
			$zipCodeObj->country_abbreviation = $zipCode->country()->abbreviation;
			$zipCodeObj->country_name = $zipCode->country()->name;
			$zipCodeObj->province_abbreviation = $zipCode->province()->abbreviation;
			$zipCodeObj->province_name = $zipCode->province()->name;

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('zip_code',$zipCodeObj);
			$response->print();
		}

		/** @method public loadZipCode(id)
		 * Load zip code details by id and return zip code object
		 */
		public function loadZipCode() {
			if (empty($_REQUEST['id'])) $this->incompleteRequest("id required");
			$zipCode = new \Geography\ZipCode($_REQUEST['id']);
			if (! $zipCode->id) $this->notFound("Zip code not found");

			$zipCodeObj = $zipCode->_clone();
			$zipCodeObj->country_abbreviation = $zipCode->country()->abbreviation;
			$zipCodeObj->country_name = $zipCode->country()->name;
			$zipCodeObj->province_abbreviation = $zipCode->province()->abbreviation;
			$zipCodeObj->province_name = $zipCode->province()->name;

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('zip_code',$zipCodeObj);
			$response->print();
		}

		/** @method public findZipCodes()
		 * Find zip codes matching specified criteria
		 * Optional parameters: code, country_id, country_abbreviation, country_name, province_id, county_id, city_id
		 */
		public function findZipCodes() {
			$zipCodeList = new \Geography\ZipCodeList();

			$parameters = array();
			if (!empty($_REQUEST['code']) && $_REQUEST['code']) $parameters['code'] = $_REQUEST['code'];

			if (!empty($_REQUEST['country_abbreviation'])) {
				$country = new \Geography\Country();
				if (! $country->getByAbbreviation(trim((string) $_REQUEST['country_abbreviation']))) $this->invalidRequest("Country not found");
				$parameters['country_id'] = $country->id;
			}
			elseif (!empty($_REQUEST['country_name'])) {
				$country = new \Geography\Country();
				if (! $country->get(trim((string) $_REQUEST['country_name']))) $this->invalidRequest("Country not found");
				$parameters['country_id'] = $country->id;
			}

			if (!empty($_REQUEST['province_name'])) {
				$province = new \Geography\Province();
				if (! $province->getByName(trim((string) $_REQUEST['province_name']))) $this->invalidRequest("Province not found");
				$parameters['province_id'] = $province->id;
			}
			if (!empty($_REQUEST['county_name'])) {
				$county = new \Geography\County();
				if (! $county->getByName(trim((string) $_REQUEST['county_name']))) $this->invalidRequest("County not found");
				$parameters['county_id'] = $county->id;
			}
			if (!empty($_REQUEST['city_name'])) {
				$city = new \Geography\City();
				if (! $city->getByName(trim((string) $_REQUEST['city_name']))) $this->invalidRequest("City not found");
				$parameters['city_id'] = $city->id;
			}

			$zipCodes = $zipCodeList->find($parameters);
			if ($zipCodeList->error()) $this->error("Error finding zip codes: ".$zipCodeList->error());

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('zip_code',$zipCodes);
			$response->print();
		}

		/** @method public setZipCodeWeather()
		 * Add a weather record for a specific zip code
		 * @param zip_code (required), timestamp or date_time (required), temperature (optional), conditions (optional)
		 */
		public function setZipCodeWeather() {
			$parameters = [];

			// Get The Country for Zip Code (required to ensure zip code is valid and to associate weather record with country)
			if (!empty($_REQUEST['country_code'])) {
				$country = new \Geography\Country();
				if (! $country->get($_REQUEST['country_code'])) $this->error("Country not found");
			}
			elseif (!empty($_REQUEST['country_abbreviation'])) {
				$country = new \Geography\Country();
				if (! $country->get($_REQUEST['country_abbreviation'])) $this->error("Country not found");
			}
			else {
				$this->incompleteRequest("country_code required");
			}

			// Get The Province (required to ensure zip code is valid and to associate weather record with province)
			if (!empty($_REQUEST['province_code'])) {
				$province = new \Geography\Province();
				if (! $province->get($country->id, trim((string) $_REQUEST['province_code']))) $this->error("Province not found");
			}
			elseif (!empty($_REQUEST['province_abbreviation'])) {
				$province = new \Geography\Province();
				if (! $province->get($country->id, trim((string) $_REQUEST['province_abbreviation']))) $this->error("Province not found");
			}
			else {
				$this->incompleteRequest("province_code required");
			}

			// Get the Zip Code
			if (! isset($_REQUEST['zip_code'])) $this->incompleteRequest("zip_code required");
			$zipCodeList = new \Geography\ZipCodeList();
			$zipCodes = $zipCodeList->find([
				'code' => trim((string) $_REQUEST['zip_code']),
				'country_id' => $country->id
			]);
			if ($zipCodeList->error()) {
				$this->error("Error finding zip code: " . $zipCodeList->error());
				return;
			}
			if (count($zipCodes) === 0) {
				$this->notFound("Zip code not found");
				return;
			}
			elseif (count($zipCodes) > 1) {
				$this->error("Multiple zip codes found matching code and country");
				return;
			}
			list($zipCode) = $zipCodes;
			$parameters['zip_code_id'] = $zipCode->id;

			// Default to Current Time if timestamp or date_time not provided
			if (! isset($_REQUEST['timestamp']) && ! isset($_REQUEST['date_time'])) $_REQUEST['timestamp'] = time(); // Default to current time if not provided

			// Calculate timestamp from either timestamp or date_time parameter
			if (isset($_REQUEST['timestamp'])) $parameters['timestamp'] = (int) $_REQUEST['timestamp'];
			elseif (isset($_REQUEST['date_time'])) {
				$timestamp = strtotime($_REQUEST['date_time']);
				if ($timestamp === false) $this->invalidRequest("Invalid date_time format");
				$parameters['timestamp'] = $timestamp;
			}

			// Optional weather parameters
			if (isset($_REQUEST['conditions'])) $parameters['conditions'] = trim((string) $_REQUEST['conditions']);
			if (isset($_REQUEST['temperature_celsius'])) $parameters['temperature_celsius'] = (float) $_REQUEST['temperature_celsius'];
			if (isset($_REQUEST['temperature_fahrenheit'])) $parameters['temperature_fahrenheit'] = (float) $_REQUEST['temperature_fahrenheit'];
			if (isset($_REQUEST['humidity'])) $parameters['humidity'] = (float) $_REQUEST['humidity'];
			if (isset($_REQUEST['pressure'])) $parameters['pressure'] = (float) $_REQUEST['pressure'];
			if (isset($_REQUEST['wind_speed_kph'])) $parameters['wind_speed_kph'] = (float) $_REQUEST['wind_speed_kph'];
			if (isset($_REQUEST['wind_speed_mph'])) $parameters['wind_speed_mph'] = (float) $_REQUEST['wind_speed_mph'];
			if (isset($_REQUEST['wind_speed_mps'])) $parameters['wind_speed_mps'] = (float) $_REQUEST['wind_speed_mps'];
			if (isset($_REQUEST['wind_gust_kph'])) $parameters['wind_gust_kph'] = (float) $_REQUEST['wind_gust_kph'];
			if (isset($_REQUEST['wind_gust_mph'])) $parameters['wind_gust_mph'] = (float) $_REQUEST['wind_gust_mph'];
			if (isset($_REQUEST['wind_gust_mps'])) $parameters['wind_gust_mps'] = (float) $_REQUEST['wind_gust_mps'];
			if (isset($_REQUEST['wind_direction'])) $parameters['wind_direction'] = (float) $_REQUEST['wind_direction'];
			if (isset($_REQUEST['precipitation_mm'])) $parameters['precipitation_mm'] = (float) $_REQUEST['precipitation_mm'];
			if (isset($_REQUEST['precipitation_in'])) $parameters['precipitation_in'] = (float) $_REQUEST['precipitation_in'];
			if (isset($_REQUEST['visibility_km'])) $parameters['visibility_km'] = (float) $_REQUEST['visibility_km'];
			if (isset($_REQUEST['visibility_miles'])) $parameters['visibility_miles'] = (float) $_REQUEST['visibility_miles'];

			// Add the Weather Record
			$weatherRecord = new \Geography\WeatherRecord();
			if (! $weatherRecord->add($parameters)) {
				$this->error("Error adding weather record: " . $weatherRecord->error());
				return;
			}

			// Assemble and Return Response
			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('weather_record', $weatherRecord);
			$response->print();
		}

		/** @method public getZipCodeWeather()
		 * Get the latest weather record before a specific timestamp for a specific zip code
		 */
		public function getZipCodeWeather() {
			$parameters = array();
			// Get The Country for Zip Code (required to ensure zip code is valid and to associate weather record with country)
			if (!empty($_REQUEST['country_code'])) {
				$country = new \Geography\Country();
				if (! $country->get($_REQUEST['country_code'])) $this->error("Country not found");
				$parameters['country_id'] = $country->id;
			}
			elseif (!empty($_REQUEST['country_abbreviation'])) {
				$country = new \Geography\Country();
				if (! $country->getByAbbreviation(trim((string) $_REQUEST['country_abbreviation']))) $this->error("Country not found");
				$parameters['country_id'] = $country->id;
			}
			else {
				$this->incompleteRequest("country_abbreviation required");
			}

			// Get the Province (required to ensure zip code is valid and to associate weather record with province)
			if (!empty($_REQUEST['province_code'])) {
				$province = new \Geography\Province();
				if (! $province->get($country->id, trim((string) $_REQUEST['province_code']))) $this->error("Province not found");
				$parameters['province_id'] = $province->id;
			}
			elseif (!empty($_REQUEST['province_abbreviation'])) {
				$province = new \Geography\Province();
				if (! $province->getByAbbreviation($country->id, trim((string) $_REQUEST['province_abbreviation']))) $this->error("Province not found");
				$parameters['province_id'] = $province->id;
			}
			else {
				$this->incompleteRequest("province_abbreviation required");
			}

			// Get the Zip Code
			if (! isset($_REQUEST['zip_code'])) $this->incompleteRequest("zip_code required");
			$zipCode = new \Geography\ZipCode();
			if (! $zipCode->get($province->id, trim((string) $_REQUEST['zip_code']))) $this->notFound("Zip code not found");

			if (!empty($_REQUEST['timestamp'])) $parameters['timestamp'] = (int) $_REQUEST['timestamp'];
			elseif (!empty($_REQUEST['date_time'])) {
				$timestamp = strtotime($_REQUEST['date_time']);
				if ($timestamp === false) $this->invalidRequest("Invalid date_time format");
				$parameters['timestamp_after'] = $timestamp;
			}

			$weatherRecordList = new \Geography\WeatherRecordList();
			list($weatherRecord) = $weatherRecordList->find($parameters, ['limit' => 1, 'sort' => 'timestamp', 'order' => 'DESC']);
			if ($weatherRecordList->error()) {
				$this->error("Error getting weather record: " . $weatherRecordList->error());
				return;
			}

			if (! $weatherRecord || ! $weatherRecord->id) {
				$this->notFound("Weather record not found for specified criteria");
				return;
			}
			$weatherRecordObj = $weatherRecord->_clone();
			$weatherRecordObj->zip_code = $zipCode->code;
			$weatherRecordObj->province_abbreviation = $province->abbreviation;
			$weatherRecordObj->country_abbreviation = $country->abbreviation;
			$weatherRecordObj->timestamp = strtotime($weatherRecord->date_record);
			$weatherRecordObj->date_record_local = $weatherRecord->dateLocal();
			$weatherRecordObj->date_record_site = $weatherRecord->dateSite();

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('parameters', $parameters);
			$response->AddElement('weather_record', $weatherRecordObj);
			$response->print();
		}

		/** @method public findWeatherRecords()
		 * Find weather records matching specified criteria (e.g. zip code, date range)
		 */
		public function findWeatherRecords() {
			$parameters = array();
			// Get The Country for Zip Code (required to ensure zip code is valid and to associate weather record with country)
			if (!empty($_REQUEST['country_abbreviation'])) {
				$country = new \Geography\Country();
				if (! $country->getByAbbreviation(trim((string) $_REQUEST['country_abbreviation']))) $this->error("Country not found");
				$parameters['country_id'] = $country->id;
			}
			elseif (!empty($_REQUEST['country_name'])) {
				$country = new \Geography\Country();
				if (! $country->get(trim((string) $_REQUEST['country_name']))) $this->error("Country not found");
				$parameters['country_id'] = $country->id;
			}

			// Get the Province (required to ensure zip code is valid and to associate weather record with province)
			if (!empty($_REQUEST['province_abbreviation'])) {
				$province = new \Geography\Province();
				if (! $province->getByAbbreviation($country->id, trim((string) $_REQUEST['province_abbreviation']))) $this->error("Province not found");
				$parameters['province_id'] = $province->id;
			}
			elseif (!empty($_REQUEST['province_name'])) {
				$province = new \Geography\Province();
				if (! $province->getByName($country->id, trim((string) $_REQUEST['province_name']))) $this->error("Province not found");
				$parameters['province_id'] = $province->id;
			}

			// Get the Zip Code
			if (!empty($_REQUEST['zip_code'])) {
				$zipCode = new \Geography\ZipCode();
				if (! $zipCode->get($province->id, trim((string) $_REQUEST['zip_code']))) $this->notFound("Zip code not found");
				$parameters['zip_code_id'] = $zipCode->id;
			}


			$weatherRecordList = new \Geography\WeatherRecordList();

			if (!empty($_REQUEST['start_timestamp']) || !empty($_REQUEST['start_date_time'])) {
				if (!empty($_REQUEST['start_timestamp'])) {
					$parameters['start_timestamp'] = (int) $_REQUEST['start_timestamp'];
				} else {
					$timestamp = strtotime($_REQUEST['start_date_time']);
					if ($timestamp === false) $this->invalidRequest("Invalid start_date_time format");
					$parameters['start_timestamp'] = $timestamp;
				}
			}
			if (!empty($_REQUEST['end_timestamp']) || !empty($_REQUEST['end_date_time'])) {
				if (!empty($_REQUEST['end_timestamp'])) {
					$parameters['end_timestamp'] = (int) $_REQUEST['end_timestamp'];
				} else {
					$timestamp = strtotime($_REQUEST['end_date_time']);
					if ($timestamp === false) $this->invalidRequest("Invalid end_date_time format");
					$parameters['end_timestamp'] = $timestamp;
				}
			}

			$weatherRecords = $weatherRecordList->find($parameters);
			if ($weatherRecordList->error()) $this->error("Error finding weather records: " . $weatherRecordList->error());

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('weather_record', $weatherRecords);
			$response->print();
		}

		/** @method public addZipCodeTimes()
		 * Add a times record for a specific zip code
		 */
		public function setZipCodeTimes() {
			// Implementation for adding a times record
			$parameters = array();
			if (empty($_REQUEST['zip_code'])) $this->incompleteRequest("zip_code required");
			$parameters['zip_code'] = trim((string) $_REQUEST['zip_code']);

			if (!empty($_REQUEST['country_abbreviation'])) {
				$country = new \Geography\Country();
				if (! $country->getByAbbreviation($_REQUEST['country_abbreviation'])) $this->error("Country not found");
				$parameters['country_id'] = $country->id;
			}
			elseif (!empty($_REQUEST['country_code'])) {
				$country = new \Geography\Country();
				if (! $country->getByAbbreviation(trim((string) $_REQUEST['country_code']))) $this->error("Country not found");
				$parameters['country_id'] = $country->id;
			}
			else {
				$this->incompleteRequest("country_code required");
			}

			if (!empty($_REQUEST['province_abbreviation'])) {
				$province = new \Geography\Province();
				if (! $province->getByAbbreviation($country->id, trim((string) $_REQUEST['province_abbreviation']))) $this->error("Province not found");
				$parameters['province_id'] = $province->id;
			}
			elseif (!empty($_REQUEST['province_code'])) {
				$province = new \Geography\Province();
				if (! $province->getByAbbreviation($country->id, trim((string) $_REQUEST['province_code']))) $this->error("Province not found");
				$parameters['province_id'] = $province->id;
			}
			else {
				$this->incompleteRequest("province_code required");
			}

			if (!empty($_REQUEST['zip_code'])) {
				$zipCode = new \Geography\ZipCode();
				if (! $zipCode->get($province->id, trim((string) $_REQUEST['zip_code']))) $this->notFound("Zip code not found");
				$parameters['zip_code_id'] = $zipCode->id;
			}
			else {
				$this->incompleteRequest("zip_code required");
			}

			if (isset($_REQUEST['sunrise'])) {
				$sunriseTime = strtotime($_REQUEST['sunrise']);
				if ($sunriseTime === false) $this->invalidRequest("Invalid sunrise format");
				$parameters['sunrise'] = date('Y-m-d H:i:s', $sunriseTime);
			}
			if (isset($_REQUEST['sunset'])) {
				$sunsetTime = strtotime($_REQUEST['sunset']);
				if ($sunsetTime === false) $this->invalidRequest("Invalid sunset format");
				$parameters['sunset'] = date('Y-m-d H:i:s', $sunsetTime);
			}
			if (isset($_REQUEST['moonrise'])) {
				$moonriseTime = strtotime($_REQUEST['moonrise']);
				if ($moonriseTime === false) $this->invalidRequest("Invalid moonrise format");
				$parameters['moonrise'] = date('Y-m-d H:i:s', $moonriseTime);
			}
			if (isset($_REQUEST['moonset'])) {
				$moonsetTime = strtotime($_REQUEST['moonset']);
				if ($moonsetTime === false) $this->invalidRequest("Invalid moonset format");
				$parameters['moonset'] = date('Y-m-d H:i:s', $moonsetTime);
			}
			if (isset($_REQUEST['moon_phase'])) $parameters['moon_phase'] = trim((string) $_REQUEST['moon_phase']);
			if (isset($_REQUEST['timezone'])) $parameters['timezone'] = trim((string) $_REQUEST['timezone']);

			// Add the Times Record
			$timesRecord = new \Geography\TimesRecord();
			if (! $timesRecord->add($parameters)) {
				$this->error("Error adding times record: " . $timesRecord->error());
				return;
			}
			// Assemble and Return Response
			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('zip_code_times', $timesRecord);
			$response->print();
		}

		/** @method public getZipCodeTimes()
		 * Get a times record for a specific zip code
		 */
		public function getZipCodeTimes() {
			if (!empty($_REQUEST['country_code'])) {
				$country = new \Geography\Country();
				if (! $country->getByAbbreviation(trim((string) $_REQUEST['country_code']))) $this->error("Country not found");
			}
			elseif (!empty($_REQUEST['country_abbreviation'])) {
				$country = new \Geography\Country();
				if (! $country->getByAbbreviation(trim((string) $_REQUEST['country_abbreviation']))) $this->error("Country not found");
			}
			else {
				$this->incompleteRequest("country_code required");
			}

			if (!empty($_REQUEST['province_code'])) {
				$province = new \Geography\Province();
				if (! $province->getByAbbreviation($country->id, trim((string) $_REQUEST['province_code']))) $this->error("Province not found");
			}
			elseif (!empty($_REQUEST['province_abbreviation'])) {
				$province = new \Geography\Province();
				if (! $province->getByAbbreviation($country->id, trim((string) $_REQUEST['province_abbreviation']))) $this->error("Province not found");
			}
			else {
				$this->incompleteRequest("province_code required");
			}

			if (!empty($_REQUEST['zip_code'])) {
				$zipCode = new \Geography\ZipCode();
				if (! $zipCode->get($province->id, trim((string) $_REQUEST['zip_code']))) $this->notFound("Zip code not found");
			}
			else {
				$this->incompleteRequest("zip_code required");
			}

			$timesRecordList = new \Geography\TimesRecordList();
			$parameters = [
				'zip_code_id' => $zipCode->id
			];
			if (!empty($_REQUEST['start_date_time']) || !empty($_REQUEST['start_timestamp'])) {
				if (!empty($_REQUEST['start_date_time'])) {
					$timestamp = strtotime($_REQUEST['start_date_time']);
					if ($timestamp === false) $this->invalidRequest("Invalid start_date_time format");
					$parameters['start_timestamp'] = $timestamp;
				} else {
					$parameters['start_timestamp'] = (int) $_REQUEST['start_timestamp'];
				}
			}

			if (!empty($_REQUEST['end_date_time']) || !empty($_REQUEST['end_timestamp'])) {
				if (!empty($_REQUEST['end_date_time'])) {
					$timestamp = strtotime($_REQUEST['end_date_time']);
					if ($timestamp === false) $this->invalidRequest("Invalid end_date_time format");
					$parameters['end_timestamp'] = $timestamp;
				} else {
					$parameters['end_timestamp'] = (int) $_REQUEST['end_timestamp'];
				}
			}

			if (!empty($_REQUEST['date'])) {
				$timestamp = strtotime($_REQUEST['date']);
				if ($timestamp === false) $this->invalidRequest("Invalid date format");
				$parameters['date'] = date('Y-m-d', $timestamp);
			}

			$timesRecords = $timesRecordList->find($parameters);
			if ($timesRecordList->error()) $this->error("Error finding times records: " . $timesRecordList->error());

			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('zip_code_times', $timesRecords);
			$response->print();
		}

		/** @method public _methods()
		 * Define available API methods
		 */
		public function _methods() {
			return array(
				'addCountry'	=> array(
					'description'	=> 'Add a country',
					'token_required'	=> true,
					'privilege_required'	=> 'manage geographical data',
					'return_element'	=> 'country',
					'return_type'	=> 'Geography::Country',
					'parameters'	=> array(
						'name'			=> array(
							'description'	=> 'Name of country',
							'required' => true
						),
						'abbreviation'	=> array(
							'description'	=> 'Abbreviation of country (optional)',
						),
						'view_order'	=> array(
							'description'	=> 'Display order (default 500)',
						),
					),
				),
				'updateCountry'	=> array(
					'description'	=> 'Update a country (identify by id, name, abbreviation, or code)',
					'token_required'	=> true,
					'privilege_required'	=> 'manage geographical data',
					'return_element'	=> 'country',
					'return_type'	=> 'Geography::Country',
					'parameters'	=> array(
						'id'			=> array(
							'description'	=> 'Country ID',
						),
						'name'			=> array(
							'description'	=> 'Country name (to find) or new name (to set)',
						),
						'abbreviation'	=> array(
							'description'	=> 'Abbreviation (to find or set)',
						),
						'code'			=> array(
							'description'	=> 'Alias for name/abbreviation to find country',
						),
						'view_order'	=> array(
							'description'	=> 'Display order',
						),
					),
				),
				'getCountry'	=> array(
					'description'	=> 'Get details regarding specified country',
					'return_element'	=> 'country',
					'return_type'	=> 'Geography::Country',
					'parameters'	=> array(
						'id'	=> array(
							'description'	=> 'Country ID',
							'requirement_group'	=> 0,
							'validation_method'	=> 'Geography::Country::validCode()',
						),
						'abbreviation' => array(
							'description'	=> 'Country Abbreviation',
							'requirement_group'	=> 2,
							'validation_method'	=> 'Geography::Country::validCode()',
							'hidden' => true,
						),
						'code' => array(
							'description'	=> 'Alias for name/abbreviation to find country',
							'requirement_group'	=> 3,
							'validation_method'	=> 'Geography::Country::validCode()',
						),
					)
				),
				'findCountries'	=> array(
					'description'	=> 'Find countries matching specified criteria',
					'return_element'	=> 'country',
					'return_type'	=> 'Geography::Country',
					'parameters'	=> array(
						'name'	=> array(
							'description'	=> 'Country Name',
							'validation_method'	=> 'Geography::Country::validName()',
							'allow_wildcards'	=> true,
						),
						'abbreviation'	=> array(
							'description'	=> 'Country Abbreviation',
							'validation_method'	=> 'Geography::Country::validCode()',
						)
					),
				),
				'addProvince'	=> array(
					'description'	=> 'Add a province or state',
					'token_required'	=> true,
					'privilege_required'	=> 'manage geographical data',
					'return_element'	=> 'province',
					'return_type'	=> 'Geography::Province',
					'parameters'	=> array(
						'country_id'	=> array(
							'description'	=> 'Country ID',
							'validation_method'	=> 'Geography::Country::validCode()',
							'requirement_group'	=> 0,
						),
						'country_name'	=> array(
							'description'	=> 'Country Name',
							'validation_method'	=> 'Geography::Country::validName()',
							'requirement_group'	=> 1,
						),
						'name'			=> array(
							'description'	=> 'Name of province',
							'required' => true,
							'validation_method'	=> 'Geography::Province::validName()',
						),
						'abbreviation'	=> array(
							'description'	=> 'Abbreviation of province',
							'required' => true,
							'validation_method'	=> 'Geography::Province::validCode()',
						),
						'code'			=> array(
							'description'	=> 'Unique code (optional; generated from country+name if omitted)',
							'validation_method'	=> 'Geography::Province::validCode()',
						),
						'type'			=> array(
							'description'	=> 'Type (e.g. state, province)',
							'validation_method'	=> 'Geography::Province::validType()',
						),
						'label'			=> array(
							'description'	=> 'Label',
							'validation_method'	=> 'Geography::Province::validName()',
						),
					),
				),
				'updateProvince'	=> array(
					'description'	=> 'Update a province or state (identify by code or id)',
					'token_required'	=> true,
					'privilege_required'	=> 'manage geographical data',
					'return_element'	=> 'province',
					'return_type'	=> 'Geography::Province',
					'parameters'	=> array(
						'code'			=> array(
							'description'	=> 'Province code (to find)',
							'validation_method'	=> 'Geography::Province::validCode()',
							'requirement_group'	=> 0,
						),
						'id'			=> array(
							'description'	=> 'Province ID (to find)',
							'type'			=> 'integer',
							'requirement_group'	=> 1,
						),
						'name'			=> array(
							'description'	=> 'Name of province',
							'validation_method'	=> 'Geography::Province::validName()',
						),
						'abbreviation'	=> array(
							'description'	=> 'Abbreviation of province',
							'validation_method'	=> 'Geography::Province::validCode()',
						),
						'country_id'	=> array(
							'description'	=> 'Country ID',
							'validation_method'	=> 'Geography::Country::validCode()',
						),
						'type'			=> array(
							'description'	=> 'Type',
							'validation_method'	=> 'Geography::Province::validType()',
						),
						'label'			=> array(
							'description'	=> 'Label',
							'validation_method'	=> 'Geography::Province::validName()',
						),
					),
				),
				'getProvince'		=> array(
					'description'	=> 'Get details regarding specified province',
					'return_element'	=> 'province',
					'return_type'	=> 'Geography::Province',
					'parameters'	=> array(
						'id'	=> array(
							'description'	=> 'Province ID',
							'requirement_group'	=> 0,
							'validation_method'	=> 'Geography::Province::validCode()',
						),
						'name'	=> array(
							'description'	=> 'Province Name',
							'requirement_group'	=> 1,
							'validation_method'	=> 'Geography::Province::validName()',
						),
						'country_name'	=> array(
							'description'	=> 'Country Name',
							'requirement_group'	=> 1,
							'validation_method'	=> 'Geography::Country::validName()',
						),
						'country_abbreviation'	=> array(
							'description'	=> 'Country Abbreviation',
							'requirement_group'	=> 1,
							'validation_method'	=> 'Geography::Country::validCode()',
						),
						'abbreviation' => array(
							'description'	=> 'Province Abbreviation',
							'requirement_group'	=> 2,
							'validation_method'	=> 'Geography::Province::validCode()',
						),
					)
				),
				'findProvinces'		=> array(
					'description'	=> 'Find provinces or states matching specified criteria',
					'return_element'	=> 'province',
					'return_type'	=> 'Geography::Province',
					'parameters'	=> array(
						'name'	=> array(
							'description'	=> 'Province Name',
							'validation_method'	=> 'Geography::Province::validName()',
							'allow_wildcards'	=> true,
						),
						'abbreviation'	=> array(
							'description'	=> 'Province Abbreviation',
							'validation_method'	=> 'Geography::Province::validCode()',
						),
						'country_id'	=> array(
							'description'	=> 'Country ID',
							'validation_method'	=> 'Geography::Country::validCode()',
							'hidden' => true,
						),
						'country_name'	=> array(
							'description'	=> 'Country Name',
							'validation_method'	=> 'Geography::Country::validName()',
							'allow_wildcards'	=> true,
						),
						'country_abbreviation'	=> array(
							'description'	=> 'Country Abbreviation',
							'validation_method'	=> 'Geography::Country::validCode()',
							'allow_wildcards'	=> true
						),
					),
				),
				'getCounty'		=> array(
					'description'	=> 'Get details regarding specified county',
					'return_element'	=> 'county',
					'return_type'	=> 'Geography::County',
					'parameters'	=> array(
						'country_abbreviation' => array(
							'description' => 'Country Abbreviation',
							'validation_method'	=> 'Geography::Country::validCode()',
						),
						'province_abbreviation' => array(
							'description' => 'Province/State Abbreviation',
							'validation_method'	=> 'Geography::Province::validCode()',
						),
						'name' => array(
							'description' => 'County Name',
							'validation_method'	=> 'Geography::County::validName()',
						),
					),
				),
				'addCounty'		=> array(
					'description'	=> 'Add a county',
					'token_required'	=> true,
					'privilege_required'	=> 'manage geographical data',
					'return_element'	=> 'county',
					'return_type'	=> 'Geography::County',
					'parameters'	=> array(
						'country_abbreviation' => array(
							'description' => 'Country Abbreviation',
							'required' => true,
							'validation_method'	=> 'Geography::Country::validCode()',
						),
						'province_abbreviation' => array(
							'description' => 'Province/State Abbreviation',
							'required' => true,
							'validation_method'	=> 'Geography::Province::validCode()',
						),
						'name' => array(
							'description' => 'County Name',
							'required' => true,
							'validation_method'	=> 'Geography::County::validName()',
						),
					),
				),
				'getCounty'		=> array(
					'description'	=> 'Get details regarding specified county',
					'return_element'	=> 'county',
					'return_type'	=> 'Geography::County',
					'parameters'	=> array(
						'country_abbreviation' => array(
							'description' => 'Country Abbreviation',
							'validation_method'	=> 'Geography::Country::validCode()',
						),
						'province_abbreviation' => array(
							'description' => 'Province/State Abbreviation',
							'validation_method'	=> 'Geography::Province::validCode()',
						),
						'name' => array(
							'description' => 'County Name',
							'validation_method'	=> 'Geography::County::validName()',
						),
					),
				),
				'findCounties'		=> array(
					'description'	=> 'Find counties matching specified criteria',
					'return_element'	=> 'county',
					'return_type'	=> 'Geography::County',
					'parameters'	=> array(
						'country_abbreviation' => array(
							'description' => 'Country Abbreviation',
							'validation_method'	=> 'Geography::Country::validCode()',
						),
						'province_abbreviation' => array(
							'description' => 'Province/State Abbreviation',
							'validation_method'	=> 'Geography::Province::validCode()',
						),
						'name' => array(
							'description' => 'County Name',
							'validation_method'	=> 'Geography::County::validName()',
							'allow_wildcards' => true,
						),
					),
				),
				'addZipCode'	=> array(
					'description'	=> 'Add a zip code with associated country, province/state, county, and city',
					'token_required'	=> true,
					'privilege_required'	=> 'manage geographical data',
					'return_element'	=> 'zip_code',
					'return_type'	=> 'Geography::ZipCode',
					'parameters'	=> array(
						'code'			=> array(
							'description'	=> 'Zip code',
							'required' => true,
						),
						'province_name' => array(
							'description' => 'Province/State Name',
							'requirement_group' => 0,
						),
						'province_abbreviation' => array(
							'description' => 'Province/State Abbreviation',
							'requirement_group' => 0,
						),
						'country_name' => array(
							'description' => 'Country Name',
							'requirement_group' => 1,
						),
						'country_abbreviation' => array(
							'description' => 'Country Abbreviation',
							'requirement_group' => 1,
						),
						'county_name' => array(
							'description' => 'County Name',
						),
						'city_name' => array(
							'description' => 'City Name',
						),
						'latitude' => array(
							'description' => 'Latitude coordinate',
							'type' => 'float',
						),
						'longitude' => array(
							'description' => 'Longitude coordinate',
							'type' => 'float',
						),
					),
				),
				'updateZipCode'	=> array(
					'description'	=> 'Update a zip code\'s details (identify by code or id)',
					'token_required'	=> true,
					'privilege_required'	=> 'manage geographical data',
					'return_element'	=> 'zip_code',
					'return_type'	=> 'Geography::ZipCode',
					'parameters'	=> array(
						'id'			=> array(
							'description'	=> 'Zip code ID',
							'type'			=> 'integer',
							'requirement_group' => 0,
						),
						'code'			=> array(
							'description'	=> 'Zip code (to find)',
							'requirement_group' => 0,
						),
						'province_name' => array(
							'description' => 'Province/State Name',
							'validation_method'	=> 'Geography::Province::validName()',
						),
						'province_abbreviation' => array(
							'description' => 'Province/State Abbreviation',
							'validation_method'	=> 'Geography::Province::validCode()',
						),
						'county_name' => array(
							'description' => 'County Name',
							'validation_method'	=> 'Geography::County::validName()',
						),
						'city_name' => array(
							'description' => 'City Name',
							'validation_method'	=> 'Geography::City::validName()',
						),
						'latitude' => array(
							'description' => 'Latitude coordinate',
							'type' => 'float',
						),
						'longitude' => array(
							'description' => 'Longitude coordinate',
							'type' => 'float',
						),
					),
				),
				'getZipCode'	=> array(
					'description'	=> 'Get details regarding specified zip code (identify by code or id)',
					'return_element'	=> 'zip_code',
					'return_type'	=> 'Geography::ZipCode',
					'parameters'	=> array(
						'id' => array(
							'description' => 'Zip code ID',
							'type' => 'integer',
							'requirement_group' => 0,
						),
						'code' => array(
							'description' => 'Zip code',
							'requirement_group' => 0,
						),
						'country_id' => array(
							'description' => 'Country ID (to validate that zip code belongs to specified country)',
						),
						'country_abbreviation' => array(
							'description' => 'Country Abbreviation (to validate that zip code belongs to specified country)',
						),
						'country_name' => array(
							'description' => 'Country Name (to validate that zip code belongs to specified country)',
						),
						'province_name' => array(
							'description' => 'Province/State Name (to validate that zip code belongs to specified province/state)',
						),
						'province_abbreviation' => array(
							'description' => 'Province/State Abbreviation (to validate that zip code belongs to specified province/state)',
						),
						'county_name' => array(
							'description' => 'County Name (to validate that zip code belongs to specified county)',
							'validation_method'	=> 'Geography::County::validName()',
						),
						'city_name' => array(
							'description' => 'City Name (to validate that zip code belongs to specified city)',
							'validation_method'	=> 'Geography::City::validName()',
						)
					),
				),
				'loadZipCode' => array(
					'description'	=> 'Load a zip code\'s details by code and country (country can be identified by id, name, or abbreviation)',
					'return_element'	=> 'zip_code',
					'return_type'	=> 'Geography::ZipCode',
					'hidden'	=> true,
					'parameters'	=> array(
						'id' => array(
							'description' => 'Country ID',
							'type' => 'integer',
							'required' => true,
						),
					),
				),
				'findZipCodes'	=> array(
					'description'	=> 'Find zip codes matching specified criteria',
					'return_element'	=> 'zip_code',
					'return_type'	=> 'Geography::ZipCode',
					'parameters'	=> array(
						'code' => array(
							'description' => 'Zip code',
						),
						'country_id' => array(
							'description' => 'Country ID',
						),
						'country_abbreviation' => array(
							'description' => 'Country Abbreviation',
						),
						'country_name' => array(
							'description' => 'Country Name',
						),
						'province_name' => array(
							'description' => 'Province/State Name',
						),
						'province_abbreviation' => array(
							'description' => 'Province/State Abbreviation',
						),
						'county_name' => array(
							'description' => 'County Name',
							'validation_method'	=> 'Geography::County::validName()',
						),
						'city_name' => array(
							'description' => 'City Name',
							'validation_method'	=> 'Geography::City::validName()',
						),
					),
				),
				'addCity'	=> array(
					'description'	=> 'Add a city',
					'token_required'	=> true,
					'privilege_required'	=> 'manage geographical data',
					'return_element'	=> 'city',
					'return_type'	=> 'Geography::City',
					'parameters'	=> array(
						'name' => array(
							'description' => 'City Name',
							'required' => true,
						),
						'country_abbreviation' => array(
							'description' => 'Country Abbreviation',
							'required' => true,
							'validation_method'	=> 'Geography::Country::validCode()',
						),
						'province_abbreviation' => array(
							'description' => 'Province/State Abbreviation',
							'required' => true,
							'validation_method'	=> 'Geography::Province::validCode()',
						),
						'county_name' => array(
							'description' => 'County Name',
							'validation_method'	=> 'Geography::County::validName()',
						),
						'latitude' => array(
							'description' => 'Latitude coordinate',
							'type' => 'float',
						),
						'longitude' => array(
							'description' => 'Longitude coordinate',
							'type' => 'float',
						),
					),
				),
				'getCity'	=> array(
					'description'	=> 'Get details regarding specified city',
					'return_element'	=> 'city',
					'return_type'	=> 'Geography::City',
					'parameters'	=> array(
						'id' => array(
							'description' => 'City ID',
							'type' => 'integer',
							'requirement_group' => 0,
						),
						'name' => array(
							'description' => 'City Name',
							'requirement_group' => 0,
						),
						'country_abbreviation' => array(
							'description' => 'Country Abbreviation (to validate that city belongs to specified country)',
							'requirement_group' => 1,
							'validation_method'	=> 'Geography::Country::validCode()',
						),
						'country_name' => array(
							'description' => 'Country Name (to validate that city belongs to specified country)',
							'requirement_group' => 1,
							'validation_method'	=> 'Geography::Country::validName()',
						),
						'province_abbreviation' => array(
							'description' => 'Province/State Abbreviation (to validate that city belongs to specified province/state)',
							'requirement_group' => 2,
							'validation_method'	=> 'Geography::Province::validCode()',
						),
						'province_name' => array(
							'description' => 'Province/State Name (to validate that city belongs to specified province/state)',
							'requirement_group' => 2,
							'validation_method'	=> 'Geography::Province::validName()',
						),
						'county_name' => array(
							'description' => 'County Name (to validate that city belongs to specified county)',
							'validation_method'	=> 'Geography::County::validName()',
						),
					),
				),
				'findCities'	=> array(
					'description'	=> 'Find cities matching specified criteria',
					'return_element'	=> 'city',
					'return_type'	=> 'Geography::City',
					'parameters'	=> array(
						'name' => array(
							'description' => 'City Name',
							'validation_method'	=> 'Geography::City::validName()',
						),
						'country_abbreviation' => array(
							'description' => 'Country Abbreviation',
							'validation_method'	=> 'Geography::Country::validCode()',
						),
						'country_name' => array(
							'description' => 'Country Name',
							'validation_method'	=> 'Geography::Country::validName()',
						),
						'province_abbreviation' => array(
							'description' => 'Province/State Abbreviation',
							'validation_method'	=> 'Geography::Province::validCode()',
						),
						'province_name' => array(
							'description' => 'Province/State Name',
							'validation_method'	=> 'Geography::Province::validName()',
						),
						'county_name' => array(
							'description' => 'County Name',
							'validation_method'	=> 'Geography::County::validName()',
						),
					),
				),
				'getStateByZipCode'	=> array(
					'description'	=> 'Get administrative details (country, province/state, county, city) for a given zip code',
					'return_element'	=> 'state',
					'return_type'	=> 'Geography::State',
					'parameters'	=> array(
						'zip_code' => array(
							'description'	=> 'Zip code to look up',
							'required' => true,
						),
					),
				),
				'setZipCodeWeather'	=> array(
					'description'	=> 'Set a weather record for a specific zip code',
					'token_required'	=> true,
					'privilege_required'	=> 'manage geographical data',
					'return_element'	=> 'weather_record',
					'return_type'	=> 'Geography::WeatherRecord',
					'parameters'	=> array(
						'zip_code' => array(
							'description'	=> 'Zip code for which to set weather record',
							'required' => true,
						),
						'country_abbreviation' => array(
							'description' => 'Country Abbreviation (to validate that zip code belongs to specified country)',
							'requirement_group' => 0,
							'validation_method'	=> 'Geography::Country::validCode()',
							'hidden' => true,
						),
						'country_code' => array(
							'description' => 'Country Code (to validate that zip code belongs to specified country)',
							'requirement_group' => 0,
							'validation_method'	=> 'Geography::Country::validCode()',
						),
						'province_abbreviation' => array(
							'description' => 'Province/State Abbreviation (to validate that zip code belongs to specified province/state)',
							'requirement_group' => 1,
							'validation_method'	=> 'Geography::Province::validCode()',
							'hidden' => true,
						),
						'province_code' => array(
							'description' => 'Province/State Code (to validate that zip code belongs to specified province/state)',
							'requirement_group' => 1,
							'validation_method'	=> 'Geography::Province::validCode()',
						),
						'timestamp' => array(
							'description' => 'Timestamp of weather record (alternatively, can provide date_time)',
							'type' => 'integer',
						),
						'date_time' => array(
							'description' => 'Date and time of weather record (alternative to timestamp)',
							'type' => 'datetime',
						),
						'conditions' => array(
							'description' => 'Weather conditions (e.g. sunny, cloudy)',
						),
						'temperature_celsius' => array(
							'description' => 'Temperature reading in Celsius',
							'type' => 'float',
						),
						'temperature_fahrenheit' => array(
							'description' => 'Temperature reading in Fahrenheit',
							'type' => 'float',
						),
						'humidity' => array(
							'description' => 'Humidity percentage',
							'type' => 'float',
						),
						'pressure' => array(
							'description' => 'Atmospheric pressure in hPa',
							'type' => 'float',
						),
						'wind_speed_mps' => array(
							'description' => 'Wind speed in meters per second',
							'type' => 'float',
						),
						'wind_speed_kph' => array(
							'description' => 'Wind speed in kilometers per hour',
							'type' => 'float',
						),
						'wind_speed_mph' => array(
							'description' => 'Wind speed in miles per hour',
							'type' => 'float',
						),
						'wind_direction' => array(
							'description' => 'Wind direction in Degrees (meteorological)',
							'type' => 'float',
						),
						'wind_gust_mps' => array(
							'description' => 'Wind gust speed in meters per second',
							'type' => 'float',
						),
						'wind_gust_kph' => array(
							'description' => 'Wind gust speed in kilometers per hour',
							'type' => 'float',
						),
						'wind_gust_mph' => array(
							'description' => 'Wind gust speed in miles per hour',
							'type' => 'float',
						),
						'precipitation_mm' => array(
							'description' => 'Precipitation amount in millimeters',
							'type' => 'float',
						),
						'precipitation_in' => array(
							'description' => 'Precipitation amount in inches',
							'type' => 'float',
						),
						'visibility_km' => array(
							'description' => 'Visibility distance in kilometers',
							'type' => 'float',
						),
						'visibility_miles' => array(
							'description' => 'Visibility distance in miles',
							'type' => 'float',
						)
					),
				),
				'getZipCodeWeather' => array(
					'description' => 'Get the latest weather record before a specific timestamp for a specific zip code',
					'return_element' => 'weather_record',
					'return_type' => 'Geography::WeatherRecord',
					'parameters' => array(
						'zip_code' => array(
							'description' => 'Zip code for which to get weather record',
							'required' => true,
						),
						'country_abbreviation' => array(
							'description' => 'Country Abbreviation (to validate that zip code belongs to specified country)',
							'requirement_group' => 0,
							'hidden' => true,
						),
						'country_code' => array(
							'description' => 'Country Code (to validate that zip code belongs to specified country)',
							'requirement_group' => 0,
							'hidden' => true,
						),
						'province_abbreviation' => array(
							'description' => 'Province/State Abbreviation (to validate that zip code belongs to specified province/state)',
							'requirement_group' => 1,
							'hidden' => true,
						),
						'province_code' => array(
							'description' => 'Province/State Code (to validate that zip code belongs to specified province/state)',
							'requirement_group' => 1,
							'hidden' => true,
						),
						'timestamp' => array(
							'description' => 'Timestamp to find previous weather record for (alternatively, can provide date_time)',
							'type' => 'integer',
						),
						'date_time' => array(
							'description' => 'Date and time to find previous weather record for (alternative to timestamp)',
							'type' => 'datetime',
						),
					),
				),
				'findWeatherRecords' => array(
					'description' => 'Find weather records matching specified criteria (e.g. zip code, date range)',
					'return_element' => 'weather_record',
					'return_type' => 'Geography::WeatherRecord',
					'parameters' => array(
						'zip_code' => array(
							'description' => 'Zip code to find weather records for',
							'type' => 'string',
							'validation_method'	=> 'Geography::ZipCode::validCode()',
						),
						'country_abbreviation' => array(
							'description' => 'Country Abbreviation (to validate that zip code belongs to specified country)',
							'validation_method'	=> 'Geography::Country::validCode()',
						),
						'country_name' => array(
							'description' => 'Country Name (to validate that zip code belongs to specified country)',
							'validation_method'	=> 'Geography::Country::validName()',
						),
						'province_abbreviation' => array(
							'description' => 'Province/State Abbreviation (to validate that zip code belongs to specified province/state)',
							'validation_method'	=> 'Geography::Province::validCode()',
						),
						'province_name' => array(
							'description' => 'Province/State Name (to validate that zip code belongs to specified province/state)',
							'validation_method'	=> 'Geography::Province::validName()',
						),
						'start_timestamp' => array(
							'description' => 'Start of date range to find weather records for (alternatively, can provide start_date_time)',
							'type' => 'integer',
						),
						'start_date_time' => array(
							'description' => 'Start of date range to find weather records for (alternative to start_timestamp)',
							'type' => 'datetime',
						),
						'end_timestamp' => array(
							'description' => 'End of date range to find weather records for (alternatively, can provide end_date_time)',
							'type' => 'integer',
						),
						'end_date_time' => array(
							'description' => 'End of date range to find weather records for (alternative to end_timestamp)',
							'type' => 'datetime',
						),
					),
				),
				'setZipCodeTimes' => array(
					'description' => 'Add a times record for a specific zip code',
					'token_required' => true,
					'privilege_required' => 'manage weather data',
					'return_element' => 'times_record',
					'return_type' => 'Geography::TimesRecord',
					'parameters' => array(
						'country_code' => array(
							'description' => 'Country code (to validate that zip code belongs to specified country)',
							'requirement_group' => 0,
						),
						'country_abbreviation' => array(
							'description' => 'Country Abbreviation (to validate that zip code belongs to specified country)',
							'requirement_group' => 0,
							'validation_method'	=> 'Geography::Country::validCode()',
							'hidden' => true,
						),
						'province_code' => array(
							'description' => 'Province/State code (to validate that zip code belongs to specified province/state)',
							'requirement_group' => 2,
						),
						'province_abbreviation' => array(
							'description' => 'Province/State Abbreviation (to validate that zip code belongs to specified province/state)',
							'requirement_group' => 2,
							'validation_method'	=> 'Geography::Province::validCode()',
							'hidden' => true,
						),
						'zip_code' => array(
							'description' => 'Zip code for which to add times record',
							'required' => true,
						),
						'timestamp' => array(
							'description' => 'Timestamp of times record (alternatively, can provide date_time)',
							'type' => 'integer',
						),
						'date_time' => array(
							'description' => 'Date and time of times record (alternative to timestamp)',
							'type' => 'datetime',
						),
						'sunrise' => array(
							'description' => 'Sunrise time (timestamp or datetime)',
						),
						'sunset' => array(
							'description' => 'Sunset time (timestamp or datetime)',
						),
						'moonrise' => array(
							'description' => 'Moonrise time (timestamp or datetime)',
						),
						'moonset' => array(
							'description' => 'Moonset time (timestamp or datetime)',
						),
						'moon_phase' => array(
							'description' => 'Moon phase (e.g. new moon, waxing crescent, etc.)',
						),
						'timezone' => array(
							'description' => 'Time zone identifier (e.g. "America/New_York")',
						),
					),
				),
				'getZipCodeTimes' => array(
					'description' => 'Get a times record for a specific zip code',
					'return_element' => 'times_record',
					'return_type' => 'Geography::TimesRecord',
					'parameters' => array(
						'zip_code' => array(
							'description' => 'Zip code for which to get times record',
							'required' => true,
						),
						'timestamp' => array(
							'description' => 'Timestamp of times record (alternatively, can provide date_time)',
							'type' => 'integer',
						),
						'date_time' => array(
							'description' => 'Date and time of times record (alternative to timestamp)',
							'type' => 'datetime',
						),
					),
				),
			);
		}
	}
