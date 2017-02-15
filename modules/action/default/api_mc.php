<?php
    ###############################################
    ### Handle API Request for Action Info and	###
    ### Management								###
    ### A. Caravello 5/7/2009               	###
    ###############################################
	$_package = array(
		"name"		=> "action",
		"version"	=> "0.0.1",
		"release"	=> "2015-08-18"
	);

	app_log($_REQUEST['method']." request:".print_r($_REQUEST,true),'debug',__FILE__,__LINE__);

	require_module('action');

	# Call Requested Event
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
	function ping() {
		$response->message = "PING RESPONSE";
		$response->success = 1;
		header('Content-Type: application/xml');
		print XMLout($response);
	}
	###################################################
	### Add Action Request							###
	###################################################
	function addActionRequest() {
		if (! preg_match('/^[\w\-\.\_\s]+$/',$_REQUEST['person_code'])) error("person_code required for addActionRequest method");

		$_person = new RegisterCustomer();
		if ($_person->error) error("Error initializing person: ".$_person->error);
		$person = $_person->get($_REQUEST['person_code']);
		if ($_person->error) error("Error finding person: ".$_person->error);
		if (! $person->id) error("Person '".$_REQUEST['person_code']."' not found");

		$_request = new ActionRequest();
		$request = $_request->add(
			array(
				'user_requested' => $person->id,
				'description'	=> $_REQUEST['description']
			)
		);
		if ($_request->error) error("Error adding action request: ".$_request->error);
		$response = new stdClass();
		$response->success = 1;
		$response->request = $request;

		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response);
	}
	###################################################
	### Add ActionTaskType Request					###
	###################################################
	function addActionTaskType() {
		$type = new ActionTaskType();
		$type->add(
			array(
				'code' => $_REQUEST['code'],
			)
		);
		if ($type->error) error("Error adding action task type: ".$type->error);
		$response = new stdClass();
		$response->success = 1;
		$response->task = $task;

		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response);
	}
	###################################################
	### Find Action Requests						###
	###################################################
	function findActionRequests() {
		if ($_REQUEST['user_requested']) {
			$_person = new RegisterCustomer();
			if ($_person->error) error("Error initializing person: ".$_person->error);
			$person = $_person->get($_REQUEST['person_code']);
			if ($_person->error) error("Error finding person: ".$_person->error);
			if (! $person->id) error("PErson '".$_REQUEST['person_code']."' not found");
		}

		$_request = new ActionRequests();
		$requests = $_request->find($parameters);
		if ($_request->error) error("Error finding action requests: ".$_request->error);
		$response = new stdClass();
		$response->success = 1;
		$response->request = $requests;

		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response);
	}
	###################################################
	### Return Properly Formatted Error Message		###
	###################################################
	function error($message) {
		$_REQUEST["stylesheet"] = '';
		app_log($message,'error',__FILE__,__LINE__);
		$response->message = $message;
		$response->success = 0;
		header('Content-Type: application/xml');
		print XMLout($response,array("stylesheet" => $_REQUEST["stylesheet"]));
		exit;
	}
	###################################################
	### Convert Object to XML						###
	###################################################
	function XMLout($object,$user_options = '') {
		require 'XML/Unserializer.php';
    	require 'XML/Serializer.php';
    	$options = array(
    	    XML_SERIALIZER_OPTION_INDENT        => '    ',
    	    XML_SERIALIZER_OPTION_RETURN_RESULT => true,
			XML_SERIALIZER_OPTION_MODE			=> 'simplexml',
    	);
		if ($user_options["rootname"]) {
			$options["rootName"] = $user_options["rootname"];
		}
    	$xml = &new XML_Serializer($options);
	   	if ($xml->serialize($object)) {
			//error_log("Returning ".$xml->getSerializedData());
			$output = $xml->getSerializedData();
			if ($user_options["stylesheet"]) {
				$output = "<?xml-stylesheet type=\"text/xsl\" href=\"/".$user_options["stylesheet"]."\"?>".$output;
			}
			return $output;
		}
	}
?>
