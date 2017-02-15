<?php
    ###############################################
    ### Handle API Request for product			###
    ### communications							###
    ### A. Caravello 8/12/2013               	###
    ###############################################

	app_log('Request: '.print_r($_REQUEST,true),'debug');

	###############################################
	### Load API Objects						###
    ###############################################
	# Support Module
	require_once(MODULES.'/support/_classes/default.php');
	$_init = new SupportInit();
	if ($_init->error)
		app_error("Error initializing Support Module: ".$_init->error,'error',__FILE__,__LINE__);

	# Default Response Values
	$response->success = 0;
	$response->method = $_REQUEST["method"];

	# Call Requested Event
	if ($_REQUEST["method"])
	{
		error_log("Method ".$_REQUEST['method']." called by ".$_customer->code);
		# Call the Specified Method
		$function_name = $_REQUEST["method"];
		$function_name();
		exit;
	}
	# Only Developers Can See The API
	elseif (role('support manager'))
	{
		header("location: /_support/home");
		exit;
	}

	###################################################
	### Just See if Server Is Communicating			###
	###################################################
	function ping()
	{
		$response->header->session = $GLOBALS['_SESSION_']->code;
		$response->header->method = $_REQUEST["method"];
		$response->header->date = system_time();
		$response->message = "PING RESPONSE";
		$response->success = 1;
		header('Content-Type: application/xml');
		print XMLout($response);
	}
	###################################################
	### Add a Request								###
	###################################################
	function addRequest()
	{
		$_request = new SupportRequest();

		$parameters = array();
		if (permitted('support manager'))
		{
			if ($_REQUEST['customer'])
			{
				$_customer = new RegisterCustomer();
				$customer = $_customer->get($_REQUEST['customer']);
				if ($_customer->error) app_error("Error getting customer: ".$_customer->error,'error',__FILE__,__LINE__);
				if (! $customer->id) error("Customer not found");
				$parameters['customer_id'] = $customer->id;
			}
			if ($_REQUEST['tech'])
			{
				$_admin = new RegisterAdmin();
				$admin = $_admin->get($_REQUEST['admin']);
				if ($_admin->error) app_error("Error getting admin: ".$_admin->error,'error',__FILE__,__LINE__);
				if (! $admin->id) error("Tech not found");
				$parameters['tech_id'] = $admin->id;
			}
			if ($_REQUEST['status'])
			{
				$parameters['status'] = $_REQUEST['status'];
			}
		}
		
		$request = $_request->add($parameters);
		if ($_request->error) app_error("Error adding request: ".$_request->error);
		$response->success = 1;
		$response->request = $request;

		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Update a Request							###
	###################################################
	function updateRequest()
	{
		$_request = new SupportRequest();
		$request = $_request->get($_REQUEST['code']);
		if ($_request->error) app_error("Error finding request: ".$_request->error,'error',__FILE__,__LINE__);
		if (! $request->id) error("Request not found");

		$request = $_request->update(
			$request->id,
			array(
				'name'			=> $_REQUEST['name'],
				'type'			=> $_REQUEST['type'],
				'status'		=> $_REQUEST['status'],
				'description'	=> $_REQUEST['description'],
			)
		);
		if ($_request->error) app_error("Error adding product: ".$_request->error,'error',__FILE__,__LINE__);
		$response->success = 1;
		$response->request = $request;

		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Get Specified Request						###
	###################################################
	function getRequest()
	{
		$_request = new SupportRequest();
		$request = $_request->get($_REQUEST['code']);

		if ($_request->error) error("Error getting request: ".$_request->error);
		$response->success = 1;
		$response->request = $request;

		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Find matching Requests						###
	###################################################
	function findRequests()
	{
		$_request = new SupportRequest();
		
		$parameters = array();
		if ($_REQUEST['status']) $parameters['status'] = $_REQUEST['status'];
		
		$requests = $_request->find($parameters);
		if ($_request->error) app_error("Error finding requests: ".$_request->error);

		$response->success = 1;
		$response->request = $requests;

		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### System Time									###
	###################################################
	function system_time()
	{
		return date("Y-m-d H:i:s");
	}
	###################################################
	### Application Error							###
	###################################################
	function app_error($message,$file = __FILE__,$line = __LINE__)
	{
		app_log($message,'error',$file,$line);
		error('Application Error');
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
	function XMLout($object,$user_options='')
	{
		if (0)
		{
			$fp = fopen('/var/log/api/monitor.log', 'a');
			fwrite($fp,"#### RESPONSE ####\n");
			fwrite($fp, print_r($object,true));
			fclose($fp);
		}

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
