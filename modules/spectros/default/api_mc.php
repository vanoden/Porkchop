<?php
    ###############################################
    ### Handle API Request for monitor			###
    ### communications							###
    ### A. Caravello 5/7/2009               	###
    ###############################################
	$_package = array(
		"name"		=> "spectros",
		"version"	=> "0.1.19",
		"release"	=> "2015-01-20"
	);

	app_log("Request: ".print_r($_REQUEST,true),'debug');

	###############################################
	### Load API Objects						###
    ###############################################
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
	# Only Developers Can See The API
	elseif (! in_array('monitor admin',$GLOBALS['_SESSION_']->customer->roles)) {
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
		print formatOutput($response);
	}

	###################################################
	### Add Calibration Verification Credits		###
	###################################################
	function addCalibrationVerificationCredits() {
		if (! isset($GLOBALS['_config']->spectros->calibration_product)) error("Calibration Product not configured");
		$cal_product = new \Product\Item();
		$cal_product->get($GLOBALS['_config']->spectros->calibration_product);
		if (! $cal_product->id) error("Calibration Product ".$GLOBALS['_config']->spectros->calibration_product." not found");

		# Find Requested Organization
		if ($_REQUEST['organization']) {
			$organization = new \Register\Organization();
			$organization->get($_REQUEST['organization']);
			if ($organization->error) app_error("Error finding organization: ".$organization->error,__FILE__,__LINE__);
			if (! $organization->id) error("Organization not found");
		}
		elseif ($_REQUEST['organization_id']) {
			$organization = new \Register\Organization($_REQUEST['oranization_id']);
		}
		else {
			$organization = new \Register\Organization($GLOBALS['_SESSION_']->customer->organization->id);
		}

		if ($organization->id != $GLOBALS['_SESSION_']->customer->organization->id && ! $GLOBALS['_SESSION_']->customer->has_role('spectros admin')) {
			error("You do not have privileges");
		}

		$product = $organization->product($cal_product->id);
		if ($product->error) app_error("Error finding calibration verification credits: ".$product->error,__FILE__,__LINE__);
		$product->add($_REQUEST['quantity']);
		if ($product->error) app_error("Error adding calibration verification credits: ".$product->error,__FILE__,__LINE__);

		app_log($_REQUEST['quantity']." Credits added for organization ".$organization_id);

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->credits = $product->count();

		print formatOutput($response);
	}

	###################################################
	### Find Calibration Verification Credits		###
	###################################################
	function findCalibrationVerificationCredits() {
		$parameters = array();
		
		# Find Requested Organization
		if ($_REQUEST['organization']) {
			$organization = new \Register\Organization();
			$organization->get($_REQUEST['organization']);
			if ($organization->error) app_error("Error finding organization: ".$organization->error,__FILE__,__LINE__);
			if (! $organization->id) error("Organization not found");
			$parameters['organization_id'] = $organization->id;
		}

		$creditlist = new \Spectros\Calibration\Verification\CreditList();
		if ($creditlist->error) app_error("Error finding calibration verification credits: ".$creditlist->error,__FILE__,__LINE__);
		$credits = $creditlist->find($parameters);
		if ($creditlist->error) app_error("Error finding credits: ".$creditlist->error,__FILE__,__LINE__);

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->credit = $credits;

		print formatOutput($response);
	}

	###################################################
	### Get Calibration Verification Credits		###
	###################################################
	function getCalibrationVerificationCredits() {
		if (! isset($GLOBALS['_config']->spectros->calibration_product)) error("Calibration Product not configured");
		$cal_product = new \Product\Item();
		$cal_product->get($GLOBALS['_config']->spectros->calibration_product);
		if (! $cal_product->id) error("Calibration Product ".$GLOBALS['_config']->spectros->calibration_product." not found");

		# Find Requested Organization
		if ($_REQUEST['organization']) {
			$organization = new \Register\Organization();
			$organization->get($_REQUEST['organization']);
			if ($organization->error) app_error("Error finding organization: ".$organization->error,__FILE__,__LINE__);
			if (! $organization->id) error("Organization not found");
		}
		elseif ($_REQUEST['organization_id']) {
			$organization = new \Register\Organization($_REQUEST['oranization_id']);
		}
		else {
			$organization = new \Register\Organization($GLOBALS['_SESSION_']->customer->organization->id);
		}

		if ($organization->id != $GLOBALS['_SESSION_']->customer->organization->id && ! $GLOBALS['_SESSION_']->customer->has_role('spectros admin')) {
			error("You do not have privileges");
		}
		$product = $organization->product($cal_product->id);
		if ($product->error) app_error("Error finding calibration verification credits: ".$product->error,__FILE__,__LINE__);

		app_log($product->count()." Credits available for organization ".$organization->id);

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->credits = $product->count();

		print formatOutput($response);
	}

	###################################################
	### Use Calibration Verification Credit			###
	###################################################
	function consumeCalibrationVerificationCredit() {
		if (! isset($GLOBALS['_config']->spectros->calibration_product)) error("Calibration Product not configured");
		$cal_product = new \Product\Item();
		$cal_product->get($GLOBALS['_config']->spectros->calibration_product);
		if (! $cal_product->id) error("Calibration Product ".$GLOBALS['_config']->spectros->calibration_product." not found");

		# Find Requested Organization
		if ($_REQUEST['organization']) {
			$organization = new \Register\Organization();
			$organization->get($_REQUEST['organization']);
			if ($organization->error) app_error("Error finding organization: ".$organization->error,__FILE__,__LINE__);
			if (! $organization->id) error("Organization not found");
		}
		elseif ($_REQUEST['organization_id']) {
			$organization = new \Register\Organization($_REQUEST['oranization_id']);
		}
		else {
			$organization = new \Register\Organization($GLOBALS['_SESSION_']->customer->organization->id);
		}

		if ($organization->id != $GLOBALS['_SESSION_']->customer->organization->id && ! $GLOBALS['_SESSION_']->customer->has_role('spectros admin')) {
			error("You do not have privileges");
		}
		$product = $organization->product($cal_product->id);
		if ($product->error) app_error("Error finding calibration verification credits: ".$product->error,__FILE__,__LINE__);
		$product->consume();
		if ($product->error) app_error("Error consuming credits: ".$credit->error,__FILE__,__LINE__);

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->credits = $product->count();

		print formatOutput($response);
	}

	###################################################
	### Return Validation Code to iMonitor			###
	###################################################
	function getVerificationCode() {
		# Authenticate Request
		$customer = new \Register\Customer();
		if (! $customer->authenticate($request->header->login,$request->header->password)) error("Authentication Failed");

		# Fetch Asset
		$asset = new \Monitor\Asset();
		$asset->get($request->serial);
		if ($asset->error) app_error("Unable to initialize MonitorAsset class: ".$asset->error,__FILE__,__LINE__);
		if (! $asset->id) error("Asset '".$request->serial."' not found",__FILE__,__LINE__);

		# Find Verification Record
		$verificationlist = new \Spectros\Calibration\VerificationList();
		list($verification) = $verificationlist->find(array("asset_code" => $request->serial));

		if ($verificationlist->error) app_error("Unable to find calibration verification: ".$verificationlist->error);
		if (! $verification->id) error("Unable to find calibration verification for asset.");

		$response = new \HTTP\Response();
		$response->success = 1;

		print formatOutput($response);
	}

	###################################################
	### Add a Calibration Verification				###
	###################################################
	function addCalibrationVerification() {
		if (! isset($GLOBALS['_config']->spectros->calibration_product)) error("Calibration Product not configured");
		$cal_product = new \Product\Item();
		$cal_product->get($GLOBALS['_config']->spectros->calibration_product);
		if (! $cal_product->id) error("Calibration Product ".$GLOBALS['_config']->spectros->calibration_product." not found");

		# Find Requested Organization
		if ($_REQUEST['organization']) {
			$organization = new \Register\Organization();
			$organization->get($_REQUEST['organization']);
			if ($organization->error) app_error("Error finding organization: ".$organization->error,__FILE__,__LINE__);
			if (! $organization->id) error("Organization not found");
		}
		elseif ($_REQUEST['organization_id']) {
			$organization = new \Register\Organization($_REQUEST['oranization_id']);
		}
		else {
			$organization = new \Register\Organization($GLOBALS['_SESSION_']->customer->organization->id);
		}

		if ($organization->id != $GLOBALS['_SESSION_']->customer->organization->id && ! $GLOBALS['_SESSION_']->customer->has_role('spectros admin')) {
			error("You do not have privileges");
		}
		if ($_REQUEST['asset']) {
			$assetlist = new \Monitor\AssetList();
			list($asset) = $assetlist->find(array("code" => $_REQUEST['asset']));
			if ($assetlist->error) app_error("Error finding asset: ".$assetlist->error,'error',__FILE__,__LINE__);
			if (! $asset->id) error("Asset not found");
		}
		else error("Asset code required");

		# Check Verification Code
		if (preg_match('/^[\w\-\_\.\s]+$/',$_REQUEST['code'])) $parameters['code'] = $_REQUEST['code'];
		$date_calibration = get_mysql_date($_REQUEST['date_calibration']);

		# See if Credits available
		$product = $organization->product($cal_product->id);
		if ($product->error) app_error("Error finding calibration verification credits: ".$product->error,__FILE__,__LINE__);
		if ($product->count() < 1) {
			app_log("Calibration credits for '".$organization->name."' = ".$product->count(),'info',__FILE__,__LINE__);
			error("Not enough calibration credits");
		}
		# Create Verification Record
		$verification = new \Spectros\CalibrationVerification();
		if ($verification->error) app_error("Error adding calibration verification: ".$verification->error,__FILE__,__LINE__);
		$verification->add(
			array(
				"asset_id"	=> $asset->id,
				"date_request"	=> date('Y-m-d H:i:s')
			)
		);
		if ($verification->error) app_error("Error adding collection: ".$verification->error,__FILE__,__LINE__);

		# Add Metadata to Verification Record
		$verification->setMetadata($verification->id,"standard_manufacturer",$_REQUEST['standard_manufacturer']);
		if ($verification->error) app_error("Error setting metadata for calibration verification: ".$verification->error,__FILE__,__LINE__);
		$verification->setMetadata($verification->id,"standard_concentration",$_REQUEST['standard_concentration']);
		if ($verification->error) app_error("Error setting metadata for calibration verification: ".$verification->error,__FILE__,__LINE__);
		$verification->setMetadata($verification->id,"standard_expires",$_REQUEST['standard_expires']);
		if ($verification->error) app_error("Error setting metadata for calibration verification: ".$verification->error,__FILE__,__LINE__);
		$verification->setMetadata($verification->id,"monitor_reading",$_REQUEST['monitor_reading']);
		if ($verification->error) app_error("Error setting metadata for calibration verification: ".$verification->error,__FILE__,__LINE__);
		$verification->setMetadata($verification->id,"cylinder_number",$_REQUEST['cylinder_number']);
		if ($verification->error) app_error("Error setting metadata for calibration verification: ".$verification->error,__FILE__,__LINE__);
		$verification->setMetadata($verification->id,"detector_voltage",$_REQUEST['detector_voltage']);
		if ($verification->error) app_error("Error setting metadata for calibration verification: ".$verification->error,__FILE__,__LINE__);

		# Verification Edits Complete
		$verification->ready();

		# Consume 1 credit
		$product->consume(1);
		if ($product->error) app_error("Error finding credits: ".$product->error,__FILE__,__LINE__);

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->calibration_verification = $verification;

		print formatOutput($response);
	}

	###################################################
	### Update a Calibration Verification			###
	###################################################
	function updateCalibrationVerification() {
		# Find Requested Calibration Verification
		$verification = new \Spectros\CalibrationVerification();
		if ($verification->error) app_error("Error initializing verification: ".$verification->error,__FILE__,__LINE__);
		$verification->get($_REQUEST['code']);
		if ($verification->error) app_error("Error finding verification: ".$verification->error,__FILE__,__LINE__);
		if (! $verification->id) error("Calibration verification '".$_REQUEST['code']."' not found");

		# Find Requested Organization
		if ($_REQUEST['organization_code']) {
			$organization = new RegisterOrganization();
			$organization->get($_REQUEST['organization_code']);
			if ($organization->error) app_error("Error finding organization: ".$organization->error,__FILE__,__LINE__);
			if (! $organization->id) error("Organization not found");
			$_REQUEST['organization_id'] = $organization->id;
		}

		# Update Calibration Verification
		$verification->update(
			$verification->id,
			$_REQUEST
		);
		if ($verification->error) app_error("Error adding calibration verification: ".$verification->error,__FILE__,__LINE__);

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->calibration_verification = $verification;

		print formatOutput($response);
	}
	###################################################
	### Set Calibration Metadata					###
	###################################################
	function setCalibrationMetadata() {
		# Find Requested Calibration Verification
		$verification = new \Spectros\CalibrationVerification();
		if ($verification->error) app_error("Error initializing verification: ".$verification->error,__FILE__,__LINE__);

		$verification->get($_REQUEST['code']);
		if ($verification->error) app_error("Error finding Verification: ".$verification->error,__FILE__,__LINE__);
		if (! $verification->id) error("Verification '".$_REQUEST['code']."' not found");

		$verification->setMetadata($_REQUEST['key'],$_REQUEST['value']);
		if ($verification->error) app_error("Error setting metadata: ".$verification->error,__FILE__,__LINE__);

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->value = $verification->getMetadata($_REQUEST['key']);

		print formatOutput($response);
	}
	###################################################
	### Find matching Calibrations					###
	###################################################
	function findCalibrationVerifications() {
		$parameters = array();
		# Find Requested Organization
		if ($_REQUEST['organization']) {
			$organization = new \Register\Organization();
			$organization->get($_REQUEST['organization']);
			if ($organization->error) app_error("Error finding organization: ".$organization->error,__FILE__,__LINE__);
			if (! $organization->id) error("Organization not found");
			$parameters['organization_id'] = $organization->id;
		}
		else {
			$parameters['organization_id'] = $GLOBALS['_SESSION_']->customer->organization->id;
		}
		if ($_REQUEST['asset']) {
			$asset = new \Monitor\Asset();
			$asset->get($_REQUEST['asset']);
			if ($asset->error) app_error("Error finding asset: ".$asset->error,'error',__FILE__,__LINE__);
			$parameters['asset_id'] = $asset->id;
		}

		$verificationlist = new \Spectros\Calibration\VerificationList();
		if ($verificationlist->error) app_error("Error initializing verification: ".$verificationlist->error,__FILE__,__LINE__);
		$verifications = $verificationlist->find($parameters);
		if ($verificationlist->error) app_error("Error finding verifications: ".$verificationlist->error,__FILE__,__LINE__);

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->verification = $verifications;

		print formatOutput($response);
	}
	###################################################
	### Find matching Calibration					###
	###################################################
	function getCalibrationVerification() {
		$verification = new \Spectros\CalibrationVerification();
		if ($verification->error) app_error("Error finding calibration verification: ".$verification->error,__FILE__,__LINE__);
		$verification->get($_REQUEST['code']);
		if ($verification->error) error("Error finding verification: ".$verification->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->calibration_verification = $verification;

		print formatOutput($response);
	}
	###################################################
	### Unconfirmed Calibration						###
	###################################################
	function nextCalibrationVerification() {
		$assetlist = new \Monitor\AssetList();
		list($asset) = $assetlist->find(array("code" => $_REQUEST['asset']));
		if ($assetlist->error) app_error("Error finding asset: ".$assetlist->error,'error',__FILE__,__LINE__);
		if (! $asset->id) error("Asset not found");

		$verificationlist = new \Spectros\CalibrationVerificationList();
		if ($verificationlist->error) app_error("Error finding calibration verification: ".$verificationlist->error,__FILE__,__LINE__);
		$verification = $verificationlist->next($asset->id);
		if ($verification->error) error("Error finding verification: ".$verification->error);
		if (! $verification->id) error("No Available Calibration Verification records");

		# Generate Confirmation Code
		list($tsec,$tmin,$thour,$tmday,$tmon,$tyear,$twday,$tyday,$tisdst) = localtime(time());
		$cyear	= chr($tyear - 40);
		$cmon	= chr($tmon + 65);
		$crem	= substr(sprintf("%04d",$verification->id),-4,4);

		# Calculate Return Code
		$challenge	= $_REQUEST["challenge"];
		$seconds	= substr($challenge,0,2);
		$minutes	= substr($challenge,2,2);
		$year		= substr($challenge,4,2);
		$hours		= substr($challenge,6,2);
		$day		= substr($challenge,8,2);
		$month		= substr($challenge,10,2);
		$result		= sprintf("%05d",$seconds*($hours + $minutes + $seconds));
		app_log("Calibration Challenge: ".$_REQUEST["challenge"]." Response: ".$result,'info',__FILE__,__LINE__);
		$verification->response = $result;

		# Return Response
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->calibration_verification = $verification;

		print formatOutput($response);
	}
	###################################################
	### Confirm Calibration							###
	###################################################
	function confirmCalibrationVerification() {
		$verification = new \Spectros\CalibrationVerification();
		if ($verification->error) app_error("Error finding calibration verification: ".$verification->error,__FILE__,__LINE__);
		$verification->get($_REQUEST['code']);
		if ($verification->error) error("Error finding verification: ".$verification->error);
		if (! $verification->id) error("Calibration Verification not found");
		$verification->confirm();
		if ($verification->error) error("Error confirming verification: ".$verification->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->calibration_verification = $verification;

		print formatOutput($response);
	}
	###################################################
	### Get CT for Collection Sensor				###
	###################################################
	function getCollectionCT() {
		# Get Collection
		$collection = new \Spectros\Collection();
		if ($collection->error()) app_error("Error finding collection: ".$collection->error(),__FILE__,__LINE__);
		$collection->get($_REQUEST['collection_code']);
		if ($collection->error()) error("Error finding collection: ".$collection->error());
		if (! isset($collection->id)) error("Collection not found");
		
		# Get Monitor
		$monitor = new \Spectros\Monitor();
		if ($monitor->error) app_error("Error finding monitor: ".$monitor->error,__FILE__,__LINE__);
		$monitor->getSimple($_REQUEST['monitor_code']);
		app_log("Getting CT for Asset '".print_r($_REQUEST['monitor_code'],true)."'",'debug',__FILE__,__LINE__);
		if ($monitor->error) error("Error finding monitor: ".$monitor->error);
		app_log("Asset Code: ".$monitor->code,'debug',__FILE__,__LINE__);
		if (! isset($monitor->id)) error("Monitor not found");
		
		# Get Sensor
		$sensor = new \Monitor\Sensor();
		if ($sensor->error) app_error("Error finding sensor: ".$sensor->error,__FILE__,__LINE__);
		$sensor->get($_REQUEST['sensor_code'],$monitor->id);
		if ($sensor->error) error("Error finding sensor: ".$sensor->error);
		if (! isset($sensor->id)) error("Sensor not found");
		
		$ct = $collection->getCTValue($sensor->id);

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->ct = $ct;
		print formatOutput($response);
	}
	###################################################
	### Generate Collection Report					###
	###################################################
	function generateReport() {
		# Get Collection
		$collection = new \Spectros\Collection();
		if ($collection->error()) app_error("Error finding collection: ".$collection->error(),__FILE__,__LINE__);
		$collection->get($_REQUEST['code']);
		if ($collection->error()) error("Error finding collection: ".$collection->error());
		if (! isset($collection->id)) error("Collection not found");
		
		# Generate Report
		if (! $collection->generateReport()) error($collection->error());
	
		$response->success = 1;
		$response->report_code = $collection->report_code;
		print formatOutput($response);
	}

	function flagAutomationAccounts() {
		$customerList = new \Spectros\CustomerList();
		if (!$customerList->flagAutomationAccounts()) error($customerList->error());

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->updates = $customerList->count();
		print formatOutput($response);
	}

    /**
     * Maintenance Cron
     *   Create a "tickler" (Tony's word not mine) cron to notify admins of outstanding tasks/events/problems
     */
    function maintenance_report() {
        $reminder = new \Email\Reminder();
        $reminder->gather();
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->item = $reminder->remind();
        print formatOutput($response);
    }

	function schemaVersion() {
		$schema = new \Spectros\Schema();
		if ($schema->error) {
			app_error("Error getting version: ".$schema->error,__FILE__,__LINE__);
		}
		$version = $schema->version();
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
	###################################################
	### Convert XML to Object						###
	###################################################
	function XMLin($string,$user_options = array()) {
		require 'XML/Unserializer.php';
    	require 'XML/Serializer.php';
    	$options = array(
    	    XML_UNSERIALIZER_OPTION_RETURN_RESULT => false,
			XML_UNSERIALIZER_OPTION_COMPLEXTYPE => 'object'
    	);
    	$_xml = new XML_Unserializer($options);
	   	if ($_xml->unserialize($string)) {
			//error_log("Returning ".$xml->getSerializedData());
			$object = $xml->getUnserializedData();
			return $object;
		}
		else {
			error("Invalid xml in request");
		}
	}
