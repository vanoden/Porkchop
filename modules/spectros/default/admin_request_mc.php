<?php
	require_module('action');
	require_module('register');

	if ($_REQUEST['btn_submit'])
	{
		$request = new ActionRequest();
		$parameters = array(
			"user_requested"	=> $_REQUEST['customer_id'],
			"description"		=> $_REQUEST['description']
		);
		$request->add($parameters);
		if ($request->error)
		{
			$GLOBALS['_page']->error = "There was an error submitting your request.";
			app_log("Error submitting support request: ".$request->error,'error',__FILE__,__LINE__);
		}
		else
		{
			# Redirect to Admin Request Detail
			header("location: /_spectros/admin_request_detail/".$request->code);
			print "Redirecting to /_spectros/admin_request_detail/".$request->code;
			exit;
		}
	}

	$_customer = new RegisterCustomer();
	$customers = $_customer->find(array("_sort" => "last_name"));
	$_organization = new RegisterOrganization();
