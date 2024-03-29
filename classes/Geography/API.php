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
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$country = new \Geography\Country();
	
			$parameters = array();
			if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			if (isset($_REQUEST['abbreviation'])) $parameters['abbreviation'] = $_REQUEST['abbreviation'];
			if (! $country->add($parameters)) $this->error("Error adding country: ".$country->error());
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->country = $country;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Update a Country							###
		###################################################
		public function updateCountry() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$country = new \Geography\Country();
			$country->get($_REQUEST['code']);
			if ($country->error) $this->error("Error finding country: ".$country->error(),'error',__FILE__,__LINE__);
			if (! $country->id) $this->error("Request not found");
	
			$parameters = array();
			$country->update(
				$parameters
			);
			if ($country->error) $this->error("Error updating country: ".$country->error(),'error',__FILE__,__LINE__);
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
				if ($country->error()) $this->error($country->error());
			}
			elseif(isset($_REQUEST['id'])) {
				$country = new \Geography\Country($_REQUEST['id']);
			}
			else {
				$this->error("Not enough parameters");
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
			if ($countryList->error) $this->error("Error finding countries: ".$countryList->error);
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->country = $countries;
	
			print $this->formatOutput($response);
		}
		###################################################
		### Add a Province or State						###
		###################################################
		public function addProvince() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$country = new \Geography\Country($_REQUEST['country_id']);
			if (! $country->id) $this->error("Country not found");
	
			$province = new \Geography\Province();
	
			$parameters = array();
			if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			if (isset($_REQUEST['abbreviation'])) $parameters['abbreviation'] = $_REQUEST['abbreviation'];
			$parameters['country_id'] = $country->id;
			if (! $province->add($parameters)) $this->error("Error adding province: ".$province->error());
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->province = $province;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Update a Province							###
		###################################################
		public function updateProvince() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$country = new \Geography\Country();
			$country->get($_REQUEST['code']);
			if ($country->error) $this->error("Error finding country: ".$country->error(),'error',__FILE__,__LINE__);
			if (! $country->id) $this->error("Request not found");
	
			$parameters = array();
			$country->update(
				$parameters
			);
			if ($country->error) $this->error("Error updating country: ".$country->error(),'error',__FILE__,__LINE__);
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->country = $country;
	
			print $this->formatOutput($response);
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
				$this->error("Not enough parameters");
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
				if (! $country->get($_REQUEST['country_name'])) $this->error("Country not found");
				$parameters['country_id'] = $country->id;
			}
			elseif ($_REQUEST['country_id']) {
				$country = new \Geography\Country($_REQUEST['country_id']);
				$parameters['country_id'] = $country->id;
			}
			
			$provinces = $provinceList->find($parameters);
			if ($provinceList->error) $this->error("Error finding provinces: ".$provinceList->error());
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->province = $provinces;
	
			print $this->formatOutput($response,$_REQUEST['_format']);
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
