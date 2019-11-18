<?php
    ###############################################
    ### Handle API Request for Build			###
    ### tracking.								###
    ### A. Caravello 11/18/2019               	###
    ###############################################

	###############################################
	### Load API Objects						###
    ###############################################
	$_package = array(
		"name"		=> "build",
		"version"	=> "0.1.1",
		"release"	=> "2019-11-18",
	);

	app_log("Request: ".print_r($_REQUEST,true),'debug',__FILE__,__LINE__);

	# Call Requested Event
	if ($_REQUEST["method"]) {
		# Call the Specified Method
		$function_name = $_REQUEST["method"];
		$function_name();
		exit;
	}
	# Only Developers Can See The API
	elseif (! $GLOBALS['_SESSION_']->customer->has_role('build manager')) {
		header("location: /_build/home");
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
	### Add a Product								###
	###################################################
	function addProduct() {
		$product = new \Build\Product();

		$parameters = array();
		if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
		if (isset($_REQUEST['workspace'])) $parameters['workspace'] = $_REQUEST['workspace'];
		if (isset($_REQUEST['major_version'])) $parameters['major_version'] = $_REQUEST['major_version'];
		if (isset($_REQUEST['minor_version'])) $parameters['minor_version'] = $_REQUEST['minor_version'];
		if (! $product->add($parameters)) app_error("Error adding product: ".$product->error());

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->product = $product;

		print formatOutput($response);
	}

	###################################################
	### Update a Product							###
	###################################################
	function updateProduct() {
		$product = new \Build\Product();
		$product->get($_REQUEST['code']);
		if ($product->error) app_error("Error finding product: ".$product->error(),'error',__FILE__,__LINE__);
		if (! $product->id) error("Request not found");

		$parameters = array();
		if (isset($_REQUEST['workspace'])) $parameters['workspace'] = $_REQUEST['workspace'];
		if (isset($_REQUEST['major_version'])) $parameters['major_version'] = $_REQUEST['major_version'];
		if (isset($_REQUEST['minor_version'])) $parameters['minor_version'] = $_REQUEST['minor_version'];

		$product->update($parameters);
		if ($product->error()) app_error("Error updating product: ".$product->error(),'error',__FILE__,__LINE__);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->product = $product;

		print formatOutput($response);
	}

	###################################################
	### Get Specified Product						###
	###################################################
	function getProduct() {
		$product = new \Build\Product();
		$product->get($_REQUEST['name']);
		if ($product->error()) app_error($product->error());

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->product = $product;

		print formatOutput($response);
	}

	###################################################
	### Find matching Products						###
	###################################################
	function findProducts() {
		$productList = new \Build\ProductList();
		
		$parameters = array();
		if ($_REQUEST['status']) $parameters['status'] = $_REQUEST['status'];
		
		$countries = $productList->find($parameters);
		if ($productList->error) app_error("Error finding countries: ".$productList->error());

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->product = $countries;

		print formatOutput($response);
	}
	###################################################
	### Add a Version								###
	###################################################
	function addVersion() {
		if ($_REQUEST['product_id']) {
			$product = new \Build\Product($_REQUEST['product_id']);
		}
		elseif ($_REQUEST['product']) {
			$product = new \Build\Product();
			$product->get($_REQUEST['product']);
		}
		else {
			app_error("product_id or product required");
		}
		if (! $product->id) app_error("Product not found");

		$version = new \Build\Version();

		$parameters = array();
		if (isset($_REQUEST['number'])) $parameters['number'] = $_REQUEST['number'];
		if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
		if (isset($_REQUEST['tarball'])) $parameters['tarball'] = $_REQUEST['tarball'];
		if (isset($_REQUEST['message'])) $parameters['message'] = $_REQUEST['message'];
		$parameters['product_id'] = $product->id;
		if (! $version->add($parameters)) app_error("Error adding version: ".$version->error());

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->version = $version;

		print formatOutput($response);
	}

	###################################################
	### Update a Version							###
	###################################################
	function updateVersion() {
		$product = new \Build\Product();
		$product->get($_REQUEST['code']);
		if ($product->error) app_error("Error finding product: ".$product->error(),'error',__FILE__,__LINE__);
		if (! $product->id) error("Request not found");

		$parameters = array();
		if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
		if (isset($_REQUEST['tarball'])) $parameters['tarball'] = $_REQUEST['tarball'];
		if (isset($_REQUEST['message'])) $parameters['message'] = $_REQUEST['message'];
		$product->update(
			$parameters
		);
		if ($product->error) app_error("Error updating version: ".$product->error(),'error',__FILE__,__LINE__);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->product = $product;

		print formatOutput($response);
	}

	###################################################
	### Get Specified Version						###
	###################################################
	function getVersion() {
		$product = new \Build\Product($_REQUEST['product_id']);
		if (! $product->id) app_error("Product not found");

		$version = new \Build\Version();
		if (! $version->get($product->id,$_REQUEST['name']));
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->version = $version;

		print formatOutput($response);
	}

	###################################################
	### Find matching Versions						###
	###################################################
	function findVersions() {
		$versionList = new \Build\VersionList();
		
		$parameters = array();
		if ($_REQUEST['status']) $parameters['status'] = $_REQUEST['status'];
		if ($_REQUEST['product']) {
			$product = new \Build\Product();
			if (! $product->get($_REQUEST['product'])) app_error("Product not found");
			$parameters['product_id'] = $product->id;
		}
		elseif ($_REQUEST['product_id']) {
			$product = new \Build\Product($_REQUEST['product_id']);
			$parameters['product_id'] = $product->id;
		}
		if (isset($_REQUEST['number'])) $parameters['number'] = $_REQUEST['number'];
		if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
		
		$versions = $versionList->find($parameters);
		if ($versionList->error) app_error("Error finding versions: ".$versionList->error());

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->version = $versions;

		print formatOutput($response);
	}

	###################################################
	### Manage Support Schema						###
	###################################################
	function schemaVersion() {
		$schema = new \Build\Schema();
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
		$schema = new \Build\Schema();
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
