<?
	if (! role("monitor admin")) {
		$GLOBALS['_page']->error = "You are not authorized to access this view";
		return;
	}

	require_once(MODULES."/monitor/_classes/default.php");
	require_once(MODULES."/spectros/_classes/default.php");

	if ($_REQUEST['btn_submit']) {

		$parameters = array();
		if (isset($_REQUEST['date_start'])) $parameters['date_start'] = $_REQUEST['date_start'];
		if (isset($_REQUEST['date_end'])) $parameters['date_end'] = $_REQUEST['date_end'];
		if (isset($_REQUEST['asset_id'])) $parameters['asset_id'] = $_REQUEST['asset_id'];
		if (isset($_REQUEST['organization_id'])) $parameters['organization_id'] = $_REQUEST['organization_id'];
		if (isset($_REQUEST['product_id'])) $parameters['product_id'] = $_REQUEST['product_id'];
		if (isset($_REQUEST['asset_code']) && $_REQUEST['asset_code']) {
			$asset = new MonitorAsset();
			$asset->get($_REQUEST['asset_code']);
			if ($asset->id)
				$parameters['asset_id'] = $asset->id;
			else {
				$GLOBALS['_page']->error = "Asset ".$_REQUEST['asset_code']." not found";
				return;
			}
		}

		if (preg_match('/^\d+\/\d+\/\d+$/',$parameters['date_start'])) $parameters['date_start'] .= " 00:00";
		if (preg_match('/^\d+\/\d+\/\d+$/',$parameters['date_end'])) $parameters['date_end'] .= " 00:00";
	
		# Get Calibrations
		$_verification = new CalibrationVerification();
		if ($_verification->error) app_error("Error initializing verification: ".$_verification->error,__FILE__,__LINE__);
		$verifications = $_verification->find($parameters);
		if ($_verification->error) app_error("Error finding verifications: ".$_verification->error,__FILE__,__LINE__);


		$date_start = $_REQUEST['date_start'];
		$date_end = $_REQUEST['date_end'];
		$asset_code = $_REQUEST['asset_code'];
	}
	else {
		$verification = array();
	}

	$product = new Product();
	$products = $product->find();
	$organization = new RegisterOrganization();
	$organizations = $organization->find();

	function app_error($string,$file,$line)
	{
		$GLOBALS['_page']->error = $string;
		app_log($string,'error',__FILE__,__LINE__);
		return;
	}
?>
