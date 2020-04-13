<?php
    ###############################################
    ### Handle API Request for Country			###
    ### tracking.								###
    ### A. Caravello 10/30/2019               	###
    ###############################################

	###############################################
	### Load API Objects						###
    ###############################################
	$_package = array(
		"name"		=> "sales",
		"version"	=> "0.1.1",
		"release"	=> "2019-10-30",
	);

	app_log("Request: ".print_r($_REQUEST,true),'debug',__FILE__,__LINE__);

	# Call Requested Event
	if ($_REQUEST["method"]) {
		# Call the Specified Method
		$function_name = $_REQUEST["method"];
		$function_name();
		exit;
	}
	# Only Developers Can See The API
	elseif (! $GLOBALS['_SESSION_']->customer->has_role('sales manager')) {
		header("location: /_sales/home");
		exit;
	}

	###################################################
	### Just See if Server Is Communicating			###
	###################################################
	function ping() {
		$response = new \HTTP\Response();
		$response->header->session = $GLOBALS['_SESSION_']->code;
		$response->header->method = $_REQUEST["method"];
		$response->header->date = system_time();
		$response->message = "PING RESPONSE";
		$response->success = 1;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		print formatOutput($response);
	}
	###################################################
	### Add a Order								###
	###################################################
	function addOrder() {
		$order = new \Sales\Order();

		$parameters = array();
		if (isset($_REQUEST['customer_id'])) $parameters['customer_id'] = $_REQUEST['customer_id'];
		if (isset($_REQUEST['salesperson_id'])) $parameters['salesperson_id'] = $_REQUEST['salesperson_id'];
		if (! $order->add($parameters)) app_error("Error adding order: ".$order->error());

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->order = $order;

		print formatOutput($response);
	}

	###################################################
	### Update a Order							###
	###################################################
	function updateOrder() {
		$order = new \Sales\Order();
		$order->get($_REQUEST['code']);
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

		print formatOutput($response);
	}

	###################################################
	### Get Specified Country						###
	###################################################
	function getCountry() {
		$country = new \Geography\Country();
		$country->get($_REQUEST['name']);
		if ($country->error()) app_error($country->error());

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->country = $country;

		print formatOutput($response);
	}

	###################################################
	### Find matching Countrys						###
	###################################################
	function findCountries() {
		$countryList = new \Geography\CountryList();
		
		$parameters = array();
		if ($_REQUEST['status']) $parameters['status'] = $_REQUEST['status'];
		
		$countries = $countryList->find($parameters);
		if ($countryList->error) app_error("Error finding countries: ".$countryList->error());

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->country = $countries;

		print formatOutput($response);
	}
	###################################################
	### Add a Province or State						###
	###################################################
	function addProvince() {
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

		print formatOutput($response);
	}

	###################################################
	### Update a Province							###
	###################################################
	function updateProvince() {
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

		print formatOutput($response);
	}

	###################################################
	### Get Specified Province						###
	###################################################
	function getProvince() {
		$country = new \Geography\Country($_REQUEST['country_id']);
		if (! $country->id) app_error("Country not found");

		$province = new \Geography\Province();
		if (! $province->get($country->id,$_REQUEST['name']));
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->province = $province;

		print formatOutput($response);
	}

	###################################################
	### Find matching Provinces						###
	###################################################
	function findProvinces() {
		$provinceList = new \Geography\ProvinceList();
		
		$parameters = array();
		if ($_REQUEST['status']) $parameters['status'] = $_REQUEST['status'];
		if ($_REQUEST['country']) {
			$country = new \Geography\Country();
			if (! $country->get($_REQUEST['country'])) app_error("Country not found");
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

		print formatOutput($response);
	}

	###################################################
	### Manage Support Schema						###
	###################################################
	function schemaVersion() {
		$schema = new \Geography\Schema();
		if ($schema->error) {
			app_error("Error getting version: ".$schema->error,__FILE__,__LINE__);
		}
		$version = $schema->version();
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->version = $version;
		print formatOutput($response);
	}
	function schemaUpgrade() {
		$schema = new \Geography\Schema();
		if ($schema->error) {
			app_error("Error getting version: ".$schema->error,__FILE__,__LINE__);
		}
		$version = $schema->upgrade();
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->version = $version;
		print formatOutput($response);
	}

	###################################################
	### System Time									###
	###################################################
	function system_time() {
		return date("Y-m-d H:i:s");
	}
	###################################################
	### Return Properly Formatted Error Message		###
	###################################################
	function error($message) {
		$_REQUEST["stylesheet"] = '';
		error_log($message);
		$response = new \HTTP\Response();
		$response->message = $message;
		$response->success = 0;
		print formatOutput($response);
		exit;
	}
	###################################################
	### Application Error							###
	###################################################
	function app_error($message,$file = __FILE__,$line = __LINE__) {
		app_log($message,'error',$file,$line);
		error('Application Error');
	}
	###################################################
	### Convert Object to XML						###
	###################################################
	function formatOutput($object) {
		if (isset($_REQUEST['_format']) && $_REQUEST['_format'] == 'json') {
			$format = 'json';
			header('Content-Type: application/json');
		}
		else {
			$format = 'xml';
			header('Content-Type: application/xml');
		}
		$document = new \Document($format);
		$document->prepare($object);
		return $document->content();
	}
