<?
	#######################################################
	### support::request								###
	### Form for customers to submit support requests.	###
	### Requires authentication.						###
	### A. Caravello 10/26/2014							###
	#######################################################

	$page = new \Site\Page();

	# Make sure customer is signed in
	if (! $GLOBALS['_SESSION_']->customer->id) {
		# Send to login page
		header("location: /_register/login?target=_support:request");
		exit;
	}

	if ($_REQUEST['btn_submit']) {
		# Enter Support Request
		$parameters = array(
			"customer_id"	=> $GLOBALS['_SESSION_']->customer->id,
			"type" 			=> $_REQUEST['type'],
			"description"	=> $_REQUEST['description']
		);
		$request = new \Support\Request();
		$request->add($parameters);
		if ($request->error()) {
			app_log("Error adding support request: ".$request->error(),'error',__FILE__,__LINE__);
			$page->addError("Error submitting request: ".$request->error());
		}
		else {
			$body = "The following support request was submitted:
	Request: ".$request->code."<br>
	Type: ".$request->type."<br>
	URL: http://".$GLOBALS['_config']->site->hostname."/_support/request/".$request->code;
			$message = new \Email\Message(
				array(
					'from'	=> 'service@spectrosinstruments.com',
					'subject'	=> "[SUPPORT] New Request #".$request->code." from ".$request->customer->full_name(),
					'body'		=> $body
				)
			);
			$message->html(true);
			$role = new \Register\Role('support user');
			$role->notify($message);
			if ($role->error) {
				app_log("Error sending request notification: ".$role->error);
			}
			$page->success = 'Support request '.$request->code.' submitted.  A representative will follow up shortly';
		}
		foreach ($_REQUEST['product_id'] as $line => $pid) {
			$item = array(
				'line'			=> $line,
				'product_id'	=> $_REQUEST['product_id'][$line],
				'serial_number'	=> $_REQUEST['serial_number'][$line],
				'description'	=> $_REQUEST['line_description'][$line],
				'quantity'		=> 1
			);
			$request->addItem($item);
			if ($request->error()) {
				$page->addError("Error adding item to request: ".$request->error());
			}
		}
	}

	$productlist = new \Product\ItemList(array('type'=>'inventory'));
	$products = $productlist->find();
?>
