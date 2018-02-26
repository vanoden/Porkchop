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

	#app_log("Server Vars: ".print_r($_SERVER,true),'debug');
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
		$response = new stdClass();
		$response->header->session = $GLOBALS['_SESSION_']->code;
		$response->header->method = $_REQUEST["method"];
		$response->header->date = system_time();
		$response->message = "PING RESPONSE";
		$response->success = 1;
		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Add Calibration Verification Credits		###
	###################################################
	function addCalibrationVerificationCredits() {
		# Find Requested Organization
		if ($_REQUEST['organization']) {
			$organization = new \Register\Organization();
			$organization->get($_REQUEST['organization']);
			if ($organization->error) app_error("Error finding organization: ".$organization->error,__FILE__,__LINE__);
			if (! $organization->id) error("Organization not found");
			$organization_id = $organization->id;
		}
		else {
			$organization_id = $GLOBALS['_SESSION_']->customer->organization->id;
		}
		$credit = new \Spectros\CalibrationVerificationCredit();
		if ($credit->error) app_error("Error adding calibration verification credits: ".$_credit->error,__FILE__,__LINE__);
		$result = $credit->add($organization_id,$_REQUEST['quantity']);
		if ($credit->error) app_error("Error adding credits: ".$_credit->error,__FILE__,__LINE__);
		$response->success = 1;
		$response->credit = $credit;

		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Find Calibration Verification Credits		###
	###################################################
	function findCalibrationVerificationCredits() {
		$parameters = array();
		# Find Requested Organization
		if ($_REQUEST['organization']) {
			$_organization = new RegisterOrganization();
			$organization = $_organization->get($_REQUEST['organization']);
			if ($_organization->error) app_error("Error finding organization: ".$_organization->error,__FILE__,__LINE__);
			if (! $organization->id) error("Organization not found");
			$parameters['organization_id'] = $organization->id;
		}

		$_credit = new CalibrationVerificationCredit();
		if ($_credit->error) app_error("Error finding calibration verification credits: ".$_credit->error,__FILE__,__LINE__);
		$result = $_credit->find($parameters);
		if ($_credit->error) app_error("Error finding credits: ".$_credit->error,__FILE__,__LINE__);
		$response->success = 1;
		$response->credit = $result;

		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Get Calibration Verification Credits		###
	###################################################
	function getCalibrationVerificationCredits() {
		# Find Requested Organization
		if ($_REQUEST['organization']) {
			$_organization = new RegisterOrganization();
			$organization = $_organization->get($_REQUEST['organization']);
			if ($_organization->error) app_error("Error finding organization: ".$_organization->error,__FILE__,__LINE__);
			if (! $organization->id) error("Organization not found");
			$organization_id = $organization->id;
		}
		elseif ($_REQUEST['organization_id']) {
			$organization_id = $_REQUEST['organization_id'];
		}

		if ((! role('spectros admin')) and (! $organization_id)) {
			$organization_id = $GLOBALS['_SESSION_']->customer->organization->id;
		}
		$_credit = new CalibrationVerificationCredit();
		if ($_credit->error) app_error("Error getting calibration verification credits: ".$_credit->error,__FILE__,__LINE__);
		$result = $_credit->get($organization_id);
		if ($_credit->error) app_error("Error finding credits: ".$_credit->error,__FILE__,__LINE__);
		$response->success = 1;
		$response->credit = $result;

		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Use Calibration Verification Credit			###
	###################################################
	function consumeCalibrationVerificationCredit() {
		# Find Requested Organization
		if ($_REQUEST['organization']) {
			$_organization = new RegisterOrganization();
			$organization = $_organization->get($_REQUEST['organization']);
			if ($_organization->error) app_error("Error finding organization: ".$_organization->error,__FILE__,__LINE__);
			if (! $organization->id) error("Organization not found");
			$organization_id = $organization->id;
		}
		else {
			$organization_id = $GLOBALS['_SESSION_']->customer->organization->id;
		}

		$_credit = new CalibrationVerificationCredit();
		if ($_credit->error) app_error("Error getting calibration verification credits: ".$_credit->error,__FILE__,__LINE__);
		$result = $_credit->consume($organization_id);
		if ($_credit->error) app_error("Error finding credits: ".$_credit->error,__FILE__,__LINE__);
		$response->success = 1;
		$response->credits = $result;

		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Return Validation Code to iMonitor			###
	###################################################
	function getVerificationCode() {
		$parsed_request = XMLout($_REQUEST['parameter']);
		$response->success = 1;
		$response->request = $parsed_request;
		
		# Authenticate Request
		$_register = new RegisterCustomer();
		if (! $_register->authenticate($request->header->login,$request->header->password)) error("Authentication Failed");

		# Fetch Asset
		$_asset = new MonitorAsset();
		$asset = $_asset->get($request->serial);
		if ($_asset->error) app_error("Unable to initialize MonitorAsset class: ".$_asset->error,__FILE__,__LINE__);
		if (! $asset->id) error("Asset '".$request->serial."' not found",__FILE__,__LINE__);

		# Find Verification Record
		$_verification = new CalibrationVerification();
		$verification = $_verification->find();

		if ($_verification->error) app_error("Unable to find calibration verification: ".$_verification->error);
		if (! $verification->id) error("Unable to find calibration verification for asset.");

		print XMLout($request);
	}

	###################################################
	### Add a Calibration Verification				###
	###################################################
	function addCalibrationVerification() {
		$parameters = array();
		# Find Requested Organization
		if ($_REQUEST['organization']) {
			$_organization = new RegisterOrganization();
			$organization = $_organization->get($_REQUEST['organization']);
			if ($_organization->error) app_error("Error finding organization: ".$_organization->error,__FILE__,__LINE__);
			if (! $organization->id) error("Organization not found");
			$parameters['organization_id'] = $organization->id;
		}
		else {
			$parameters['organization_id'] = $GLOBALS['_SESSION_']->customer->organization->id;
		}

		if ($_REQUEST['asset']) {
			$_asset = new MonitorAsset();
			$asset = $_asset->get($_REQUEST['asset']);
			if ($_asset->error) app_error("Error finding asset: ".$_asset->error,'error',__FILE__,__LINE__);
			if (! $asset->id) error("Asset not found");
			$parameters['asset_id'] = $asset->id;
		}
		else error("Asset code required");

		if (preg_match('/^[\w\-\_\.\s]+$/',$_REQUEST['code'])) $parameters['code'] = $_REQUEST['code'];
		$parameters['customer_id'] = $GLOBALS['_SESSION_']->customer->organization->id;
		$parameters['date_calibration'] = get_mysql_date($_REQUEST['date_calibration']);

		$_credit = new CalibrationVerificationCredit();
		if ($_credit->error) app_error("Error initializing calibration verification credits: ".$_credit->error,__FILE__,__LINE__);

		# See if Credits available
		$result = $_credit->get($parameters['organization_id']);
		if ($result->quantity < 1)
		{
			app_log("Calibration credits for '".$parameters['organization_id']."' = ".$result->quantity,'info',__FILE__,__LINE__);
			error("Not enough calibration credits");
		}
		# Consume 1 credit
		$result = $_credit->consume($parameters['organization_id'],1);
		if ($_credit->error) app_error("Error finding credits: ".$_credit->error,__FILE__,__LINE__);

		# Create Verification Record
		$_verification = new CalibrationVerification();
		if ($_verification->error) app_error("Error adding calibration verification: ".$_verification->error,__FILE__,__LINE__);
		$verification = $_verification->add($parameters);
		if ($_verification->error) app_error("Error adding collection: ".$_verification->error,__FILE__,__LINE__);

		# Add Metadata to Verification Record
		$_verification->setMetadata($verification->id,"standard_manufacturer",$_REQUEST['standard_manufacturer']);
		if ($_verification->error) app_error("Error setting metadata for calibration verification: ".$_verification->error,__FILE__,__LINE__);
		$_verification->setMetadata($verification->id,"standard_concentration",$_REQUEST['standard_concentration']);
		if ($_verification->error) app_error("Error setting metadata for calibration verification: ".$_verification->error,__FILE__,__LINE__);
		$_verification->setMetadata($verification->id,"standard_expires",$_REQUEST['standard_expires']);
		if ($_verification->error) app_error("Error setting metadata for calibration verification: ".$_verification->error,__FILE__,__LINE__);
		$_verification->setMetadata($verification->id,"monitor_reading",$_REQUEST['monitor_reading']);
		if ($_verification->error) app_error("Error setting metadata for calibration verification: ".$_verification->error,__FILE__,__LINE__);
		$_verification->setMetadata($verification->id,"cylinder_number",$_REQUEST['cylinder_number']);
		if ($_verification->error) app_error("Error setting metadata for calibration verification: ".$_verification->error,__FILE__,__LINE__);
		$_verification->setMetadata($verification->id,"detector_voltage",$_REQUEST['detector_voltage']);
		if ($_verification->error) app_error("Error setting metadata for calibration verification: ".$_verification->error,__FILE__,__LINE__);

		$response->success = 1;
		$response->calibration_verification = $verification;

		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Update a Calibration Verification			###
	###################################################
	function updateCalibrationVerification() {
		# Find Requested Calibration Verification
		$_verification = new CalibrationVerification();
		if ($_verification->error) app_error("Error initializing verification: ".$_verification->error,__FILE__,__LINE__);
		$verification = $_verification->get($_REQUEST['code']);
		if ($_verification->error) app_error("Error finding verification: ".$_verification->error,__FILE__,__LINE__);
		if (! $verification->id) error("Calibration verification '".$_REQUEST['code']."' not found");

		# Find Requested Organization
		if ($_REQUEST['organization_code']) {
			$_organization = new RegisterOrganization();
			$organization = $_organization->get($_REQUEST['organization_code']);
			if ($_organization->error) app_error("Error finding organization: ".$_organization->error,__FILE__,__LINE__);
			if (! $organization->id) error("Organization not found");
			$_REQUEST['organization_id'] = $organization->id;
		}

		# Update Calibration Verification
		$verification = $_verification->update(
			$verification->id,
			$_REQUEST
		);
		if ($_verification->error) app_error("Error adding calibration verification: ".$_verification->error,__FILE__,__LINE__);
		$response->success = 1;
		$response->calibration_verification = $verification;

		header('Content-Type: application/xml');
		print XMLout($response);
	}
	###################################################
	### Set Calibration Metadata					###
	###################################################
	function setCalibrationMetadata() {
		$_collection = new MonitorCollection();
		if ($_collection->error) app_error("Error initializing collection: ".$_collection->error,__FILE__,__LINE__);

		$collection = $_collection->get($_REQUEST['code']);
		if ($_collection->error) app_error("Error finding collection: ".$_collection->error,__FILE__,__LINE__);
		if (! $collection->id) error("Collection '".$_REQUEST['code']."' not found");

		$_collection->setMetadata($collection->id,$_REQUEST['key'],$_REQUEST['value']);
		if ($_collection->error) app_error("Error setting metadata: ".$_collection->error,__FILE__,__LINE__);

		$collection = $_collection->get($_REQUEST['code']);
		$response->success = 1;
		$response->collection = $collection;

		header('Content-Type: application/xml');
		print XMLout($response);
	}
	###################################################
	### Find matching Calibrations					###
	###################################################
	function findCalibrationVerifications() {
		$parameters = array();
		# Find Requested Organization
		if ($_REQUEST['organization']) {
			$_organization = new RegisterOrganization();
			$organization = $_organization->get($_REQUEST['organization']);
			if ($_organization->error) app_error("Error finding organization: ".$_organization->error,__FILE__,__LINE__);
			if (! $organization->id) error("Organization not found");
			$parameters['organization_id'] = $organization->id;
		}
		else {
			$parameters['organization_id'] = $GLOBALS['_SESSION_']->customer->organization->id;
		}
		if ($_REQUEST['asset']) {
			$_asset = new MonitorAsset();
			$asset = $_asset->get($_REQUEST['asset']);
			if ($_asset->error) app_error("Error finding asset: ".$_asset->error,'error',__FILE__,__LINE__);
			$parameters['asset_id'] = $asset->id;
		}

		$_verification = new CalibrationVerification();
		if ($_verification->error) app_error("Error initializing verification: ".$_verification->error,__FILE__,__LINE__);
		$verifications = $_verification->find($parameters);
		if ($_verification->error) app_error("Error finding verifications: ".$_verification->error,__FILE__,__LINE__);
		$response->success = 1;
		$response->verification = $verifications;

		header('Content-Type: application/xml');
		print XMLout($response);
	}
	###################################################
	### Find matching Calibration					###
	###################################################
	function getCalibrationVerification() {
		$_verification = new CalibrationVerification();
		if ($_verification->error) app_error("Error finding calibration verification: ".$_verification->error,__FILE__,__LINE__);
		$verification = $_verification->get($_REQUEST['code']);
		if ($_verification->error) error("Error finding verification: ".$_verification->error);
		$response->success = 1;
		$response->calibration_verification = $verification;

		header('Content-Type: application/xml');
		print XMLout($response);
	}
	###################################################
	### Unconfirmed Calibration						###
	###################################################
	function nextCalibrationVerification() {
		$_asset = new MonitorAsset();
		$asset = $_asset->get($_REQUEST['asset']);
		if ($_asset->error) app_error("Error finding asset: ".$_asset->error,'error',__FILE__,__LINE__);
		if (! $asset->id) error("Asset not found");
		$asset_id = $asset->id;

		$_verification = new CalibrationVerification();
		if ($_verification->error) app_error("Error finding calibration verification: ".$_verification->error,__FILE__,__LINE__);
		$verification = $_verification->next($asset_id);
		if ($_verification->error) error("Error finding verification: ".$_verification->error);

		# Generate Confirmation Code
		list($tsec,$tmin,$thour,$tmday,$tmon,$tyear,$twday,$tyday,$tisdst) = localtime(time);
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
		$response->success = 1;
		$response->calibration_verification = $verification;
			
		header('Content-Type: application/xml');
		print XMLout($response);
	}
	###################################################
	### Confirm Calibration							###
	###################################################
	function confirmCalibrationVerification() {
		$_verification = new CalibrationVerification();
		if ($_verification->error) app_error("Error finding calibration verification: ".$_verification->error,__FILE__,__LINE__);
		$verification = $_verification->get($_REQUEST['code']);
		if ($_verification->error) error("Error finding verification: ".$_verification->error);
		if (! $verification->id) error("Calibration Verification not found");
		$verification = $_verification->confirm($verification->id);
		if ($_verification->error) error("Error confirming verification: ".$_verification->error);
		$response->success = 1;
		$response->calibration_verification = $verification;

		header('Content-Type: application/xml');
		print XMLout($response);
	}
	###################################################
	### Get CT for Collection Sensor				###
	###################################################
	function getCollectionCT() {
		# Get Collection
		$_collection = new SpectrosCollection();
		if ($_collection->error) app_error("Error finding collection: ".$_collection->error,__FILE__,__LINE__);
		$collection = $_collection->get($_REQUEST['collection_code']);
		if ($_collection->error) error("Error finding collection: ".$_collection->error);
		if (! isset($collection->id)) error("Collection not found");
		
		# Get Monitor
		$_monitor = new SpectrosMonitor();
		if ($_monitor->error) app_error("Error finding monitor: ".$_monitor->error,__FILE__,__LINE__);
		$monitor = $_monitor->get($_REQUEST['monitor_code']);
		if ($_monitor->error) error("Error finding monitor: ".$_monitor->error);
		if (! isset($monitor->id)) error("Monitor not found");
		
		# Get Sensor
		$_sensor = new SpectrosSensor();
		if ($_sensor->error) app_error("Error finding sensor: ".$_sensor->error,__FILE__,__LINE__);
		$sensor = $_sensor->get($_REQUEST['sensor_code'],$monitor->id);
		if ($_sensor->error) error("Error finding sensor: ".$_sensor->error);
		if (! isset($sensor->id)) error("Sensor not found");
		
		$ct = $_collection->getCTValue($collection->id,$sensor->id);

		$response = new stdClass();
		$response->success = 1;
		$response->ct = $ct;

		header('Content-Type: application/xml');
		print XMLout($response);
	}
	function schemaVersion() {
		$schema = new SpectrosSchema();
		if ($schema->error) {
			app_error("Error getting version: ".$schema->error,__FILE__,__LINE__);
		}
		$version = $schema->version();
		$response = new stdClass();
		$response->success = 1;
		$response->version = $version;
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
		$response->message = $message;
		$response->success = 0;
		header('Content-Type: application/xml');
		print XMLout($response,array("stylesheet" => $_REQUEST["stylesheet"]));
		exit;
	}
	###################################################
	### Convert Object to XML						###
	###################################################
	function XMLout($object,$user_options='') {
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
    	);
		if (isset($user_options["rootname"])) {
			$options["rootName"] = $user_options["rootname"];
		}
    	$xml = &new XML_Serializer($options);
	   	if ($xml->serialize($object)) {
			//error_log("Returning ".$xml->getSerializedData());
			$output = $xml->getSerializedData();
			if (isset($user_options["stylesheet"])) {
				$output = "<?xml-stylesheet type=\"text/xsl\" href=\"/".$user_options["stylesheet"]."\"?>".$output;
			}
			return $output;
		}
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
    	$_xml = &new XML_Unserializer($options);
	   	if ($_xml->unserialize($string)) {
			//error_log("Returning ".$xml->getSerializedData());
			$object = $xml->getUnserializedData();
			return $object;
		}
		else {
			error("Invalid xml in request");
		}
	}
?>
