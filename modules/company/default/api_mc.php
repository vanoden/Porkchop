<?php
    ###############################################
    ### Handle API Request for Company Info and	###
    ### Management								###
    ### A. Caravello 5/7/2009               	###
    ###############################################
	$_package = array(
		"name"		=> "customer",
		"version"	=> "0.1.0",
		"release"	=> "2018-03-13"
	);

	app_log("Request: ".print_r($_REQUEST,true),'debug',__FILE__,__LINE__);

	# Call Requested Event
	if ($_REQUEST["method"]) {
		# Call the Specified Method
		$function_name = $_REQUEST["method"];
		$function_name();
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
	### Get Details regarding Specified Company		###
	###################################################
	function getCompany() {
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'person.customer.xsl';

		# Initiate Company List
		$companylist = new \CompanyList();
		
		list($company) = $companylist->find();

		# Error Handling
		if ($companylist->error) error($companylist->error);
		else{
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->customer = $company;
		}

		# Send Response
		print formatOutput($response);
	}

	###################################################
	### Update Company 								###
	###################################################
	function updateCompany() {
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'company.xsl';

		# Initiate Company Object
		$companylist = new \CompanyList();
		list($company) = $companylist->find();

		# Update Company
		$company->update(
			array(
				"name"			=> $_REQUEST["name"],
				"status"		=> $_REQUEST["category"]
			)
		);

		# Error Handling
		if ($company->error) error($company->error);
		else{
			$response = new \HTTP\Response();
			$response->company = $company;
			$response->success = 1;
		}

		# Send Response
		print formatOutput($response);
	}
	# Manage Company Schema
	function schemaVersion() {
		$schema = new \Company\Schema();
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
		$schema = new \Company\Schema();
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
	### Return Properly Formatted Error Message		###
	###################################################
	function error($message) {
		$_REQUEST["stylesheet"] = '';
		error_log($message);
		$response = new \HTTP\Response();
		$response->error = $message;
		$response->success = 0;
		api_log($response);
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
?>
