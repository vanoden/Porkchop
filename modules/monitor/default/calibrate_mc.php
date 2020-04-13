<?php		
    ###############################################
	### Load API Objects						###
    ###############################################
	# Product
	require_once(MODULES.'/product/_classes/default.php');
	# Spectros Monitor Classes
	require_once(MODULES.'/monitor/_classes/default.php');
	# Spectros Monitor Classes
	require_once(MODULES.'/monitor/_classes/spectros.php');

	if (! $GLOBALS['_SESSION_']->customer->id)
	{
		$GLOBALS['_page']->error = "Must be signed in to use this tool";
		return;
	}
	if (! $GLOBALS['_SESSION_']->customer->organization->id)
	{
		$GLOBALS['_page']->error = "Must belong to an active organization to use this tool";
		return;
	}

	if (! $_REQUEST['code'])
	{
		$_REQUEST['code'] = $GLOBALS['_REQUEST_']->query_vars_array[0];
	}
	if (! $_REQUEST['code'])
	{
		$GLOBALS['_page']->error = "Asset code required for calibration";
		return 0;
	}
	# Get Asset Details
	$_asset = new MonitorAsset();
	list($asset) = $_asset->find(
		array(
			"code"	=> $_REQUEST['code'],
		)
	);
	if ($_asset->error)
	{
		$GLOBALS['_page']->error = "Error finding asset: ".$_asset->error;
		return 0;
	}
	if (! $asset->id)
	{
error_log("Cannot find asset '".$_REQUEST['code']);
		$GLOBALS['_page']->error = "Asset not found";
		return 0;
	}
error_log("Found asset '".$_REQUEST['code']."'");

	# See If 'Credits' available
	$_credits = new SpectrosCalibrationVerificationCredit();
	$available = $_credits->count($GLOBALS['_SESSION_']->customer->organization->id);
	if ($_credits->error)
	{
		$GLOBALS['_page']->error = "Failed to get credits: ".$_credits->error;
	}
	if (! $available) $available = 0;

	# Get Product Info
	$_product = new Product();
	list($product) = $_product->find(
		array(
			id	=> $asset->product_id
		)
	);

	$_verification = new SpectrosCalibrationVerification();

	# Handle Actions
	if ($available < 1)
	{
		$GLOBALS['_page']->error = "No credits available";
	}
	elseif ($_REQUEST['todo'])
	{
		$_date = new Date();

		$verification = $_verification->add(
			$asset->id,
			array(
				"customer_id"	=> $GLOBALS['_SESSION_']->customer_id,
				"date_request"	=> $_date->mysqlFormat($_REQUEST['date_request']),
				"custom_1"		=> $_REQUEST['custom_1'],
				"custom_2"		=> $_REQUEST['custom_2'],
				"custom_3"		=> $_REQUEST['custom_3'],
				"custom_4"		=> $_REQUEST['custom_4'],
				"custom_5"		=> $_REQUEST['custom_5'],
				"custom_6"		=> $_REQUEST['custom_6'],
			)
		);
		if ($_verification->error)
		{
			$GLOBALS['_page']->error = "Error recording calibration: ".$_verification->error."\n";
		}

		# One Calibration Used
		$_credits->consume($GLOBALS['_SESSION_']->customer->organization->id,$GLOBALS['_config']->monitor->calibration_product_id);
		if ($_credits->error)
		{
			error_log("Error consuming credits for organization ".$GLOBALS['_SESSION_']->customer->organization->id.": ".$_credits->error);
			$GLOBALS['_page']->error = "Error consuming credit: ".$_credits->error;
		}
		$GLOBALS['_page']->success = "Verification recorded, you have ".$_credits->count($GLOBALS['_SESSION_']->customer->organization->id)." credits left";
	}

	$verifications = $_verification->find(
		array(
			"asset_id"	=> $asset->id,
		)
	);
	if ($_verification->error) {
		$GLOBALS['_page']->error = "Error loading verifications: ".$_verification->error;
	}
