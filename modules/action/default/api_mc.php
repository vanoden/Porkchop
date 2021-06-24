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
	### Add Action Request							###
	###################################################
	function addActionRequest() {
		if (! preg_match('/^[\w\-\.\_\s]+$/',$_REQUEST['person_code'])) error("person_code required for addActionRequest method");

		$person = new \Register\Customer();
		if ($person->error) error("Error initializing person: ".$person->error);
		$person->get($_REQUEST['person_code']);
		if ($person->error) error("Error finding person: ".$person->error);
		if (! $person->id) error("Person '".$_REQUEST['person_code']."' not found");

		$request = new \Action\Request();
		$request->add(
			array(
				'user_requested' => $person->id,
				'description'	=> $_REQUEST['description']
			)
		);
		if ($request->error) error("Error adding action request: ".$request->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->request = $request;

		api_log($response);
		print formatOutput($response);
	}
	###################################################
	### Add ActionTaskType Request					###
	###################################################
	function addActionTaskType() {
		$type = new \Action\Task\Type();
		$type->add(
			array(
				'code' => $_REQUEST['code'],
			)
		);
		if ($type->error) error("Error adding action task type: ".$type->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->task = $task;

		api_log($response);
		print formatOutput($response);
	}
	###################################################
	### Find Action Requests						###
	###################################################
	function findActionRequests() {
		if ($_REQUEST['user_requested']) {
			$customer = new \Register\Customer();
			if ($customer->error) error("Error initializing person: ".$customer->error);
			$customer->get($_REQUEST['person_code']);
			if ($customer->error) error("Error finding customer: ".$customer->error);
			if (! $customer->id) error("Customer '".$_REQUEST['person_code']."' not found");
		}

		$requestlist = new \Action\RequestList();
		$requests = $requestlist->find($parameters);
		if ($requestlist->error) error("Error finding action requests: ".$requestlist->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->request = $requests;

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Manage Action Schema						###
	###################################################
	function schemaVersion() {
		$schema = new \Action\Schema();
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
		$schema = new \Action\Schema();
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
		$response->error = $message;
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
