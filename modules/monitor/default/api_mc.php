<?php
    ###############################################
    ### Handle API Request for monitor			###
    ### communications							###
    ### A. Caravello 5/7/2009               	###
    ###############################################
	$_package = array(
		"name"		=> "monitor",
		"version"	=> "0.1.2",
		"release"	=> "2015-01-18"
	);

	app_log("Request: ".print_r($_REQUEST,true),'debug',__FILE__,__LINE__);

	###############################################
	### Load API Objects						###
    ###############################################
	# Identify Asset
	$_asset = new \Monitor\Asset();
	if ($_asset->error) {
		app_log("Failed to initiate MonitorAsset",'error',__FILE__,__LINE__);
		print "Application error";
		exit;
	}

	# Call Requested Event
	if (isset($_REQUEST["method"])) {
		$message = "Method ".$_REQUEST['method']." called by user ".$GLOBALS['_SESSION_']->customer->code;
		if (array_key_exists('asset_code',$_REQUEST)) $message .= " for asset ".$_REQUEST['asset_code'];
		app_log($message,'debug',__FILE__,__LINE__);

		# Comm Dashboard
		$_comm = new \Monitor\Communication();
		$store_request = $GLOBALS['_REQUEST_'];
		$this_post = $_POST;
		unset($this_post->password);
		$store_request->post = $this_post;
		$_comm->add(json_encode($store_request),'[PENDING]');

		# Call the Specified Method
		$function_name = $_REQUEST["method"];
		$function_name();
		exit;
	}
	# Only Developers Can See The API
	elseif (! role('monitor admin')) {
		header("location: /_monitor/home");
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
		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Add a Collector								###
	###################################################
	function addCollector() {
		$product = new \Product\Item();
		$product->get($_REQUEST['product_code']);
		if ($product->error) error("Error finding product: ".$product->error);
		if (! $product->id) error("No product found matching '".$_REQUEST['product_code']."'");

		$collector = new \Monitor\Collector();
		if ($collector->error) error("Error adding collector: ".$collector->error);
		$collector->add(
			array(
				'code'				=> $_REQUEST['code'],
				'product_id'		=> $product->id,
				'organization_id'	=> $_REQUEST['organization_id']
			)
		);
		if ($collector->error) error("Error adding collector: ".$collector->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->collector = $collector;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Update a Collector							###
	###################################################
	function updateCollector() {
		$collector = new \Monitor\Collector();
		if ($collector->error) error("Error adding collector: ".$collector->error);
		$collector->get($_REQUEST['code']);
		if ($collector->error) app_error("Error finding collector: ".$collector->error,__FILE__,__LINE__);
		if (! $collector->id) error("Collector not found");
		if ($_REQUEST['product_code']) {
			# Get Product ID
			$product = new \Product\Item($_REQUEST['product_id']);
			if (! $product->id) error("Could not find product matching ".$_REQUEST['product_code']);
		}
		$collector->update(
			array(
				'name'				=> $_REQUEST['name'],
				'organization_id'	=> $_REQUEST['organization_id'],
				'product_id'		=> $_REQUEST['product_id'],
			)
		);
		if ($collector->error) app_error("Error adding collector: ".$collector->error,__FILE__,__LINE__);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->collector = $collector;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Find matching Collector						###
	###################################################
	function findCollectors() {
		$collectorlist = new \Monitor\CollectorList();
		if ($collectorlist->error) app_error("Error adding collector: ".$collectorlist->error,__FILE__,__LINE__);
		$collectors = $collectorlist->find(
			array(
				'code' 				=> $_REQUEST['code'],
				'organization_id'	=> $_REQUEST['organization_id'],
			)
		);
		if ($collectorlist->error) app_error("Error finding collector: ".$collectorlist->error,__FILE__,__LINE__);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->collector = $collectors;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Add a Collection							###
	###################################################
	function addCollection() {
		$timezone = new DateTimeZone($GLOBALS['_SESSION_']->timezone);
		if (get_mysql_date($_REQUEST['date_start'])) {
			$time = new DateTime(get_mysql_date($_REQUEST['date_start']),$timezone);
			$_REQUEST['timestamp_start'] = $time->getTimeStamp();
		}
		else $_REQUEST['timestamp_start'] = time();
		if (get_mysql_date($_REQUEST['date_end'])) {
			$time = new DateTime(get_mysql_date($_REQUEST['date_end']),$timezone);
			$_REQUEST['timestamp_end'] = $time->getTimeStamp();
		}
		else $_REQUEST['timestamp_end'] = time() + 86400;

		$collection = new \Monitor\Collection();
		if ($collection->error) app_error("Error adding collection: ".$collection->error,__FILE__,__LINE__);
		$collection->add(
			array(
				'code'				=> $_REQUEST['code'],
				'organization_id'	=> $_REQUEST['organization_id'],
				'name'				=> $_REQUEST['name'],
				'timestamp_start'	=> $_REQUEST['timestamp_start'],
				'timestamp_end'		=> $_REQUEST['timestamp_end']
			)
		);
		if ($collection->error) app_error("Error adding collection: ".$collection->error,__FILE__,__LINE__);

		# Timezone
		$timezone = new DateTimeZone($GLOBALS['_SESSION_']->timezone);
		$time = new DateTime('now',$timezone);
		$time->setTimeStamp($collection->timestamp_start);
		$collection->date_start = $time->format("m/d/Y H:i");
		$time->setTimeStamp($collection->timestamp_end);
		$collection->date_end = $time->format("m/d/Y H:i");

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->collection = $collection;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Update a Collection							###
	###################################################
	function updateCollection() {
		# Find Requested Collection
		$collection = new \Monitor\Collection();
		if ($collection->error) app_error("Error initializing collection: ".$collection->error,__FILE__,__LINE__);
		$collection->get($_REQUEST['code']);
		if ($collection->error) app_error("Error finding collection: ".$collection->error,__FILE__,__LINE__);
		if (! $collection->id) error("Collection '".$_REQUEST['code']."' not found");

		# Find Requested Organization
		if ($_REQUEST['organization_code']) {
			$organization = new \Register\Organization();
			$organization->get($_REQUEST['organization_code']);
			if ($organization->error) app_error("Error finding organization: ".$organization->error,__FILE__,__LINE__);
			if (! $organization->id) error("Organization not found");
			$_REQUEST['organization_id'] = $organization->id;
		}

		$timezone = new DateTimeZone($GLOBALS['_SESSION_']->timezone);
		if (get_mysql_date($_REQUEST['date_start'])) {
			$time = new DateTime(get_mysql_date($_REQUEST['date_start']),$timezone);
			$_REQUEST['timestamp_start'] = $time->getTimeStamp();
		}
		if (get_mysql_date($_REQUEST['date_end'])) {
			$time = new DateTime(get_mysql_date($_REQUEST['date_end']),$timezone);
			$_REQUEST['timestamp_end'] = $time->getTimeStamp();
		}

		# Update Collection
		$collection->update(
			$_REQUEST
		);
		if ($collection->error) app_error("Error adding collection: ".$collection->error,__FILE__,__LINE__);

		# Timezone
		$timezone = new DateTimeZone($GLOBALS['_SESSION_']->timezone);
		$time = new DateTime('now',$timezone);
		$time->setTimeStamp($collection->timestamp_start);
		$collection->date_start = $time->format("m/d/Y H:i");
		$time->setTimeStamp($collection->timestamp_end);
		$collection->date_end = $time->format("m/d/Y H:i");

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->collection = $collection;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response);
	}
	###################################################
	### Set Collection Metadata						###
	###################################################
	function setCollectionMetadata() {
		$collection = new \Monitor\Collection();
		if ($collection->error) app_error("Error initializing collection: ".$collection->error,__FILE__,__LINE__);

		$collection->get($_REQUEST['code']);
		if ($collection->error) app_error("Error finding collection: ".$collection->error,__FILE__,__LINE__);
		if (! $collection->id) error("Collection '".$_REQUEST['code']."' not found");

		$collection->setMetadata($_REQUEST['key'],$_REQUEST['value']);
		if ($collection->error) app_error("Error setting metadata: ".$collection->error,__FILE__,__LINE__);

		$collection->get($_REQUEST['code']);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->collection = $collection;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response);
	}
	###################################################
	### Find matching Collections					###
	###################################################
	function findCollections() {
		$collectionlist = new \Monitor\CollectionList();
		if ($collectionlist->error) app_error("Error initializing collection: ".$collectionlist->error,__FILE__,__LINE__);
		$collections = $collectionlist->find(
			$_REQUEST
		);
		if ($collectionlist->error) app_error("Error finding collection: ".$collectionlist->error,__FILE__,__LINE__);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->collection = $collections;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response,array("stylesheet" => "monitor.collections.xsl"));
	}
	###################################################
	### Find matching Collection					###
	###################################################
	function getCollection() {
		if (isset($_REQUEST['organization'])) {
			if ($GLOBALS['_SESSION_']->customer->has_role('register manager')) {
				$organization = new \Register\Organization();
				$organization->get($_REQUEST['organization']);
			}
			else {
				error("No permissions to see other organizations data");
			}
		}
		else {
			$organization = $GLOBALS['_SESSION_']->customer->organization;
		}

		$collection = new \Monitor\Collection();
		if ($collection->error) app_error("Error finding collection: ".$collection->error,__FILE__,__LINE__);
		$collection->get($_REQUEST['code']);
		if ($collection->error) error("Error finding collection: ".$collection->error);

		# Timezone
		$timezone = new DateTimeZone($GLOBALS['_SESSION_']->timezone);
		$time = new DateTime('now',$timezone);
		$time->setTimeStamp($collection->timestamp_start);
		$collection->date_start = $time->format("m/d/Y H:i");
		$time->setTimeStamp($collection->timestamp_end);
		$collection->date_end = $time->format("m/d/Y H:i");

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->collection = $collection;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response,array("stylesheet" => "monitor.collections.xsl"));
	}

	###################################################
	### Add Sensor to Collection					###
	###################################################
	function addCollectionSensor() {
		if (! preg_match('/^[\w\-\.\_\s]+$/',$_REQUEST['collection_code'])) error("collection_code required for addCollectionSensor method");
		if (! preg_match('/^[\w\-\.\_\s]+$/',$_REQUEST['asset_code'])) error("asset_code required for addCollectionSensor method");
		if (! preg_match('/^[\w\-\.\_\s]+$/',$_REQUEST['sensor_code'])) error("sensor_code required for addCollectionSensor method");

		$collection = new \Monitor\Collection();
		if ($collection->error) error("Error finding collection: ".$collection->error);
		$collection->get($_REQUEST['collection_code'],$_REQUEST['organization_id']);
		if ($collection->error) error("Error finding collection: ".$collection->error);
		if (! $collection->id) error("Collection not found");

		$_asset = new \Monitor\Asset();
		if ($_asset->error) error("Error finding asset: ".$_asset->error);
		$asset = $_asset->get($_REQUEST['asset_code']);
		if ($_asset->error) error("Error finding asset: ".$_asset->error);
		if (! $asset->id) error("Asset '".$_REQUEST['asset_code']."' not found");

		$sensor = new \Monitor\Sensor();
		if ($sensor->error) error("Error finding sensor: ".$sensor->error);
		$sensor->get($_REQUEST['sensor_code'],$asset->id);
		if ($sensor->error) error("Error finding sensor: ".$sensor->error);
		if (! $sensor->id) error("Sensor '".$_REQUEST['sensor_code']."' for asset '".$_REQUEST['asset_code']."' not found");

		$collection->addSensor(
			$sensor->id,
			array(
				'name' => $_REQUEST['name'],
			)
		);
		if ($collection->error) error("Error adding sensor to collection: ".$collection->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->sensor = $sensor;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response);
	}
	###################################################
	### Drop Sensor from Collection					###
	###################################################
	function dropCollectionSensor()	{
		if (! preg_match('/^[\w\-\.\_\s]+$/',$_REQUEST['collection_code'])) error("collection_code required for dropCollectionSensor method");
		if (! preg_match('/^[\w\-\.\_\s]+$/',$_REQUEST['asset_code'])) error("asset_code required for dropCollectionSensor method");
		if (! preg_match('/^[\w\-\.\_\s]+$/',$_REQUEST['sensor_code'])) error("sensor_code required for dropCollectionSensor method");

		$collection = new \Monitor\Collection();
		if ($collection->error) error("Error initializing collection: ".$collection->error);
		$collection->get($_REQUEST['collection_code'],$_REQUEST['organization_id']);
		if ($collection->error) error("Error finding collection: ".$collection->error);
		if (! $collection->id) error("Collection not found");

		$asset = new \Monitor\Asset();
		if ($asset->error) error("Error finding asset: ".$asset->error);
		$asset->get($_REQUEST['asset_code']);
		if ($asset->error) error("Error finding asset: ".$asset->error);
		if (! $asset->id) error("Asset '".$_REQUEST['asset_code']."' not found");

		$sensor = new \Monitor\Sensor();
		if ($sensor->error) error("Error finding sensor: ".$sensor->error);
		$sensor->get($_REQUEST['sensor_code'],$asset->id);
		if ($sensor->error) error("Error finding sensor: ".$sensor->error);
		if (! $sensor->id) error("Sensor '".$_REQUEST['sensor_code']."' for asset '".$_REQUEST['asset_code']."' not found");

		$collection->dropSensor(
			$sensor->id
		);
		if ($collection->error) error("Error dropping sensor from collection: ".$collection->error);
		$response = new \HTTP\Response();
		$response = new stdClass();
		$response->success = 1;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response);
	}
	###################################################
	### Find Sensors in Collection					###
	###################################################
	function findCollectionSensors() {
		if (! array_key_exists('collection_code',$_REQUEST)) error('collection_code required');
		if (! array_key_exists('organization_id',$_REQUEST)) $_REQUEST['organization_id'] = '';

		$collection = new \Monitor\Collection();
		if ($collection->error) error("Error finding collection: ".$collection->error);
		$collection->get($_REQUEST['collection_code'],$_REQUEST['organization_id']);
		if ($collection->error) error("Error finding collection: ".$collection->error);
		if (! $collection->id) error("Collection not found");

		$sensors = $collection->sensors($collection->id);
		if ($collection->error) error("Error finding collection: ".$collection->error);

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->sensor = $sensors;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response);
	}
	function dygraphData() {
		$collection = new \Monitor\Collection();
		if (! isset($_REQUEST['code'])) error("Invalid or no id given to graph");

		if (isset($_REQUEST['organization'])) {
			if ($GLOBALS['_SESSION_']->customer->has_role('register manager')) {
				$organization = new \Register\Organization();
				$organization->get($_REQUEST['organization']);
			}
			else {
				error("No permissions to see other organizations data");
			}
		}
		else {
			$organization = $GLOBALS['_SESSION_']->customer->organization;
		}

		# Get Collection from Database
		$collection->get($_REQUEST['code'],$organization->id);
		if ($collection->error) error($collection->error);
		if (! $collection->id) error("Collection not found");

		$sensors = $collection->sensors($collection->id);
		if ($collection->error) error("Error getting sensors: ".$collection->error);
	
		$data = array();
		foreach ($sensors as $sensor)
		{
			$readings = $collection->readings(
				$sensor->id,
				array("_timestamp" => 1)
			);
			if ($collection->error) {
				print "Error getting readings: ".$_collection->error;
				return;
			}
			foreach ($readings as $reading) {
				$data[sprintf("%0d",($reading->timestamp/60))*60][$sensor->code] = $reading->value;
			}
		}
		ksort($data);
		header("Content-type: text/csv");
		$output = array();
		foreach (array_keys($data) as $x) {
			$instance = array();
			$instance[0] = date("Y/m/d H:i",$x);
			$i = 1;
			foreach ($sensors as $sensor) {
				if ($data[$x][$sensor->code]) $instance[$i] = $data[$x][$sensor->code];
				else $instance[$i] = 'null';
				$i ++;
			}
			array_push($output,$instance);
		}
		$_comm = new \Monitor\Communication();
		$response = new \HTTP\Response();
		$response->success = 1;
		api_log($response);
		$response->output = $output;
		$_comm->update(json_encode($response));
		header("Content-type: application/json");
		print json_encode($output);
	}
	function dygraphCSV() {
		if (! $GLOBALS['_SESSION_']->customer->id) {
			print "Customer not logged in";
			return null;
		}
		if (! isset($_REQUEST['code'])) error("Invalid or no id given to graph");

		if (isset($_REQUEST['organization'])) {
			if ($GLOBALS['_SESSION_']->customer->has_role('register manager')) {
				$organization = new \Register\Organization();
				$organization->get($_REQUEST['organization']);
			}
			else {
				error("No permissions to see other organizations data");
			}
		}
		else {
			$organization = $GLOBALS['_SESSION_']->customer->organization;
		}

		$collection = new \Monitor\Collection();
	
		# Get Collection from Database
		$collection->get($_REQUEST['code'],$organization->id);
	
		if ($collection->error) error($collection->error);
		if (! $collection->id) error("Collection not found");

		$sensors = $collection->sensors();
		if ($collection->error) error("Error getting sensors: ".$collection->error);
	
		$data = array();
		foreach ($sensors as $sensor) {
			$readings = $collection->readings(
				$sensor->id,
				array("_timestamp" => 1)
			);
			if ($collection->error) {
				print "Error getting readings: ".$collection->error;
				return;
			}
			foreach ($readings as $reading) {
				$data[sprintf("%0d",($reading->timestamp/60))*60][$sensor->code] = $reading->value;
			}
		}

		$timezone = new DateTimeZone($GLOBALS['_SESSION_']->timezone);

		ksort($data);
		header("Content-type: text/csv");
		$content = '';
		$labels = 'Date';
		$first_loop = 1;
		foreach (array_keys($data) as $x) {
			$time = new DateTime('now',$timezone);
			$time->setTimeStamp($x);
			
			$content .= $time->format("Y/m/d H:i").",";
			$last = count($sensors);
			$i = 0;
			foreach ($sensors as $sensor) {
				if ($first_loop)
					$labels .= ",".$sensor->code;

				if (array_key_exists($sensor->code,$data[$x])) $content .= $data[$x][$sensor->code];
				else $content .= 'null';
				$i ++;
				if ($i < $last) $content .= ",";
			}
			$content .= "\n";
			$first_loop = 0;
		}
		$_comm = new \Monitor\Communication();
		$response = new \HTTP\Response();
		$response->success = 1;
		api_log($response);
		$response->output = $labels."\n".$content;
		$_comm->update(json_encode($response));
		print $labels."\n".$content;
		exit;
	}
	###################################################
	### Add an Asset								###
	###################################################
	function addAsset() {
		if (! preg_match('/^[\w\-\_\.\:\(\)]+$/',$_REQUEST['code']))
			error("code required to add asset");

		if (isset($_REQUEST['organization'])) {
			if ($GLOBALS['_SESSION_']->customer->has_role('register manager')) {
				$organization = new \Register\Organization($_REQUEST['organization_id']);
			}
			else {
				error("No permissions to see other organizations data");
			}
		}
		else {
			$organization = $GLOBALS['_SESSION_']->customer->organization;
		}

		$product = new \Product\Item();
		$product->get($_REQUEST['product_code']);
		if ($product->error) {
			app_error("Error finding product: ".$product->error,__FILE__,__LINE__);
			error("No product found matching '".$_REQUEST['product']."'");
		}

		$organization = new \Register\Organization($_REQUEST['organization_id']);
		if ($organization->error) app_error("Error finding organization: ".$organization->error,__FILE__,__LINE__);
		if (! $organization->id) error("No organization found matching '".$_REQUEST['organization']);

		$asset = new \Monitor\Asset();
		if ($asset->error) app_error("Error adding asset: ".$asset->error,__FILE__,__LINE__);
		$asset->add(
			array(
				'code'				=> $_REQUEST['code'],
				'product_id'		=> $product->id,
				'organization_id'	=> $organization->id,
				'name'				=> $_REQUEST['code']
			)
		);
		if ($asset->error) error("Error adding asset: ".$asset->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->asset = $asset;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Get Specified Asset							###
	###################################################
	function getAsset() {
		$asset = new \Monitor\Asset();
		if ($asset->error) app_error("Error initializing asset: ".$asset->error,__FILE__,__LINE__);

		$asset->get($_REQUEST['code']);
		if ($asset->error) app_error("Error finding asset(s): ".$asset->error,__FILE__,__LINE__);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->asset = $asset;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response); #,array("stylesheet" => "monitor.assets.xsl"));
	}

	###################################################
	### Update an Asset								###
	###################################################
	function updateAsset() {
		$asset = new \Monitor\Asset();
		if ($asset->error) app_error("Error initializing asset: ".$asset->error,__FILE__,__LINE__);
		$asset->get($_REQUEST['code']);
		if ($asset->error) app_error("Error finding asset: ".$asset->error,__FILE__,__LINE__);
		if (! $asset->id) error("Asset not found");

		$parameters = array();
		if ($_REQUEST['name'])
			$parameters['name'] = $_REQUEST['name'];
	
		if (isset($_REQUEST['organization'])) {
			if ($GLOBALS['_SESSION_']->customer->has_role('register manager')) {
				$organization = new \Register\Organization();
				$organization->get($_REQUEST['organization_code']);
				if ($organization->error) app_error("Error finding organization: ".$organization->error,__FILE__,__LINE__);
				$parameters['organization_id'] = $organization->id();
			}
			else {
				error("No permissions to specify another organization");
			}
		}

		$asset->update($parameters);
		if ($asset->error) app_error("Error updating asset: ".$_asset->error,__FILE__,__LINE__);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->asset = $asset;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Find matching Assets						###
	###################################################
	function findAssets() {
		$assetlist = new \Monitor\AssetList();
		if ($assetlist->error) app_error("Error initializing asset: ".$assetlist->error,__FILE__,__LINE__);

		$parameters = array();
		if (isset($_REQUEST['code']))
			$parameters['code'] = $_REQUEST['code'];

		if (isset($_REQUEST['name']))
			$parameters['name'] = $_REQUEST['name'];

		if (isset($_REQUEST['product_code'])) {
			$product = new \Product\Item();
			$product->get($_REQUEST['product_code']);
			if ($product->error) app_error("Error finding product: ".$product->error,__FILE__,__LINE__);
			if (! $product->id) error("Product not found");
			$parameters['product_id'] = $product->id;
		}
		if (isset($_REQUEST['organization_code'])) {
			if ($GLOBALS['_SESSION_']->customer->has_role('register manager')) {
				$organization = new \Register\Organization();
				$organization->get($_REQUEST['organization_code']);
				if ($organization->error) app_error("Error finding organization: ".$organization->error,__FILE__,__LINE__);
				$parameters['organization_id'] = $organization->id();
			}
			else {
				error("No permissions to specify another organization");
			}
		}

		$assets = $_asset->find($parameters);
		if ($_asset->error) app_error("Error initializing asset(s): ".$_asset->error,__FILE__,__LINE__);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->asset = $assets;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response); #,array("stylesheet" => "monitor.assets.xsl"));
	}

	###################################################
	### Add a Sensor								###
	###################################################
	function addSensor() {
		if (! $_REQUEST['code']) error("Code required for addSensor");
		if (! $_REQUEST['asset_code']) error("Asset required for addSensor");

		$asset = new \Monitor\Asset();
		if ($asset->error) app_error("Error adding asset: ".$asset->error,__FILE__,__LINE__);
		$asset->get($_REQUEST['asset_code']);
		if ($asset->error) app_error("Error finding asset: ".$asset->error,__FILE__,__LINE__);
		if (! $asset->id) error("Asset ".$_REQUEST['asset_code']." not found");
		if ($asset->organization->id != $GLOBALS['_SESSION_']->customer->organization->id && ! $GLOBALS['_SESSION_']->customer->has_role('register manager')) {
			error("No permissions to edit this asset");
		}

		$sensor = new \Monitor\Sensor();
		$sensor->add(
			array(
				'code'		=> $_REQUEST['code'],
				'asset_id'	=> $asset->id
			)
		);
		if ($sensor->error) error("Error adding sensor: ".$sensor->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->sensor = $sensor;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Get a Sensor								###
	###################################################
	function getSensor() {
		if (! $_REQUEST['code']) error("code required to get sensor");

		$asset = new \Monitor\Asset();
		if ($asset->error) app_error("Error adding asset: ".$asset->error,__FILE__,__LINE__);
		$asset = $asset->get($_REQUEST['asset_code']);
		if ($asset->error) app_error("Error finding asset: ".$asset->error,__FILE__,__LINE__);
		if (! $asset->id) error("Asset ".$_REQUEST['asset_code']." not found");
		if ($asset->organization->id != $GLOBALS['_SESSION_']->customer->organization->id && ! $GLOBALS['_SESSION_']->customer->has_role('register manager')) {
			error("No permissions to view this asset");
		}

		$sensor = new \Monitor\Sensor();
		if ($sensor->error) app_error("Error finding sensor: ".$sensor->error,__FILE__,__LINE__);
		$sensor->get($_REQUEST['code'],$asset->id);
		if ($sensor->error) app_error("Error finding sensor: ".$sensor->error,__FILE__,__LINE__);

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->sensor = $sensor;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Update a Sensor								###
	###################################################
	function updateSensor() {
		if (! $_REQUEST['code']) error("code required to get sensor");

		$_asset = new \Monitor\Asset();
		if ($_asset->error) error("Error adding asset: ".$_asset->error);
		$asset = $_asset->get($_REQUEST['asset_code'],$product->id);
		if ($_asset->error) error("Error finding asset: ".$_asset->error);
		if (! $asset->id) error("Asset ".$_REQUEST['asset_code']." not found");

		$_sensor = new \Monitor\Sensor();
		if ($_sensor->error) error("Error finding sensor: ".$_sensor->error);
		$sensor = $_sensor->get($_REQUEST['code'],$asset->id);
		if ($_sensor->error) error("Error finding sensor: ".$_sensor->error);
		if (! $sensor->id) error("Sensor ".$_REQUEST['code']." not found");
		$sensor = $_sensor->update(
			$sensor->id,
			array(
				'name'				=> $_REQUEST['name'],
				'units'				=> $_REQUEST['units']
			)
		);
		if ($_sensor->error) error("Error updating sensor: ".$_sensor->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->sensor = $sensor;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Find matching Sensors						###
	###################################################
	function findSensors() {
		$parameters = array();

		if (isset($_REQUEST['asset_code']) && strlen($_REQUEST['asset_code'])) {
			$asset = new \Monitor\Asset();
			$asset->get($_REQUEST["asset_code"]);
			if ($asset->error) error ("Error finding asset: ".$asset->error);
			if (! $asset->id) error ("Asset not found");
			$parameters['asset_id'] = $asset->id;
		}
		if (isset($_REQUEST['organization_id']) && $_REQUEST['organization_id'] > 0) $parameters['organization_id'] = $_REQUEST['organization_id'];
		if (isset($_REQUEST['code']) && strlen($_REQUEST['code'])) $parameters['code'] = $_REQUEST['code'];
		
		$sensorlist = new \Monitor\SensorList();
		if ($sensorlist->error) error("Error finding sensor: ".$sensorlist->error);
		$sensors = $sensorlist->find($parameters);
		if ($sensorlist->error) error("Error finding sensor: ".$sensorlist->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->sensor = $sensors;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Add a Reading								###
	###################################################
	function addReading() {
		if (! $GLOBALS['_SESSION_']->customer->id)
			error("Authentication Required");
		if ((! $GLOBALS['_SESSION_']->customer->organization->id) and (! in_array('monitor admin',$GLOBALS['_SESSION_']->customer->roles)))
			error("Must belong to an organization");
		if ($_REQUEST['organization']) {
			if (! in_array('monitor admin',$GLOBALS['_SESSION_']->customer->roles)) error("No permission for adding to other organizations");
			$organization = new \Register\Organization();
			$organization->get($_REQUEST['organization']);
			if ($organization->error) app_error("Error initializing organization: ".$organization->error,__FILE__,__LINE__);
			if (! $organization->id) error("Organization not found");
		}
		else
			$organization = $GLOBALS['_SESSION_']->customer->organization;

		# Get Asset (Monitor) by code
		if (! preg_match('/^[\w\-\.\_]+$/',$_REQUEST['asset_code'])) error("Valid asset_code required");
		$asset = new \Monitor\Asset();
		if ($asset->error) app_error("Error initializing Asset: ".$asset->error,__FILE__,__LINE__);
		$asset->get($_REQUEST['asset_code']);
		if ($asset->error) app_error("Error finding Asset: ".$asset->error,__FILE__,__LINE__);
		if (! $asset->id) error("Could not find Asset '".$_REQUEST['asset_code']."' for organization '".$organization->id."'");

		# Get Sensor by asset id/code
		if (! preg_match('/^[\w\-\.\_]+$/',$_REQUEST['sensor_code'])) error("Valid sensor_code required");
		$sensor = new \Monitor\Sensor();
		if ($sensor->error) app_error("Error initializing sensor: ".$sensor->error,__FILE__,__LINE__);
		$sensor = $sensor->get($_REQUEST['sensor_code'],$asset->id);
		if ($_sensor->error) app_error("Error finding sensor: ".$_sensor->error,__FILE__,__LINE__);
		if (! $sensor->id) error("Sensor ".$_REQUEST['sensor_code']." not found for asset ".$_REQUEST['asset_code']);

		# Add Reading
		$reading = new \Monitor\Reading();
		if ($reading->error) app_error("Error adding reading: ".$reading->error);
		$reading->add(
			array(
				'sensor_id'			=> $sensor->id,
				'organization_id'	=> $organization->id,
				'date_reading'		=> $_REQUEST['date_reading'],
				'value'				=> $_REQUEST['value'],
			)
		);
		if ($reading->error) app_error("Error adding reading: ".$reading->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->reading = $reading;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response);
	}
	###################################################
	###	Get Last Reading							###
	###################################################
	function lastReading() {
		if ($_REQUEST['collection_code']) {
			$_collection = new \Monitor\Collection();
			if ($_collection->error) app_error("Error initializing collection: ".$_collection->error,__FILE__,__LINE__);
			$collection = $_collection->get($_REQUEST['collection_code']);
			if ($_collection->error) app_error("Error finding collection: ".$_collection->error,__FILE__,__LINE__);
			if (! $collection->id) error("Collection not found");
		}
		if ($_REQUEST['asset_code']) {
			$_asset = new \Monitor\Asset();
			if ($_asset->error) error("Error finding asset: ".$_asset->error);
			list($asset) = $_asset->find(
				array (
					'code'	=> $_REQUEST['asset_code']
				)
			);
			if ($_asset->error) error("Error finding asset: ".$_asset->error);
			if (! $asset->id) error("Asset not found");
		}
		if ($_REQUEST['sensor_code']) {
			$_sensor = new \Monitor\Sensor();
			if ($_sensor->error) error("Error finding sensor: ".$_sensor->error);
			list($sensor) = $_sensor->find(
				array (
					'code'		=> $_REQUEST['sensor_code'],
					'asset_id'	=> $_asset->id,
				)
			);
			if ($_sensor->error) error("Error finding sensor: ".$_sensor->error);
			if (! $sensor->id) error("Sensor not found");
		}
		$_reading = new \Monitor\Reading();
		if ($_reading->error) error("Error finding reading: ".$_reading->error);
		$readings = $_reading->find(
			array(
				'code' 				=> $_REQUEST['code'],
				'organization_id'	=> $_REQUEST['organization_id'],
				'sensor_id'			=> $sensor->id,
				'asset_id'			=> $asset->id,
				'collection_id'		=> $collection->id,
				'_lastN'			=> 1
			)
		);
		if ($_reading->error) error("Error finding reading: ".$_reading->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->reading = $readings;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response);
	}
	###################################################
	### Find matching Sensors						###
	###################################################
	function findReading() {
		if ($_REQUEST['collection_code']) {
			$_collection = new \Monitor\Collection();
			if ($_collection->error) app_error("Error initializing collection: ".$_collection->error,__FILE__,__LINE__);
			$collection = $_collection->get($_REQUEST['collection_code']);
			if ($_collection->error) app_error("Error finding collection: ".$_collection->error,__FILE__,__LINE__);
			if (! $collection->id) error("Collection not found");
		}
		if ($_REQUEST['asset_code']) {
			$_asset = new \Monitor\Asset();
			if ($_asset->error) error("Error finding asset: ".$_asset->error);
			list($asset) = $_asset->find(
				array (
					'code'	=> $_REQUEST['asset_code']
				)
			);
			if ($_asset->error) error("Error finding asset: ".$_asset->error);
			if (! $asset->id) error("Asset not found");
		}
		if ($_REQUEST['sensor_code']) {
			$_sensor = new \Monitor\Sensor();
			if ($_sensor->error) error("Error finding sensor: ".$_sensor->error);
			list($sensor) = $_sensor->find(
				array (
					'code'		=> $_REQUEST['sensor_code'],
					'asset_id'	=> $_asset->id,
				)
			);
			if ($_sensor->error) error("Error finding sensor: ".$_sensor->error);
			if (! $sensor->id) error("Sensor not found");
		}
		$_reading = new \Monitor\Reading();
		if ($_reading->error) error("Error finding reading: ".$_reading->error);
		$readings = $_reading->find(
			array(
				'code' 				=> $_REQUEST['code'],
				'organization_id'	=> $_REQUEST['organization_id'],
				'sensor_id'			=> $sensor->id,
				'asset_id'			=> $asset->id,
				'collection_id'		=> $collection->id,
			)
		);
		if ($_reading->error) error("Error finding reading: ".$_reading->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->reading = $readings;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response);
	}
	###################################################
	### Find Readings for a Collection/Sensor		###
	###################################################
	function collectionReadings() {
		$_collection = new \Monitor\Collection();
		if ($_collection->error) app_error("Error finding collection: ".$_collection->error,__FILE__,__LINE__);
		if ($_REQUEST['collection_id']) {
			list($collection) = $_collection->find(
				array(
					'id'	=> $_REQUEST['collection_id']
				)
			);
			if ($_collection->error) app_error("Error finding collection: ".$_collection->error,__FILE__,__LINE__);
			if (! $collection->id) error("Collection not found for id ".$_REQUEST['collection_id']);
		}
		elseif ($_REQUEST['collection_code']) {
			list($collection) = $_collection->find(
				array (
					'code'	=> $_REQUEST['collection_code']
				)
			);
			if ($_collection->error) app_error("Error finding collection: ".$_collection->error,__FILE__,__LINE__);
			if (! $collection->id) error("Collection not found");
		}
		else {
			error("collection_code required");
		}
		if ($_REQUEST['asset_code']) {
			$_asset = new \Monitor\Asset();
			if ($_asset->error) app_error("Error finding asset: ".$_asset->error,__FILE__,__LINE__);
			list($asset) = $_asset->find(
				array (
					'code'	=> $_REQUEST['asset_code']
				)
			);
			if ($_asset->error) app_error("Error finding asset: ".$_asset->error,__FILE__,__LINE__);
			if (! $asset->id) error("Asset not found");
		}
		else {
			error("asset_code required");
		}
		if ($_REQUEST['sensor_code']) {
			$_sensor = new \Monitor\Sensor();
			if ($_sensor->error) app_error("Error finding sensor: ".$_sensor->error,__FILE__,__LINE__);
			list($sensor) = $_sensor->find(
				array (
					'code'		=> $_REQUEST['sensor_code'],
					'asset_id'	=> $asset->id,
				)
			);
			if ($_sensor->error)app_error("Error finding sensor: ".$_sensor->error,__FILE__,__LINE__);
			if (! $sensor->id) error("Sensor not found");
		}
		else {
			error("sensor_code required");
		}
		$readings = $_collection->readings($collection->id,$sensor->id);
		if ($_reading->error) app_error("Error finding reading: ".$_reading->error,__FILE__,__LINE__);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->reading = $readings;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Add a Message								###
	###################################################
	function addMessage() {
		if (! $GLOBALS['_SESSION_']->customer->organization->id)
			error("Must belong to an organization");
			
		# Get Asset (Monitor) by code
		if (preg_match('/^[\w\-\.\_]+$/',$_REQUEST['asset_code'])) {
			$asset = new \Monitor\Asset();
			if ($asset->error) app_error("Error initializing Asset: ".$asset->error,__FILE__,__LINE__);
			$asset->get($_REQUEST['asset_code']);
			if ($asset->error) app_error("Error finding Asset: ".$asset->error,__FILE__,__LINE__);
			if (! $asset->id) error("Could not find Asset '".$_REQUEST['asset_code']."' for organization ");
		}
		else {
			error("Valid asset_code required");
		}

		# Get Sensor by asset id/code
		if ($_REQUEST['sensor_code']) {
			if (! $asset->id) error("asset_code required when sensor_code specified.");
			if (! preg_match('/^[\w\-\.\_]+$/',$_REQUEST['sensor_code'])) error("Invalid sensor_code");
			$sensor = new \Monitor\Sensor();
			if ($sensor->error) app_error("Error initializing sensor: ".$sensor->error,__FILE__,__LINE__);
			$sensor->get($_REQUEST['sensor_code'],$asset->id);
			if ($sensor->error) app_error("Error finding sensor: ".$sensor->error,__FILE__,__LINE__);
			if (! $sensor->id) error("Sensor ".$_REQUEST['sensor_code']." not found for asset ".$_REQUEST['asset_code']);
		}

		# Get Collection
		if ($_REQUEST['collection_code']) {
			if (! preg_match('/^\w+$/',$_REQUEST['collection_code'])) error("Invalid collection code");
			$collection = new MonitorCollection();
			$collection->get($_REQUEST['collection_code']);
			if ($collection->error) app_error("Error finding collection: ".$collection->error,__FILE__,__LINE__);
			if (! $collection->id) error("Collection not found");
		}

		# Add Message
		$parameters = array();

		$parameters['organization_id'] = $GLOBALS['_SESSION_']->customer->organization->id;
		$parameters['user_id'] = $GLOBALS['_SESSION_']->customer->id;
		$parameters['asset_id'] = $asset->id;
		if (isset($sensor->id)) $parameters['sensor_id'] = $sensor->id;
		$parameters['message'] = $_REQUEST['message'];
		if (isset($_REQUEST['level'])) $parameters['level'] = $_REQUEST['level'];
		else $parameters['level'] = 'INFO';
		if (isset($collection->id)) $parameters['collection_id'] = $collection->id;
		if (get_mysql_date($_REQUEST['date_recorded'])) $parameters['date_recorded'] = get_mysql_date($_REQUEST['date_recorded']);
		else $parameters['date_recorded'] = date('Y-m-d H:i:s');

		$message = new \Monitor\Message();
		if ($message->error) app_error("Error initializing message: ".$message->error);
		$message->add($parameters);
		if ($message->error) app_error("Error adding message: ".$message->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->message = $message;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response);
	}
	###################################################
	### Find matching Sensors						###
	###################################################
	function findMessages() {
		if ($_REQUEST['collection_code']) {
			$collection = new \Monitor\Collection();
			if ($collection->error) app_error("Error initializing collection: ".$collection->error,__FILE__,__LINE__);
			$collection->get($_REQUEST['collection_code']);
			if ($collection->error) app_error("Error finding collection: ".$collection->error,__FILE__,__LINE__);
			if (! $collection->id) error("Collection not found");
		}
		if ($_REQUEST['asset_code']) {
			$asset = new \Monitor\Asset();
			if ($asset->error) error("Error finding asset: ".$asset->error);
			$asset->get($_REQUEST['asset_code']);
			if ($asset->error) error("Error finding asset: ".$asset->error);
			if (! $asset->id) error("Asset not found");
		}
		if ($_REQUEST['sensor_code']) {
			$sensor = new \Monitor\Sensor();
			if ($sensor->error) error("Error finding sensor: ".$sensor->error);
			$sensor->get($_REQUEST['sensor_code'],$asset->id);
			if ($sensor->error) error("Error finding sensor: ".$sensor->error);
			if (! $sensor->id) error("Sensor not found");
		}

		$_message = new \Monitor\Message();
		$messages = $_message->find(
			array(
				"asset_id"	=> $asset->id,
				"sensor_id"	=> $sensor->id,
				"collection_id"	=> $collection->id,
				"_limit"	=> $_REQUEST['_limit'],
			)
		);
		if ($_message->error) error("Error finding reading: ".$_reading->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->message = $messages;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response);
	}
	function schemaVersion() {
		$schema = new \Monitor\Schema();
		if ($schema->error) {
			app_error("Error getting version: ".$schema->error,__FILE__,__LINE__);
		}
		$version = $schema->version();
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->monitor->version = $version;
		$sschema = new \Spectros\Schema();
		$sversion = $sschema->version();
		$response->spectros->version = $sversion;
		header('Content-Type: application/xml');
		print XMLout($response);
	}
	function schemaUpgrade() {
		$schema = new \Monitor\Schema();
		if ($schema->error) {
			app_error("Error getting version: ".$schema->error,__FILE__,__LINE__);
		}
		$version = $schema->upgrade();
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->monitor->version = $version;
		$sschema = new \Spectros\Schema();
		$sversion = $sschema->upgrade();
		$response->spectros->version = $sversion;
		header('Content-Type: application/xml');
		print XMLout($response);
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
		$response->message = $message;
		$response->success = 0;
		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		header('Content-Type: application/xml');
		print XMLout($response,array("stylesheet" => $_REQUEST["stylesheet"]));
		exit;
	}
	###################################################
	### Convert Object to XML						###
	###################################################
	function XMLout($object,$user_options=array()) {
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
			'rootName'							=> 'opt'
    	);
    	$xml = new XML_Serializer($options);
	   	if ($xml->serialize($object)) {
			//error_log("Returning ".$xml->getSerializedData());
			$output = $xml->getSerializedData();
			if (array_key_exists('stylesheet',$user_options)) {
				$output = "<?xml-stylesheet type=\"text/xsl\" href=\"/".$user_options["stylesheet"]."\"?>".$output;
			}
			return $output;
		}
	}
?>