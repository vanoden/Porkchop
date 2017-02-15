<?php
    ###############################################
    ### Handle API Request for Customer Info 	###
    ### and Management							###
    ### A. Caravello 5/7/2009               	###
    ###############################################
	$_package = array(
		"name"		=> "event",
		"version"	=> "0.1.1",
		"release"	=> "2016-12-05",
	);

	require_module("event");
	# Call Requested Event
	//error_log($_REQUEST['action']." Request received from ".$_REQUEST['hub_code']);
	if ($_REQUEST["method"]) {
		# Call the Specified Method
		$function_name = $_REQUEST["method"];
		$function_name($_package);
		exit;
	}

	###################################################
	### Just See if Server Is Communicating			###
	###################################################
	function ping($_package) {
		$response = new stdClass();
		$response->message = "PING RESPONSE";
		$response->package_version = $_package["version"];
		$response->release_date = $_package["release"];
		$response->success = 1;
		header('Content-Type: application/xml');
		print XMLout($response);
	}
	###################################################
	### Add an Action Event							###
	###################################################
	function addEventItem() {
		# Record Event
		$event = new EventItem();
		$event->add(
			"MonitorAsset",
			[	"code"  => uniqid(),
				"timestamp" => date("c"),
				"user"  => $GLOBALS['_SESSION_']->customer->code,
				"description"   => "Test Event Created",
			]
		);
		if ($event->error) {
			app_log("Failed to add change to history: ".$event->error,'error',__FILE__,__LINE__);
		}

		$response = new stdClass();
		$response->success = 1;
		$response->role = $result;

		header('Content-Type: application/xml');
		print XMLout($response);
	}
	
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
