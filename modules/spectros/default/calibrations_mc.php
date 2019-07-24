<?
	$page = new \Site\Page();
	#$page->requireRole('administrator');

	# Get Asset
	if (! $_REQUEST['code']) {
		$_REQUEST['code'] = $GLOBALS['_REQUEST_']->query_vars_array[0];
		$_REQUEST['product'] = $GLOBALS['_REQUEST_']->query_vars_array[1];

	}
	if (! $_REQUEST['code']) {
		$GLOBALS['_page']->error = "Asset code required";
		return;
	}

	$asset_product = new \Product\Item();
	$asset_product->get($_REQUEST['product']);
	if (! $asset_product->id) {
		$GLOBALS['_page']->error = "Product not found";
		return;
	}
	
	$asset = new \Monitor\Asset();
	$asset->get($_REQUEST['code'],$asset_product->id);
	if (! $asset->id) {
		$GLOBALS['_page']->error = "Asset ".$_REQUEST['code']." not found";
		return;
	}

	# Get Calibrations
	$verificationlist = new \Spectros\CalibrationVerificationList();
	if ($verificationlist->error) app_error("Error initializing verification: ".$verificationlist->error,__FILE__,__LINE__);
	$verifications = $verificationlist->find(array('asset_id' => $asset->id));
	if ($verificationlist->error) app_error("Error finding verifications: ".$verificationlist->error,__FILE__,__LINE__);

	function app_error($string,$file,$line) {
		$GLOBALS['_page']->error = $string;
		app_log($string,'error',__FILE__,__LINE__);
		return;
	}
?>
