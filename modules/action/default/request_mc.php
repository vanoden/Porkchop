<?
	require_module('action');
	
	if ($_REQUEST['btn_submit'])
	{
		$_request = new ActionRequest();
		$parameters = array(
			"user_requested"	=> $GLOBALS['_SESSION_']->customer->id,
			"description"		=> $_REQUEST['description']
		);
		$request = $_request->add($parameters);
		if ($_request->error)
		{
			$GLOBALS['_page']->error = "There was an error submitting your request.";
			app_log("Error submitting support request: ".$_request->error,'error',__FILE__,__LINE__);
		}
		else
		{
			$GLOBALS['_page']->success = "Thank you for your patience.  You will be contacted shortly.";
		}
	}
?>