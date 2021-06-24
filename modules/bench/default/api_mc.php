<?php
    ###############################################
    ### Handle API Request for monitor			###
    ### communications							###
    ### A. Caravello 5/7/2009               	###
    ###############################################
	$_package = array(
		"name"		=> "bench",
		"version"	=> "0.1.0",
		"release"	=> "2017-10-30"
	);

	#app_log("Server Vars: ".print_r($_SERVER,true),'debug');
	app_log("Request: ".print_r($_REQUEST,true),'debug');

	# Call Requested Event
	if ($_REQUEST["method"]) {
		$message = "Method ".$_REQUEST['method']." called by user ".$GLOBALS['_SESSION_']->customer->code;
		if (array_key_exists('asset_code',$_REQUEST)) $message .= " for asset ".$_REQUEST['asset_code'];
		app_log($message,'debug',__FILE__,__LINE__);

		# Call the Specified Method
		$function_name = $_REQUEST["method"];
		$function_name();
		exit;
	}
	## Only Developers Can See The API
	#elseif (! in_array('bench admin',$GLOBALS['_SESSION_']->customer->roles)) {
	#	header("location: /_monitor/home");
	#	exit;
	#}

	###################################################
	### Just See if Server Is Communicating			###
	###################################################
	function ping() {
		$response = new stdClass();
		$response->header->session = $GLOBALS['_SESSION_']->code;
		$response->header->method = $_REQUEST["method"];
		$response->header->date = system_time();
		$response->message = "PING RESPONSE";
		$response->success = 1;

		print formatOutput($response);
	}

	###################################################
	### Add Bench Inventory							###
	###################################################
	function registerAsset() {
		if ($_REQUEST['code']) {
			# Find Requested Organization
			$asset = new \Bench\Asset('code');
			if ($asset->error) app_error("Error registering asset: ".$asset->error,__FILE__,__LINE__);
		}
		else {
			app_arror("'code' required for new asset");
		}
		$response = new stdClass();
		$response->success = 1;
		$response->asset = $asset;

		print formatOutput($response);
	}

	function testRequest() {
		$client = new \HTTP\Client();
	
	}
	###################################################
	### Load Bench Asset							###
	###################################################
	function getAsset() {
		if (! isset($_REQUEST['code'])) {
			app_error("'code' required for new asset");
		}

		$porkchop = new \Porkchop\Session();
		if (! $porkchop->connect('test.spectrosinstruments.com'))
			error("Could not connect: ".$porkchop->error());
		if (! $porkchop->authenticate('acaravello','Concentr8!'))
			error("Could not authenticate: ".$porkchop->error());

		$asset = new \Porkchop\Monitor\Asset($porkchop);
		if ($asset->error())
			error("Could initialize asset: ".$asset->error());
		$asset->get($_REQUEST['code']);
		if ($asset->error())
			error("Could not get asset: ".$asset->error());
		$obj = array(
			'id'	=> $asset->id(),
			'code'	=> $asset->code(),
			'product'	=> array(
				'code'		=> $asset->product()->code(),
				'name'		=> $asset->product()->name(),
				'type'		=> $asset->product()->type(),
				'status'	=> $asset->product()->status()
			)
		);

		$response = new stdClass();
		$response->success = 1;
		$response->asset = $obj;

		print formatOutput($response);
	}

	###################################################
	### Get Bench Inventory							###
	###################################################
	function findAssets() {
		$assetList = new \Bench\AssetList();
		if ($assetList->error) app_error("Error initializing Asset List: ".$assetList->error,__FILE__,__LINE__);

		if ($_REQUEST['code']) {
		}
		else {
		}

		$response = new stdClass();
		$response->success = 1;
		$response->asset = $asset;

		print formatOutput($response);
	}

	function pingBuildService() {
		$buildService = new \Build\API();
		$result = $buildService->ping();

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->info = $result;

		print formatOutput($response);
	}

	###################################################
	### Get/Update Schema Version					###
	###################################################
	function schemaVersion() {
		$schema = new \Bench\Schema();
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
		$schema = new \Bench\Schema();
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
		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		print formatOutput($response);
		exit;
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