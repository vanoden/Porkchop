<?
	if (! $GLOBALS['_SESSION_']->customer->id) {
		header("location: /_register/login?target=_action:request");
		exit;
	}
	
	if ($_REQUEST['btn_submit']) {
		$request = new \Action\Request();
		$parameters = array(
			"user_requested"	=> $GLOBALS['_SESSION_']->customer->id,
			"description"		=> $_REQUEST['description']
		);
		$request->add($parameters);
		if ($request->error) {
			$GLOBALS['_page']->error = "There was an error submitting your request.";
			app_log("Error submitting support request: ".$request->error,'error',__FILE__,__LINE__);
		}
		else {
			$GLOBALS['_page']->success = "Thank you for your patience.  You will be contacted shortly.";
		}
	}
?>
