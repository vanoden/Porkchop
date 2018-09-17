<?php
    ###############################################
    ### Handle API Request for Product Info and	###
    ### Management								###
    ### A. Caravello 5/7/2009               	###
    ###############################################
	$_package = array(
		"name"		=> "session",
		"version"	=> "0.2.0",
		"release"	=> "2018-05-02"
	);

	app_log($_REQUEST['action']." request:".print_r($_REQUEST,true),'debug',__FILE__,__LINE__);

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
	### Get session info by session code			###
	###################################################
	function getSession() {
		$session = new \Site\Session();
		$session->get($_REQUEST['code']);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->session = $session;
        api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Get session info by session code			###
	###################################################
	function getSessionHits() {
		$session = new \Site\Session();
		$session->get($_REQUEST['code']);
		$hits = $session->hits();
		$response->success = 1;
		$response->hit = $hits;
        api_log($response);
		print formatOutput($response);
	}

	function timelocal() {
		if (isset($_REQUEST['code'])) {
			$session = new \Site\Session();
			$session->get($_REQUEST['code']);
		}
		else {
			$session = $GLOBALS['_SESSION_'];
		}
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->datetime = $session->localtime();
		print formatOutput($response);
	}

	###################################################
	### Manage Session Schema						###
	###################################################
	function schemaVersion() {
		$schema = new \Site\Schema();
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
		$schema = new \Site\Schema();
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
		app_log($message,'error',__FILE__,__LINE__);
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
?>
