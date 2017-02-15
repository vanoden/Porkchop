<?
	require_once(MODULES."/monitor/_classes/default.php");
	require_once(MODULES."/spectros/_classes/default.php");
	
	# Get Asset
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

	# Get Calibrations
	$_verification = new CalibrationVerification();
	if ($_verification->error) app_error("Error initializing verification: ".$_verification->error,__FILE__,__LINE__);
	$verifications = $_verification->find($parameters);
	if ($_verification->error) app_error("Error finding verifications: ".$_verification->error,__FILE__,__LINE__);

	function app_error($string,$file,$line)
	{
		$GLOBALS['_page']->error = $string;
		app_log($string,'error',__FILE__,__LINE__);
		return;
	}
?>