<?php
	$page = new \Site\Page(array("module" => 'register',"view" => 'reset_password'));
	if (isset($GLOBALS['_SESSION_']->customer->id)) {
		$customer_id = $GLOBALS['_SESSION_']->customer->id;
		$customer = new \Register\Customer($customer_id);
	} else {
		header("location: /_register/login?target=_register/account");
		exit;
	}
	app_log($GLOBALS['_SESSION_']->customer->login." accessing password reset for customer ".$customer_id,'notice',__FILE__,__LINE__);
	
    if (isset($_REQUEST["password"])) {
    
        app_log("Reset Password form submitted",'debug',__FILE__,__LINE__);
        $customerUpdated = false;

        // check for errors
	    if ($_REQUEST["password"] != $_REQUEST["password_2"]) $page->error .= "Passwords do not match";
	    if ($customer->password_strength($_REQUEST["password"]) < $GLOBALS['_config']->register->minimum_password_strength) $page->error .= "Password needs more complexity.".$customer->password_strength($_REQUEST["password"]);
	    if (empty($page->error) && $customer->id) {
	        app_log("Updating customer ".$customer_id,'debug',__FILE__,__LINE__);
	        $customerUpdated = $customer->update(array('password' => $_REQUEST["password"]));

	        // set the user to active if they're expired, this will ensure then can continue to login
	        if ($customer->status == 'EXPIRED') $customer->update(array('status' => 'ACTIVE'));
	        
	        if ($customer->error) {
		        app_log("Error updating customer: ".$customer->error,'error',__FILE__,__LINE__);
		        $page->error = "Error updating customer password.  Our admins have been notified.  Please try again later";
	        } else if ($customerUpdated) $page->success = 'Your password has been updated.';
	    }
	}
