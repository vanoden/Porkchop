<?php
    ###############################################
    ### Handle API Request for Cache module.	###
    ### A. Caravello 11/13/2019               	###
    ###############################################

	###############################################
	### Load API Objects						###
    ###############################################
	$_package = array(
		"name"		=> "cache",
		"version"	=> "0.1.1",
		"release"	=> "2019-11-13",
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
	elseif (! $GLOBALS['_SESSION_']->customer->has_role('administrator')) {
		header("location: /_site/home");
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
	### Get List of Cache Keys						###
	###################################################
	function findKeys() {
		$client = $GLOBALS['_CACHE_'];

		if (! $GLOBALS['_SESSION_']->customer->has_role('administrator')) error('Permission denied');

		$object = null;
		if (isset($_REQUEST['object']) && preg_match('/^\w[\w\-\.\_]*$/',$_REQUEST['object']) > 0) $object = $_REQUEST['object'];
		$keyArray = array();
		$keys = $client->keys($object);

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->key = $keys;

		print formatOutput($response);
	}

	###################################################
	### Get Specific Item from Cache				###
	###################################################
	function getItem() {
		if (! $GLOBALS['_SESSION_']->customer->has_role('administrator')) error('Permission denied');
		$cache_key = $_REQUEST['object']."[".$_REQUEST['id']."]";
		$cache = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
		if ($cache->error) {
			app_log("Error in cache mechanism: ".$cache->error,'error',__FILE__,__LINE__);
		}

		$object = $cache->get();

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->object = $object;

		print formatOutput($response);
	}

	###################################################
	### Delete Specific Item from Cache				###
	###################################################
	function deleteItem() {
		if (! $GLOBALS['_SESSION_']->customer->has_role('administrator')) error('Permission denied');
		$cache_key = $_REQUEST['object']."[".$_REQUEST['id']."]";
		$cache = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
		if ($cache->error) {
			app_log("Error in cache mechanism: ".$cache->error,'error',__FILE__,__LINE__);
		}

		$count = 0;
		if ($cache->exists()) {
			$cache->delete();
			$count = 1;
		}

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->count = $count;

		print formatOutput($response);
	}

	###################################################
	### Cache Stats									###
	###################################################
	function stats() {
		$client = $GLOBALS['_CACHE_'];

		if (! $GLOBALS['_SESSION_']->customer->has_role('administrator')) error('Permission denied');

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->stats = $client->stats();

		print formatOutput($response);
	}

	###################################################
	### Flush Cache									###
	###################################################
	function flushCache() {
		$client = $GLOBALS['_CACHE_'];

		if (! $GLOBALS['_SESSION_']->customer->has_role('administrator')) error('Permission denied');

		$client->flush();

		$response = new \HTTP\Response();
		$response->success = 1;

		print formatOutput($response);
	}

	###################################################
	### Manage Support Schema						###
	###################################################
	function schemaVersion() {
		$schema = new \Geography\Schema();
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
		$schema = new \Geography\Schema();
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
