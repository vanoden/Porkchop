<?php
	$page = new \Site\Page();
	$api = new \Database\API();

	api_log("Request: ".print_r($_REQUEST,true),'debug');

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
