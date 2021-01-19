<?php
    ###############################################
    ### Handle API Request for alerts			###
    ### Kevin Hinds  1/19/2021               	###
    ###############################################
	$api = (object) array(
		"name"		=> "alerts",
		"version"	=> "0.0.1",
		"release"	=> "2021-01-19"
	);

	app_log("Request: ".print_r($_REQUEST,true),'debug',__FILE__,__LINE__);

	function schemaVersion() {
		$schema = new \Alert\Schema();
		if ($schema->error) app_error("Error getting version: ".$schema->error,__FILE__,__LINE__);
		$version = $schema->version();
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->version = $version;
		print formatOutput($response);
	}
	
	function schemaUpdate() {
		$schema = new \Alert\Schema();
		if ($schema->error) app_error("Error getting version: ".$schema->error,__FILE__,__LINE__);
		$version = $schema->upgrade();
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->version = $version;
		print formatOutput($response);
	}

	// Call Requested Event
	if (isset($_REQUEST["method"])) {
		$message = "Method ".$_REQUEST['method']." called by user ".$GLOBALS['_SESSION_']->customer->code;
		if (array_key_exists('asset_code',$_REQUEST)) $message .= " for asset ".$_REQUEST['asset_code'];
		app_log($message,'debug',__FILE__,__LINE__);

		// Call the Specified Method
		$function_name = $_REQUEST["method"];
		$function_name($api);
		exit;  
	} elseif (! $GLOBALS['_SESSION_']->customer->has_role('monitor admin')) {
    	// Only Developers Can See The API
		header("location: /_monitor/home");
		exit;
	}

	/**
	 * Just See if Server Is Communicating
	 */
	function ping($api) {
		$response = new \HTTP\Response();
		$response->header->session = $GLOBALS['_SESSION_']->code;
		$response->header->method = $_REQUEST["method"];
		$response->header->date = system_time();
		$response->message = "PING RESPONSE";
		$response->api = $api;
		$response->success = 1;

		api_log($response);
		print formatOutput($response);
	}

	/**
	 * System Time
	 */
	function system_time() {
		return date("Y-m-d H:i:s");
	}
	
	/**
	 * Application Error
	 */
	function app_error($message,$file = __FILE__,$line = __LINE__) {
		app_log($message,'error',$file,$line);
		error('Application Error');
	}
	
	/**
	 * Return Properly Formatted Error Message
	 */
	function error($message) {
		$_REQUEST["stylesheet"] = '';
		$response = new \HTTP\Response();
		$response->error = $message;
		$response->success = 0;
		$_comm = new \Alert\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		print formatOutput($response);
		exit;
	}

	function formatOutput($object) {
		if (isset($_REQUEST['_format']) && $_REQUEST['_format'] == 'json') {
			$format = 'json';
			header('Content-Type: application/json');
		} else {
			$format = 'xml';
			header('Content-Type: application/xml');
		}
		$document = new \Document($format);
		$document->prepare($object);
		return $document->content();
	}
