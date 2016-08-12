<?php
    ###############################################
    ### Handle API Request for Company Info and	###
    ### Management								###
    ### A. Caravello 5/7/2009               	###
    ###############################################

	error_log($_REQUEST['action']." request:".print_r($_REQUEST,true));

	# Initialize Config Variable
    $_config = array();

	###############################################
	### Load API Objects						###
    ###############################################
    # Global Config
    require_once '/home/php_api/config.php';
    # Session Classes
	require_once '/home/php_api/classes/session.php';
	# Database Abstraction
	require_once '/home/3rd_party_inc/adodb/adodb.inc.php';
	# Page Handling
    require_once '/home/php_api/classes/page.php';
	# Person (Visitor/Customer/Admin)
    require_once '/home/php_api/classes/contact.php';

	###############################################
	### Initialize Common Objects				###
	###############################################
    # Load Configs
    $_config = new Config;

    # Connect to Database
    $_database = NewADOConnection('mysqli');
	$_database->Connect(
		$GLOBALS['_config']->database['hostname'],
		$GLOBALS['_config']->database['username'],
		$GLOBALS['_config']->database['password'],
		$GLOBALS['_config']->database['schema']
	);

    # Create Session
	#error_log("Session: ".$_REQUEST['session_code']);
    #$_SESSION_ = new Session($_REQUEST['session_code']);

    # Load Page Information
    $_page = new Page;
    if ($_page->error)
    {
        $response->success = 0;
        $response->message = $_page->error;
        header("Content-Type: application/xml");
        print_r($xml->serialize($response));
        exit;
    }

    #$_customer = new Customer($_SESSION_->customer);

    # Initiate Response
	# We'll be returning an XML formatted Message
    $response->message = "Message Received";
	$response->success = 1;
    $response->header->session = $_session->code;
	$response->header->method = $_REQUEST["method"];

	# Call Requested Event
	//error_log($_REQUEST['action']." Request received from ".$_REQUEST['hub_code']);
	if ($_REQUEST["method"])
	{
		# Call the Specified Method
		$function_name = $_REQUEST["method"];
		$function_name();
		exit;
	}

	###################################################
	### Just See if Server Is Communicating			###
	###################################################
	function ping()
	{
		$response->message = "PING RESPONSE";
		$response->success = 1;
		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Get Details regarding Specified Company		###
	###################################################
	function getCompany()
	{
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'person.customer.xsl';

		# Initiate Product Object
		$_customer = new Customer();
		
		list($customer) = $_customer->find(array("code" => $_REQUEST["code"]));

		# Error Handling
		if ($_customer->error) error($_customer->error);
		else{
			$response->customer = $_customer->details($customer);
			$response->success = 1;
		}

		# Send Response
		header('Content-Type: application/xml');
		print XMLout($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
	}

	###################################################
	### Update Company 								###
	###################################################
	function updateCompany()
	{
		# Initiate Response
		$response->header->session = $GLOBALS['_session']->code;
		$response->header->method = $_REQUEST["method"];

		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'company.xsl';

		# Initiate Company Object
		$_company = new Company();

		# Update Company
		$_company->update(
			array(
				"name"			=> $_REQUEST["name"],
				"status"		=> $_REQUEST["category"]
			)
		);

		# Error Handling
		if ($_company->error) error($_company->error);
		else{
			$response->company = $_company->details();
			$response->success = 1;
		}

		# Send Response
		header('Content-Type: application/xml');
		print XMLout($response,array("stylesheet" => $_REQUEST["stylesheet"]));
	}

	###################################################
	### Return Properly Formatted Error Message		###
	###################################################
	function error($message)
	{
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
	function XMLout($object,$user_options = '')
	{
		require 'XML/Unserializer.php';
    	require 'XML/Serializer.php';
    	$options = array(
    	    XML_SERIALIZER_OPTION_INDENT        => '    ',
    	    XML_SERIALIZER_OPTION_RETURN_RESULT => true,
			XML_SERIALIZER_OPTION_MODE			=> 'simplexml',
    	);
		if ($user_options["rootname"])
		{
			$options["rootName"] = $user_options["rootname"];
		}
    	$xml = &new XML_Serializer($options);
	   	if ($xml->serialize($object))
		{
			//error_log("Returning ".$xml->getSerializedData());
			$output = $xml->getSerializedData();
			if ($user_options["stylesheet"])
			{
				$output = "<?xml-stylesheet type=\"text/xsl\" href=\"/".$user_options["stylesheet"]."\"?>".$output;
			}
			return $output;
		}
	}
?>
