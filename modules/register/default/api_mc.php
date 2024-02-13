<?php
    ###############################################
    ### Handle API Request for Customer Info 	###
    ### and Management							###
    ### A. Caravello 5/7/2009               	###
    ###############################################
	$page = new \Site\Page();
	$api = new \API();
	//api_log("Request: ".print_r($_REQUEST,true),'debug');

	# Call Requested Event
	if ($_REQUEST["method"]) {
		# Call the Specified Method
		$api->method($_REQUEST["method"]);
		exit;
	} else {
		$page->requireRole($api->admin_role());
		print $api->_form();
	}
