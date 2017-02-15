<?
	if (! $GLOBALS['_SESSION_']->customer->id) {
		header("location: /_register/login?target=_monitor:assets");
		exit;
	}

	# Get Assets
	$assetList = new \Monitor\AssetList();
	$assets = $assetList->find();
?>
