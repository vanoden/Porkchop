<?php
	$page = new \Site\Page();
	$api = new \Geography\API();
	api_log("Request: ".print_r($_REQUEST,true),'debug');

	# Call Requested Event
	$method = $_REQUEST["method"] ?? null;
	if (!empty($method)) {
		if (!$api->validText($method)) {
			$page->addError("Invalid method format");
		} elseif (!method_exists($api, $method)) {
			$page->addError("Method not found: $method");
		} else {
			# Call the Specified Method
			$api->$method();
			exit;
		}
	} else {
		$page->requireRole($api->admin_role());
		print $api->_form();
	}
