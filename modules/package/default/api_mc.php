<?php
	$page = new \Site\Page();
	$api = new \Package\API();

	api_log("Request: ".print_r($_REQUEST,true),'debug');

	# Call Requested Event
	if ($_REQUEST["method"]) {
        if ($api->can($_REQUEST['method'])) {
    		# Call the Specified Method
    		$function_name = $_REQUEST["method"];
	    	$api->$function_name();
        }
        else {
            $api->error("Method not supported");
        }
		exit;
	} else {
		$page->requireRole($api->admin_role());
		print $api->_form();
	}
