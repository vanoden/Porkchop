<?php
	if (! $GLOBALS['_SESSION_']->customer->id) {
		header("location: /_register/login?target=_monitor:assets");
		exit;
	}

	# Get Assets
	$parameters = array();
	$parameters['_flat'] = true;
	$assetList = new \Monitor\AssetList();
	if (! $GLOBALS['_SESSION_']->customer->has_role('monitor admin')) {
		if ($GLOBALS['_SESSION_']->customer->organization->id) {
			$parameters['organization_id'] = $GLOBALS['_SESSION_']->customer->organization->id;
		} else {
			$page->addError("Must belong to an organization");
		}
	}
	elseif($_REQUEST['organization_id']) {
		$parameters['organization_id'] = $GLOBALS['_SESSION_']->customer->organization->id;
	}
	$assets = $assetList->find($parameters);
