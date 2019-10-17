<?php
	$page = new \Site\Page();
	$page->requireRole("support user");

    // @TODO form submitted, handle here
    if ($_REQUEST['form_submitted'] == 'submit') {
    
        // $_REQUEST ->
        //    [shipping_firstname] => Kevin Hinds
        //    [shipping_address] => test
        //    [shipping_city] => test
        //    [shipping_state] => test
        //    [shipping_zip] => test
        //    [billing_same_as_shipping] => billing_same_as_shipping
        //    [billing_firstname] => Kevin Hinds
        //    [billing_address] => 
        //    [billing_city] => 
        //    [billing_state] => 
        //    [billing_zip] => 
        //    [power_cord] => power_cord
        //    [filters] => filters
        //    [battery] => battery
        //    [carry_bag] => carry_bag
        //    [usb_comm_cable] => usb_comm_cable
        //    [cellular_access_point] => cellular_access_point
        //    [agree_package_properly] => agree_package_properly
        //    [agree_payment_received] => agree_payment_received
        //    [delivery_instructions] => test
        //    [tracking_numbers] => test
        //    [form_submitted] => submit
        
        print_r($_REQUEST);    
        die();
    }
    
	if (isset($_REQUEST['id'])) {
		$rma = new \Support\Request\Item\RMA($_REQUEST['id']);
	} elseif (isset($_REQUEST['code'])) {
		$rma = new \Support\Request\Item\RMA();
		$rma->get($_REQUEST['code']);
	} elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$rma = new \Support\Request\Item\RMA();
		$rma->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
	}

	if ($rma->exists()) {
		$events = $rma->events();
	} else {
		$events = array();
	}
