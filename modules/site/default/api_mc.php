<?php
	$page = new \Site\Page();
	$api = new \Site\API();
	$page->requireRole($api->admin_role());

	api_log("Request: ".print_r($_REQUEST,true),'debug');

	# Call Requested Event
	if ($_REQUEST["method"]) {
		# Call the Specified Method
		$function_name = $_REQUEST["method"];
		$api->$function_name();
		exit;
	}
	else {
		print $api->_form();
	}
?>