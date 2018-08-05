<?php
    ###############################################
    ### Handle API Request for monitor			###
    ### communications							###
    ### A. Caravello 5/7/2009               	###
    ###############################################
	$_package = array(
		"name"		=> "issue",
		"version"	=> "0.1.0",
		"release"	=> "2018-04-27"
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
	elseif (! $GLOBALS['_SESSION_']->customer->has_role('issue admin')) {
		header("location: /_issue/home");
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
		$owner = new \Register\Customer();
		$owner->get($_REQUEST['owner_code']);
		if ($owner->error) error("Error finding reporter: ".$owner->error);
		if (! $owner->id) error("No user found matching '".$_REQUEST['owner_code']."'");

		$product = new \Issue\Product();
		if ($product->error) error("Error adding product: ".$product->error);
		$product->add(
			array(
				'name'			=> $_REQUEST['name'],
				'owner_id'		=> $owner->id,
				'status'		=> $_REQUEST['status'],
				'description'	=> $_REQUEST['description']
			)
		);
		if ($product->error()) error("Error adding issue: ".$product->error());
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->product = $product;

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Find Products								###
	###################################################
	function findProducts() {
		$productList = new \Issue\ProductList();
		$products = $productList->find();
		
		if ($productList->error()) error("Error finding products: ".$productList->error());

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->product = $products;

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Add an Issue								###
	###################################################
	function addIssue() {
		$product = new \Issue\Product();
		$product->get($_REQUEST['product_code']);
		if ($product->error()) error("Error finding product: ".$product->error());
		if (! $product->id) error("No product found matching '".$_REQUEST['product_code']."'");

		if (isset($_REQUEST['user_code']) && $_REQUEST['user_code'] && $_REQUEST['user_code'] != $GLOBALS['_SESSION_']->customer->code) {
			if ($GLOBALS['_SESSION_']->customer->has_role('issue admin')) {
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
		$issue = new \Issue\Issue();
		if ($issue->error()) error("Error adding issue: ".$issue->error());
		$issue->add(
			array(
				'title'				=> $_REQUEST['title'],
				'status'			=> $_REQUEST['status'],
				'priority'			=> $_REQUEST['priority'],
				'product_id'		=> $product->id,
				'user_reported_id'	=> $reporter->id,
				'internal'			=> $internal,
				'description'		=> $_REQUEST['description']
			)
		);
		if ($issue->error()) error("Error adding issue: ".$issue->error());
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->issue = $issue;

		api_log($response);
		print formatOutput($response);
	}


	###################################################
	### Find Issues								###
	###################################################
	function findIssues() {
		$issueList = new \Issue\IssueList();
		$issues = $issueList->find();
		
		if ($issueList->error()) error("Error finding issue: ".$issueList->error());

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->issue = $issues;

		api_log($response);
		print formatOutput($response);
	}
	###################################################
	### Run Upgrades and Return DB Schema Version	###
	###################################################
	function schemaVersion() {
		$schema = new \Issue\Schema();
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
		$schema = new \Issue\Schema();
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
?>
