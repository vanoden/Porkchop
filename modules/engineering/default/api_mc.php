<?php
    ###############################################
    ### Handle API Request for the Engineering	###
    ### Module									###
    ### A. Caravello 8/22/2018               	###
    ###############################################
	$_package = array(
		"name"		=> "engineering",
		"version"	=> "0.1.0",
		"release"	=> "2018-08-22"
	);

	app_log("Request: ".print_r($_REQUEST,true),'debug',__FILE__,__LINE__);

	###############################################
	### Load API Objects						###
    ###############################################
	# Call Requested Event
	if (isset($_REQUEST["method"])) {
		$message = "Method ".$_REQUEST['method']." called by user ".$GLOBALS['_SESSION_']->customer->code;
		app_log($message,'debug',__FILE__,__LINE__);

		# Call the Specified Method
		$function_name = $_REQUEST["method"];
		$function_name();
		exit;
	}
	# Only Developers Can See The API
	elseif (! $GLOBALS['_SESSION_']->customer->has_role('engineering admin')) {
		header("location: /_engineering/home");
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

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Add a Product								###
	###################################################
	function addProduct() {
		$product = new \Engineering\Product();
		if ($product->error()) error("Error adding product: ".$product->error());
		$product->add(
			array(
				'title'			=> $_REQUEST['title'],
				'description'	=> $_REQUEST['description'],
			)
		);
		if ($product->error()) error("Error adding product: ".$product->error());
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->product = $product;

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Update a Product							###
	###################################################
	function updateProduct() {
		$response = new \HTTP\Response();

		if (isset($_REQUEST['code'])) {
			$product = new \Engineering\Product();
			if ($product->get($_REQUEST['code'])) {
				$parameters = array();
				if ($_REQUEST['description']) {
					$parameters['description'] = $_REQUEST['description'];
				}
				if ($_REQUEST['title']) {
					$parameters['title'] = $_REQUEST['title'];
				}
				if ($product->update($parameters)) {
					$response->success = 1;
					$response->product = $product;
				}
				else {
					$response->success = 0;
					$response->error = $product->error();
				}
			}
			else {
				$response->success = 0;
				$response->error = $product->error();
			}
		}
		else {
			$response->success = 0;
			$response->error = "Product code required";
		}

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Find Products								###
	###################################################
	function findProducts() {
		$productList = new \Engineering\ProductList();
		$products = $productList->find();
		
		if ($productList->error()) error("Error finding products: ".$productList->error());

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->product = $products;

		api_log($response);
		print formatOutput($response);
	}

	function getProduct() {
		$response = new \HTTP\Response();

		$product = new \Engineering\Product();
		if ($product->get($_REQUEST['code'])) {
			$response->success = 1;
			$response->product = $product;
		}
		elseif($product->error()) {
			$response->success = 0;
			$response->error = $product->error();
		}
		else {
			$response->success = 1;
		}

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Add a Task									###
	###################################################
	function addTask() {
		$product = new \Engineering\Product();
		$product->get($_REQUEST['product_code']);
		if ($product->error()) error("Error finding product: ".$product->error());
		if (! $product->id) error("No product found matching '".$_REQUEST['product_code']."'");

		if (isset($_REQUEST['user_code']) && $_REQUEST['user_code'] && $_REQUEST['user_code'] != $GLOBALS['_SESSION_']->customer->code) {
			if ($GLOBALS['_SESSION_']->customer->has_role('engineering admin')) {
				$reporter = new \Register\Customer();
				$reporter->get($_REQUEST['user_code']);
				if ($reporter->error) error("Error finding reporter: ".$reporter->error);
				if (! $reporter->id) error("No user found matching '".$_REQUEST['reporter_login']."'");
			}
			else {
				error("Permission denied");
			}
		}
		else {
			$reporter = new \Register\Customer($GLOBALS['_SESSION_']->customer->id);
		}

		if (isset($_REQUEST['internal']) && $_REQUEST['internal'] == 1) {
			$internal = true;
		}
		else {
			$internal = false;
		}
		$task = new \Engineering\Task();
		if ($task->error()) error("Error adding task: ".$task->error());
		$issue->add(
			array(
				'title'				=> $_REQUEST['title'],
				'status'			=> $_REQUEST['status'],
				'product_id'		=> $product->id,
				'requested_id'		=> $reporter->id,
				'internal'			=> $internal,
				'description'		=> $_REQUEST['description']
			)
		);
		if ($task->error()) error("Error adding task: ".$task->error());
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->task = $task;

		api_log($response);
		print formatOutput($response);
	}


	###################################################
	### Find Tasks									###
	###################################################
	function findTasks() {
		$taskList = new \Engineering\TaskList();
		$tasks = $taskList->find();
		
		if ($taskList->error()) error("Error finding tasks: ".$taskList->error());

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->task = $tasks;

		api_log($response);
		print formatOutput($response);
	}
	###################################################
	### Run Upgrades and Return DB Schema Version	###
	###################################################
	function schemaVersion() {
		$schema = new \Engineering\Schema();
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
		$schema = new \Engineering\Schema();
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
		$response = new \HTTP\Response();
		$response->error = $message;
		$response->success = 0;
		api_log($response);
		print formatOutput($response);
		exit;
	}
	###################################################
	### Return Properly Formatted Message			###
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
