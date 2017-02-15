<?php
	require_once(MODULES."/monitor/_classes/default.php");
	require_once(MODULES."/spectros/_classes/default.php");
	
	if (! $_REQUEST['code'])
	{
		$_REQUEST['code'] = $GLOBALS['_REQUEST_']->query_vars_array[0];
	}
	if (! $_REQUEST['code'])
	{
		$GLOBALS['_page']->error = "Asset code required";
		return;
	}
	$_asset = new MonitorAsset();
	$asset = $_asset->get($_REQUEST['code']);
	if (! $asset->id)
	{
		$GLOBALS['_page']->error = "Asset not found";
		return;
	}
	
	# Show available credits
	$_credit = new CalibrationVerificationCredit();
	if ($_credit->error) app_error("Error initializing calibration verification credits: ".$_credit->error,__FILE__,__LINE__);

	# See if Credits available
	$result = $_credit->get($GLOBALS['_SESSION_']->customer->organization->id);
	if ($_credit->error)
	{
		app_log("Error getting credits: ".$_credit->error,'error',__FILE__,__LINE__);
		$GLOBALS['_page']->error = "Error getting calibration credits";
		return;
	}

	if ($result->quantity > 0) $available_credits = $result->quantity;
	else $available_credits = 0;

	if ($_REQUEST['btn_submit'])
	{
		$parameters = array();

		$parameters['organization_id'] = $GLOBALS['_SESSION_']->customer->organization->id;
		$parameters['asset_id'] = $asset->id;
		$parameters['customer_id'] = $GLOBALS['_SESSION_']->customer->id;
		$parameters['date_calibration'] = get_mysql_date($_REQUEST['date_calibration']);

		$_credit = new CalibrationVerificationCredit();
		if ($_credit->error) app_error("Error initializing calibration verification credits: ".$_credit->error,__FILE__,__LINE__);

		# See if Credits available
		$result = $_credit->get($parameters['organization_id']);
		if ($_credit->error)
		{
			app_log("Error getting credits: ".$_credit->error,'error',__FILE__,__LINE__);
			$GLOBALS['_page']->error = "Error getting calibration credits";
			return;
		}
		if ($result->quantity < 1)
		{
			app_log("Not enough credits for organization '".$parameters['organization_id']."' [".print_r($result,true)."]",'notice',__FILE__,__LINE__);
			$GLOBALS['_page']->error = "Not enough calibration credits";
			return;
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

		header("location: /_spectros/calibrations/".$_REQUEST['code']);
		exit;
	}

	$date_calibration = date('m/d/Y H:i:s');
	
	function app_error($string,$file,$line)
	{
		$GLOBALS['_page']->error = $string;
		app_log($string,'error',__FILE__,__LINE__);
		return;
	}
?>