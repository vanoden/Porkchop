<?
	#######################################################
	### support::request								###
	### Form for customers to submit support requests.	###
	### Requires authentication.						###
	### A. Caravello 10/26/2014							###
	#######################################################

	require_once(MODULES."/support/_classes/default.php");

	# Make sure customer is signed in
	if (! $GLOBALS['_SESSION_']->customer->id)
	{
		# Send to login page
		header("location: /_register/login?target=_support:request");
		exit;
	}

	if ($_REQUEST['btn_submit'])
	{
		# Enter Support Request
		$_request = new SupportRequest();
		$request = $_request->add();
		if ($_request->error)
		{
			app_log("Error adding support request: ".$_request->error,'error',__FILE__,__LINE__);
			$GLOBALS['_page']->error = "Error submitting request";
		}
		else
		{
			$_event = new SupportEvent();
			$event = $_event->add(
				array(
					"request"	=> $request->code,
					"comment"	=> $_REQUEST['description']
				)
			);
			if ($_event->error)
			{
				app_log("Error adding support event: ".$_event->error,'error',__FILE__,__LINE__);
				$GLOBALS['_page']->error = "Error submitting request";
			}
			print "Request added<br>";
			print_r($request);
			print_r($event);
			exit;
		}
	}
	else
	{
	}
?>