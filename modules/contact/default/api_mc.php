<?php
    ###############################################
    ### Handle API Request for Contact			###
    ### communications							###
    ### A. Caravello 12/15/2014               	###
    ###############################################

	#app_log("Server Vars: ".print_r($_SERVER,true),'debug');
	app_log("Request: ".print_r($_REQUEST,true),'debug');

	###############################################
	### Load API Objects						###
    ###############################################
	# Call Requested Event
	if ($_REQUEST["method"]) {
		error_log("Method ".$_REQUEST['method']." called by user ".$GLOBALS['_SESSION_']->customer->code." for asset ".$_REQUEST['asset_code']);
		# Call the Specified Method
		$function_name = $_REQUEST["method"];
		$function_name();
		exit;
	}
	# Only Developers Can See The API
	#elseif (! in_array('contact admin',$GLOBALS['_SESSION_']->customer->roles))
	#{
	#	header("location: /_monitor/home");
	#	exit;
	#}

	###################################################
	### Just See if Server Is Communicating			###
	###################################################
	function ping() {
		$response->header->session = $GLOBALS['_SESSION_']->code;
		$response->header->method = $_REQUEST["method"];
		$response->header->date = system_time();
		$response->message = "PING RESPONSE";
		$response->success = 1;
		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Add an Event								###
	###################################################
	function addEvent() {
		$event = new \Contact\Event();
		if ($event->error) app_error("Error initializing ContactEvent: ".$_event->error);
		
		$parameters = array();
		if ($_REQUEST['status']) $parameters['status'] = $_REQUEST['status'];
		else $_REQUEST['status'] = 'NEW';
		$_REQUEST['content'] = $_REQUEST['content'];

		$event->add($parameters);
		if ($event->error) error("Error adding event: ".$event->error);
		$response->success = 1;
		$response->event = $event;

		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Find matching Events						###
	###################################################
	function findEvents() {
		$eventlist = new \Contact\EventList();
		if ($eventlist->error) app_error("Error finding events: ".$eventlist->error,__FILE__,__LINE__);
		
		if (in_array($_REQUEST['status'],array('NEW','OPEN','CLOSED'))) $parameters['status'] = $_REQUEST['status'];
		elseif($_REQUEST['status']) error("Invalid status for events");
		
		$events = $eventlist->find($parameters);
		if ($eventlist->error) app_error("Error finding events: ".$eventlist->error,__FILE__,__LINE__);
		$response->success = 1;
		$response->event = $events;

		header('Content-Type: application/xml');
		print XMLout($response);
	}
	function schemaVersion() {
		$schema = new \Contact\Schema();
		if ($schema->error) {
			app_error("Error getting version: ".$schema->error,__FILE__,__LINE__);
		}
		$version = $schema->version();
		$response = new stdClass();
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
		$response->message = $message;
		$response->success = 0;
		header('Content-Type: application/xml');
		print XMLout($response,array("stylesheet" => $_REQUEST["stylesheet"]));
		exit;
	}
	###################################################
	### Convert Object to XML						###
	###################################################
	function XMLout($object,$user_options='') {
		if (0) {
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
		if ($user_options["rootname"]) {
			$options["rootName"] = $user_options["rootname"];
		}
    	$xml = new XML_Serializer($options);
	   	if ($xml->serialize($object)) {
			//error_log("Returning ".$xml->getSerializedData());
			$output = $xml->getSerializedData();
			if ($user_options["stylesheet"]) {
				$output = "<?xml-stylesheet type=\"text/xsl\" href=\"/".$user_options["stylesheet"]."\"?>".$output;
			}
			return $output;
		}
	}
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
