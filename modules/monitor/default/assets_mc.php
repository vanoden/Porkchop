<?
	if (! $GLOBALS['_SESSION_']->customer->id) {
		header("location: /_register/login?target=_monitor:assets");
		exit;
	}

	app_log("Getting assets");
	# Get Assets
	$assetList = new \Monitor\AssetList();
	$assets = $assetList->find();
	app_log("Found assets");
?>
