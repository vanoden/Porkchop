<?php
	$page = new \Site\Page();
	app_log(print_r($_REQUEST,true),'debug');
	$api = new \Site\API();
app_log("API Initialized",'debug');
	api_log("Request: ".print_r($_REQUEST,true),'debug');

	# Call Requested Event
	if ($_REQUEST["method"]) {
		# Call the Specified Method
		$function_name = $_REQUEST["method"];
app_log("Calling method: $function_name",'debug');
		$api->$function_name();
		exit;
	} else {
		$page->requireRole($api->admin_role());
		print $api->_form();
	}
