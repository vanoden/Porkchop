<?php
    ###############################################
    ### Handle API Request for Customer Info 	###
    ### and Management							###
    ### A. Caravello 5/7/2009               	###
    ###############################################
	$_package = array(
		"name"		=> "builtin",
		"version"	=> "0.1.1",
		"release"	=> "2016-12-15",
	);

	# Call Requested Event
	//error_log($_REQUEST['action']." Request received from ".$_REQUEST['hub_code']);
	if ($_REQUEST["method"]) {
		# Call the Specified Method
		$function_name = $_REQUEST["method"];
		$function_name($_package);
		exit;
	}
    # Only Developers Can See The API
	elseif (! in_array('register manager',$GLOBALS['_SESSION_']->customer->roles)) {
        header("location: /_register/login");
        exit;
    }

	###################################################
	### Just See if Server Is Communicating			###
	###################################################
	function ping($_package) {
		$response = new stdClass();
		$response->message = "PING RESPONSE";
		$response->schema_version = $_package["schema"];
		$response->package_version = $_package["version"];
		$response->release_date = $_package["release"];
		$response->success = 1;
		header('Content-Type: application/xml');
		print XMLout($response);
	}
	
	###################################################
	### Get Schema Information						###
	###################################################
	function schemaVersion($_package) {
		$schema = new BuiltInSchema();
		$response = new stdClass();
		$response->version = $schema->version();
		$response->success = 1;
		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Get Details regarding Specified Location	###
	###################################################
	//function getLocation() {
	//	# Default StyleSheet
	//	if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'register.customer.xsl';
	//
	//	# Initiate Product Object
	//	$_customer = new RegisterCustomer();
	//	
	//	if ($_REQUEST["login"] and (! $_REQUEST{"code"})) $_REQUEST['code'] = $_REQUEST['login'];
	//	$customer = $_customer->get($_REQUEST["code"]);
	//
	//	# Error Handling
	//	if ($_customer->error) error($_customer->error);
	//	else{
	//		$response = new stdClass();
	//		$response->customer = $customer;
	//		$response->success = 1;
	//	}
	//
	//	# Send Response
	//	header('Content-Type: application/xml');
	//	print XMLout($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
	//}
	//###################################################
	//### Update Specified Customer					###
	//###################################################
	//function updateLocation() {
	//	# Default StyleSheet
	//	if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'register.customer.xsl';
	//
	//	# Initiate Product Object
	//	$_customer = new RegisterCustomer();
	//
	//	# Find Customer
	//	$customer = $_customer->get($_REQUEST['code']);
	//	if ($_customer->error) app_error("Error getting customer: ".$_customer->error,__FILE__,__LINE__);
	//	if (! $customer->id) error("Customer not found");
	//
	//	if ($_REQUEST['organization'])
	//	{
	//		$_organization = new RegisterOrganization();
	//		$organization = $_organization->get($_REQUEST['organization']);
	//		if ($_organization->error) app_error("Error getting organization: ".$_organization->error,__FILE__,__LINE__);
	//		if (! $organization->id) error("Organization not found");
	//		$parameters['organization_id'] = $organization->id;
	//	}
	//	
	//	if ($_REQUEST['first_name']) $parameters['first_name'] = $_REQUEST['first_name'];
	//	if ($_REQUEST['last_name']) $parameters['last_name'] = $_REQUEST['last_name'];
	//	if ($_REQUEST['password']) $parameters['password'] = $_REQUEST['password'];
	//
	//	# Update Customer
	//	$customer = $_customer->update(
	//		$customer->id,
	//		$parameters
	//	);
	//
	//	# Error Handling
	//	if ($_customer->error) app_error("Error updating customer: ".$_customer->error,__FILE__,__LINE__);
	//	$response = new stdClass();
	//	$response->customer = $customer;
	//	$response->success = 1;
	//
	//	# Send Response
	//	header('Content-Type: application/xml');
	//	print XMLout($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
	//}
	//
	//###################################################
	//### Find Customers								###
	//###################################################
	//function findLocations() {
	//	# Default StyleSheet
	//	if (! isset($_REQUEST["stylesheet"])) $_REQUEST["stylesheet"] = 'register.customers.xsl';
	//
	//	# Initiate Image Object
	//	$_customer = new RegisterCustomer();
	//
	//	# Build Query Parameters
	//	$parameters = array();
	//	if ($_REQUEST["code"]) $parameters["code"] = $_REQUEST["code"];
	//	elseif (isset($_REQUEST["login"])) $parameters["code"] = $_REQUEST["login"];
	//	if ($_REQUEST["first_name"]) $parameters["first_name"] = $_REQUEST["first_name"];
	//	if ($_REQUEST["last_name"]) $parameters["last_name"] = $_REQUEST["last_name"];
	//	if (isset($_REQUEST["active"])) $parameters["active"] = $_REQUEST["active"];
	//
	//	if ($_REQUEST["organization"]) {
	//		$_organization = new RegisterOrganization();
	//		$organization = $_organization->get($_REQUEST["organization"]);
	//		if ($_organization->error) app_error("Error finding organization: ".$_organization->error,'error',__FILE__,__LINE__);
	//		if (! $organization->id) error("Could not find organization");
	//		$parameters['organization_id'] = $organization->id;
	//	}
	//
	//	# Get List of Matching Customers
	//	$customers = $_customer->find($parameters);
	//
	//	# Error Handling
	//	if ($_customer->error) error($_customer->error);
	//
	//	$response = new stdClass();
	//	$response->success = 1;
	//	$response->size = count($customers);
	//	$response->customer = $customers;
	//
	//	# Send Response
	//	header('Content-Type: application/xml');
	//	print XMLout($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
	//}

	###################################################
	### System Time									###
	###################################################
	function system_time() {
		return date("Y-m-d H:i:s");
	}
	###################################################
	### Application Error							###
	###################################################
	function app_error($message,$file = __FILE__,$line = __LINE__) {
		app_log($message,'error',$file,$line);
		error('Application Error');
	}
	###################################################
	### Return Properly Formatted Error Message		###
	###################################################
	function error($message) {
		$_REQUEST["stylesheet"] = '';
		error_log($message);
		$response->message = $message;
		$response->success = 0;
		header('Content-Type: application/xml');
		print XMLout($response,array("stylesheet" => $_REQUEST["stylesheet"]));
		exit;
	}
	###################################################
	### Convert Object to XML						###
	###################################################
	function XMLout($object,$user_options = array()) {
		require 'XML/Unserializer.php';
    	require 'XML/Serializer.php';
    	$options = array(
    	    XML_SERIALIZER_OPTION_INDENT        => '    ',
    	    XML_SERIALIZER_OPTION_RETURN_RESULT => true,
			XML_SERIALIZER_OPTION_MODE			=> 'simplexml',
    	);
		if (array_key_exists("rootname",$user_options))
		{
			$options["rootName"] = $user_options["rootname"];
		}
    	$xml = &new XML_Serializer($options);
	   	if ($xml->serialize($object))
		{
			//error_log("Returning ".$xml->getSerializedData());
			$output = $xml->getSerializedData();
			if (array_key_exists("stylesheet",$user_options))
			{
				$output = "<?xml-stylesheet type=\"text/xsl\" href=\"/".$user_options["stylesheet"]."\"?>".$output;
			}
			return $output;
		}
	}
?>
