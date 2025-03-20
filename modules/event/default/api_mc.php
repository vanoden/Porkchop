<?php
    ###############################################
    ### Handle API Request for Event Info 		###
    ### and Management							###
    ### A. Caravello 5/7/2009               	###
    ###############################################
	$api = array(
		"name"		=> "event",
		"version"	=> "0.1.1",
		"release"	=> "2016-12-05",
	);
	
	$can_proceed = true;
	
	// Initialize object for validation
	$eventItem = new \Event\Item();

	app_log("Request: ".print_r($_REQUEST,true),'debug',__FILE__,__LINE__);

	# Call Requested Event
	$method = $_REQUEST["method"] ?? null;
	if (!empty($method)) {
		if (!$eventItem->validText($method)) {
			error("Invalid method format");
			$can_proceed = false;
		} elseif (!function_exists($method)) {
			error("Method not found: $method");
			$can_proceed = false;
		} else {
			# Call the Specified Method
			$function_name = $method;
			$function_name($api);
			exit;
		}
	}

	###################################################
	### Just See if Server Is Communicating			###
	###################################################
	function ping($api) {
		$response = new \HTTP\Response();
		$response->header->session = $GLOBALS['_SESSION_']->code;
		$response->header->method = $_REQUEST["method"] ?? 'ping';
		$response->header->date = system_time();
		$response->message = "PING RESPONSE";
		$response->api = $api;
		$response->success = 1;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		print formatOutput($response);
	}
	###################################################
	### Add an Action Event							###
	###################################################
	function addEventItem() {
		$can_proceed = true;
		$eventItem = new \Event\Item();
		
		$description = $_REQUEST["description"] ?? "Event Created";
		if (!$eventItem->validText($description)) {
			error("Invalid description format");
			$can_proceed = false;
		}
		
		if ($can_proceed) {
			# Record Event
			$event = new \Event\Item();
			$event->add(
				"MonitorAsset",
				[	"code"  => uniqid(),
					"timestamp" => date("c"),
					"user"  => $GLOBALS['_SESSION_']->customer->code,
					"description" => $description,
				]
			);
			if ($event->error) {
				app_log("Failed to add change to history: ".$event->error,'error',__FILE__,__LINE__);
				error("Failed to add event: ".$event->error);
				$can_proceed = false;
			}
			
			if ($can_proceed) {
				$response = new \HTTP\Response();
				$response->success = 1;
				$response->role = $result ?? null;

				header('Content-Type: application/xml');
				print XMLout($response);
			}
		}
	}

	###################################################
	### Manage Company Schema						###
	###################################################
	function schemaVersion() {
		$schema = new \Event\Schema();
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
		$schema = new \Event\Schema();
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
		$format = $_REQUEST['_format'] ?? 'xml';
		if ($format == 'json') {
			header('Content-Type: application/json');
		} else {
			$format = 'xml';
			header('Content-Type: application/xml');
		}
		$document = new \Document($format);
		$document->prepare($object);
		return $document->content();
	}
