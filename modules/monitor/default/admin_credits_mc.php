<?php
	if (! in_array('monitor admin',$GLOBALS['_SESSION_']->customer->role)) {
		return;
	}

	require_once(MODULES."/monitor/_classes/spectros.php");

	$_organization = new Organization();

	# Get Organization Info
	if ($_REQUEST['organization_id']) {
		list($organization) = $_organization->find(
			array(
				"id" => $_REQUEST['organization_id']
			)
		);
		if ($_organization->error) {
			$GLOBALS['_page']->error = "Error getting organization: ".$_organization->error;
			return;
		}

		$_credit = new SpectrosCalibrationVerificationCredit();
		if (preg_match('/^[1-9]\d*$/',$_REQUEST['more_credits'])) {
			$_credit->add($organization->id,$_REQUEST['more_credits']);
			if ($_credit->error)
				$GLOBALS['_page']->error = "Failed to add credits: ".$_credit->error;
			else
				$GLOBALS['_page']->success = "Added ".$_REQUEST['more_credits']." credits";
		}
		$credits = $_credit->count($organization->id);
		if ($_credit->error) $GLOBALS['_page']->error = "Error getting credits for organization: ".$_credit->error;
	}
	
	# Reference Information
	$organizations = $_organization->find();
