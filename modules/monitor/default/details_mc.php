<?
	# Product
	#require_once(MODULES.'/product/_classes/product.php');
	# Monitor Classes
	require_once(MODULES.'/monitor/_classes/monitor.php');

	# Spectros Classes
	require_once(MODULES.'/monitor/_classes/spectros.php');

	# Get ID from Query String if Not Post
	if (! $_REQUEST['id'])
	{
		$_REQUEST['id'] = $GLOBALS['_page']->query_string_vars[0];
	}

	# Initialize Asset Object
	$_asset = new MonitorAsset();
	if (preg_match('/^\d+$/',$_REQUEST['id']))
	{
		# Get Asset
		list($asset) = $_asset->find(
			array(
				id => $_REQUEST['id'],
			)
		);
		if ($_asset->error)
		{
			$GLOBALS['_page']->error = "Error loading asset: ".$_asset->error;
		}
		
		# Get Sensors
		$sensors = $_asset->sensors($asset->id);

		# Get Calibration History
		$_verification = new SpectrosCalibrationVerification();
		$calibrations = $_verification->find(array("asset_id" => $asset->id));
		if ($_verification->error)
		{
			$GLOBALS['_page']->error = "Error getting calibration history: ".$_verification->error;
			return 0;
		}
	}
?>