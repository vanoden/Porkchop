<?php
	$site = new \Site();
	$page = $site->page();
	$api = new \Calendar\API();

	# Call Requested Event
	if ($_REQUEST["method"]) {
		# Call the Specified Method
		$function_name = $_REQUEST["method"];

		$api->$function_name();
		exit;
	} else {
		$page->requireRole($api->admin_role());
		print $api->_form();
	}