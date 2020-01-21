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
			if (! $country->add($parameters)) app_error("Error adding country: ".$country->error());
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->country = $country;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Update a Country							###
		###################################################
		public function updateCountry() {
			$country = new \Geography\Country();
			$country->get($_REQUEST['code']);
			if ($country->error) app_error("Error finding country: ".$country->error(),'error',__FILE__,__LINE__);
			if (! $country->id) error("Request not found");
	
			$parameters = array();
			$country->update(
				$parameters
			);
			if ($country->error) app_error("Error updating country: ".$country->error(),'error',__FILE__,__LINE__);
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->country = $country;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Get Specified Country						###
		###################################################
		public function getCountry() {
			if (isset($_REQUEST['name'])) {
				$country = new \Geography\Country();
				$country->get($_REQUEST['name']);
				if ($country->error()) app_error($country->error());
			}
			elseif(isset($_REQUEST['id'])) {
				$country = new \Geography\Country($_REQUEST['id']);
			}
			else {
				error("Not enough parameters");
			}
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->country = $country;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Find matching Countrys						###
		###################################################
		public function findCountries() {
			$countryList = new \Geography\CountryList();
			
			$parameters = array();
			if ($_REQUEST['status']) $parameters['status'] = $_REQUEST['status'];
			
			$countries = $countryList->find($parameters);
			if ($countryList->error) app_error("Error finding countries: ".$countryList->error());
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->country = $countries;
	
			print $this->formatOutput($response);
		}
		###################################################
		### Add a Province or State						###
		###################################################
		public function addProvince() {
			$country = new \Geography\Country($_REQUEST['country_id']);
			if (! $country->id) app_error("Country not found");
	
			$province = new \Geography\Province();
	
			$parameters = array();
			if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			if (isset($_REQUEST['abbreviation'])) $parameters['abbreviation'] = $_REQUEST['abbreviation'];
			$parameters['country_id'] = $country->id;
			if (! $province->add($parameters)) app_error("Error adding province: ".$province->error());
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->province = $province;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Update a Province							###
		###################################################
		public function updateProvince() {
			$country = new \Geography\Country();
			$country->get($_REQUEST['code']);
			if ($country->error) app_error("Error finding country: ".$country->error(),'error',__FILE__,__LINE__);
			if (! $country->id) error("Request not found");
	
			$parameters = array();
			$country->update(
				$parameters
			);
			if ($country->error) app_error("Error updating country: ".$country->error(),'error',__FILE__,__LINE__);
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->country = $country;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Get Specified Province						###
		###################################################
		public function getProvince() {
			if (isset($_REQUEST['country_id'])) {
				$country = new \Geography\Country($_REQUEST['country_id']);
				if (! $country->id) error("Country not found");
	
				$province = new \Geography\Province();
				if (! $province->get($country->id,$_REQUEST['name'])) error("Province not found");
			}
			elseif (isset($_REQUEST['id'])) {
				$province = new \Geography\Province($_REQUEST['id']);
				if (! $province->id) error("Province not found");
			}
			else {
				error("Not enough parameters");
			}
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->province = $province;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Find matching Provinces						###
		###################################################
		public function findProvinces() {
			$provinceList = new \Geography\ProvinceList();
			
			$parameters = array();
			if ($_REQUEST['status']) $parameters['status'] = $_REQUEST['status'];
			if ($_REQUEST['country_name']) {
				$country = new \Geography\Country();
				if (! $country->get($_REQUEST['country_name'])) app_error("Country not found");
				$parameters['country_id'] = $country->id;
			}
			elseif ($_REQUEST['country_id']) {
				$country = new \Geography\Country($_REQUEST['country_id']);
				$parameters['country_id'] = $country->id;
			}
			
			$provinces = $provinceList->find($parameters);
			if ($provinceList->error) app_error("Error finding provinces: ".$provinceList->error());
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->province = $provinces;
	
			print $this->formatOutput($response);
		}

		public function _methods() {
			return array(
				'ping'	=> array(),
				'addCountry'	=> array(
					'name'		=> array('required' => true),
					'abbreviation'	=> array(),
				),
				'updateCountry'	=> array(
				),
				'getCountry'	=> array(
					'name'	=> array('required' => true),
				),
				'findCountries'	=> array(
				),
				'addProvince'	=> array(
					'country_id'	=> array('required' => true),
					'name'			=> array('required' => true),
					'abbreviation'	=> array('required' => true),
				),
				'updateProvince'	=> array(
				),
				'getProvince'		=> array(
					'country_id'	=> array('required' => true),
					'name'			=> array('required' => true)
				),
				'findProvinces'		=> array(
					'country_id'	=> array(),
					'country_name'	=> array(),
				),
			);
		}
	}
