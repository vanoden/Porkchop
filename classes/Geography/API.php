<?php
	namespace Geography;

	/* Base Class for APIs */
	class API extends \API {

		public function __construct() {
			$this->_name = 'geography';
			$this->_version = '0.1.1';
			$this->_release = '2020-01-21';
			$this->_schema = new Schema();
			parent::__construct();
		}
		###################################################
		### Add a Country								###
		###################################################
		public function addCountry() {
			$country = new \Geography\Country();
	
			$parameters = array();
			if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			if (isset($_REQUEST['abbreviation'])) $parameters['abbreviation'] = $_REQUEST['abbreviation'];
			if (! $country->add($parameters)) $this->error("Error adding country: ".$country->error());
	
			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('country',$country);
			$response->print();
		}
	
		###################################################
		### Update a Country							###
		###################################################
		public function updateCountry() {
			$country = new \Geography\Country();
			$country->get($_REQUEST['code']);
			if ($country->error()) $this->error("Error finding country: ".$country->error(),'error',__FILE__,__LINE__);
			if (! $country->id) $this->error("Request not found");
	
			$parameters = array();
			$country->update($parameters);

			if ($country->error()) $this->error("Error updating country: ".$country->error(),'error',__FILE__,__LINE__);
			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('country',$country);
			$response->print();
		}
	
		###################################################
		### Get Specified Country						###
		###################################################
		public function getCountry() {
			if (isset($_REQUEST['name'])) {
				$country = new \Geography\Country();
				$country->get($_REQUEST['name']);
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
			if ($_REQUEST['status']) $parameters['status'] = $_REQUEST['status'];
			
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
			$country = new \Geography\Country($_REQUEST['country_id']);
			if (! $country->id) $this->error("Country not found");
	
			$province = new \Geography\Province();
	
			$parameters = array();
			if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			if (isset($_REQUEST['abbreviation'])) $parameters['abbreviation'] = $_REQUEST['abbreviation'];
			$parameters['country_id'] = $country->id;
			if (! $province->add($parameters)) $this->error("Error adding province: ".$province->error());
	
			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('province',$province);
			$response->print();
		}
	
		###################################################
		### Update a Province							###
		###################################################
		public function updateProvince() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$province = new \Geography\Province();
			$province->get($_REQUEST['code']);
			if ($province->error()) $this->error("Error finding province: ".$province->error(),'error',__FILE__,__LINE__);
			if (! $province->id) $this->error("Province not found");
	
			$parameters = array();
			$province->update(
				$parameters
			);
			if ($province->error()) $this->error("Error updating country: ".$province->error(),'error',__FILE__,__LINE__);
	
			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('province',$province);
			$response->print();
		}
	
		###################################################
		### Get Specified Province						###
		###################################################
		public function getProvince() {
			if (!empty($_REQUEST['country_id'])) {
				$_REQUEST['country_id'] = (int) $_REQUEST['country_id'];
				$country = new \Geography\Country($_REQUEST['country_id']);
				if (! $country->id) $this->error("Country not found");
	
				$province = new \Geography\Province();
				if (! $province->getProvince($country->id,$_REQUEST['name'])) $this->error("Province not found");
			}
			elseif (isset($_REQUEST['id'])) {
				$province = new \Geography\Province($_REQUEST['id']);
				if (! $province->id) $this->error("Province not found");
			}
			else {
				$this->incompleteRequest("Not enough parameters");
			}
	
			if (! $province->exists()) $this->notFound();

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
				if (! $country->get($_REQUEST['country_name'])) $this->error("Country not found");
				$parameters['country_id'] = $country->id;
			}
			elseif ($_REQUEST['country_id']) {
				$country = new \Geography\Country($_REQUEST['country_id']);
				$parameters['country_id'] = $country->id;
			}
			if ($_REQUEST['name']) $parameters['name'] = $_REQUEST['name'];
			
			$provinces = $provinceList->find($parameters);
			if ($provinceList->error()) $this->error("Error finding provinces: ".$provinceList->error());
	
			$response = new \APIResponse();
			$response->success(true);
			$response->AddElement('province',$provinces);
			$response->print();
		}

		public function _methods() {
			return array(
				'ping'	=> array(),
				'addCountry'	=> array(
					'description'	=> 'Add a country',
					'token_required'	=> true,
					'privilege_required'	=> 'manage geographical data',
					'return_element'	=> 'country',
					'return_type'	=> 'Geography::Country',
					'parameters'	=> array(
						'name'			=> array(
							'description'	=> 'Name of country',
							'validation_method'	=> 'Geography::Country::validName()',
							'required' => true
						),
						'abbreviation'	=> array(
							'description'	=> 'Abbreviation of country',
							'validation_method'	=> 'Geography::Country::validCode()',
						),
					),
				),
				'updateCountry'	=> array(
					'description'	=> 'Update a country',
					'token_required'	=> true,
					'privilege_required'	=> 'manage geographical data',
					'return_element'	=> 'country',
					'return_type'	=> 'Geography::Country',
					'parameters'	=> array(
						'code'			=> array(
							'description'	=> 'Country code',
							'validation_method'	=> 'Geography::Country::validCode()',
							'required' => true
						),
						'name'			=> array(
							'description'	=> 'Name of country',
							'validation_method'	=> 'Geography::Country::validName()',
						),
						'abbreviation'	=> array(
							'description'	=> 'Abbreviation of country',
							'validation_method'	=> 'Geography::Country::validCode()',
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
						'name'	=> array(
							'description'	=> 'Country Name',
							'requirement_group'	=> 1,
							'validation_method'	=> 'Geography::Country::validName()',
						),
						'abbreviation' => array(
							'description'	=> 'Country Abbreviation',
							'requirement_group'	=> 2,
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
							'required' => true
						),
						'name'			=> array(
							'description'	=> 'Name of province',
							'validation_method'	=> 'Geography::Province::validName()',
							'required' => true
						),
						'abbreviation'	=> array(
							'description'	=> 'Abbreviation of province',
							'validation_method'	=> 'Geography::Province::validCode()',
						),
					),
				),
				'updateProvince'	=> array(
					'description'	=> 'Update a province or state',
					'token_required'	=> true,
					'privilege_required'	=> 'manage geographical data',
					'return_element'	=> 'province',
					'return_type'	=> 'Geography::Province',
					'parameters'	=> array(
						'code'			=> array(
							'description'	=> 'Province code',
							'validation_method'	=> 'Geography::Province::validCode()',
							'required' => true
						),
						'name'			=> array(
							'description'	=> 'Name of province',
							'validation_method'	=> 'Geography::Province::validName()',
						),
						'abbreviation'	=> array(
							'description'	=> 'Abbreviation of province',
							'validation_method'	=> 'Geography::Province::validCode()',
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
						),
						'country_name'	=> array(
							'description'	=> 'Country Name',
							'validation_method'	=> 'Geography::Country::validName()',
							'allow_wildcards'	=> true,
						),
					),
				),
			);
		}
	}
