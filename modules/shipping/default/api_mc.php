<?php
	$page = new \Site\Page();
	$api = new \Shipping\API();

	api_log("Request: ".print_r($_REQUEST,true),'debug');

	# Call Requested Event
	if (isset($_REQUEST["method"]) && !empty($_REQUEST["method"])) {
		# Call the Specified Method
		$function_name = $_REQUEST["method"];
		$api->$function_name();
		exit;
	} else {
		$page->requireRole($api->admin_role());
		print $api->_form();
	}
