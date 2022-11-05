<?php
	#######################################################
	### support::request								###
	### Form for customers to submit support requests.	###
	### Requires authentication.						###
	### A. Caravello 10/26/2014							###
	#######################################################

	$page = new \Site\Page();
	$page->requireAuth();

	if (empty($GLOBALS['_SESSION_']->customer->organization->id)) $page->addError("Your registration has not been completed.  Please make sure you've validated your email and contact ".$GLOBALS['_config']->site->support_email.' for assistance.');

	if (isset($_REQUEST['btn_submit'])) {
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
            $page->addError("Invalid Token");
        }
        else {
			// Enter Support Request
			$parameters = array(
				"customer_id"	=> $GLOBALS['_SESSION_']->customer->id,
				"type" 			=> 'service',
				"description"	=> $_REQUEST['description']
			);
			$request = new \Support\Request();
			$request->add($parameters);
			if ($request->error()) {
				app_log("Error adding support request: ".$request->error(),'error',__FILE__,__LINE__);
				$page->addError("Error submitting request: ".$request->error());
			} else {
			
				// build email message string for internal and external users
				$requestorBody = "The following service request was submitted:
						Request: ".$request->code."<br>
						Type: ".$_REQUEST['type']."<br>";

				$supportUserBody = $requestorBody . "URL: http://".$GLOBALS['_config']->site->hostname."/_support/request_detail/".$request->code;

			if ($_REQUEST['type'] == 'gas monitor') {
				foreach ($_REQUEST['product_id'] as $line => $pid) {
					$parameters = array(
						'line'			=> $line,
						'product_id'	=> $_REQUEST['product_id'][$line],
						'serial_number'	=> $_REQUEST['serial_number'][$line],
						'description'	=> $_REQUEST['line_description'][$line],
						'quantity'		=> 1
					);
					$item = $request->addItem($parameters);
					if ($request->error()) $page->addError("Error adding item to request: ".$request->error());

					$body .= "<br>
	Ticket: <a href='http://".$GLOBALS['_config']->site->hostname."/_support/request_item/".$item->id."'>".$item->id."</a><br>
	&nbsp;&nbsp;".$item->product->code." ".$item->serial_number.": ".$item->description;
				}
			} else {
				$parameters = array(
					'line'			=> 0,
					'product_id'	=> 0,
					'serial_number'	=> '',
					'description'	=> $_REQUEST['description'],
					'quantity'		=> 1
				);
				$item = $request->addItem($parameters);
				if ($request->error()) $page->addError("Error adding item to request: ".$request->error());

					// internal user get the URL, external just the description
					$supportUserBody .= "<br>
						Ticket: <a href='http://".$GLOBALS['_config']->site->hostname."/_support/request_item/".$item->id."'>".$item->id."</a><br>
						&nbsp;&nbsp;".$item->description;
						
					$requestorBody .= "&nbsp;&nbsp;".$item->description;;                  
						
			}

				$message = new \Email\Message(
					array(
						'from'	=> 'service@spectrosinstruments.com',
						'subject'	=> "[SUPPORT] New Request #".$request->code." from ".$request->customer->full_name(),
						'body'		=> $supportUserBody
					)
				);
				$message->html(true);
				
				$role = new \Register\Role();
				$role->get('support user');
				$role->notify($message);
				
				// send ticket requestor an email verifying that indeed a support request now exists for them
				$message = new \Email\Message(
					array(
						'from'	=> 'service@spectrosinstruments.com',
						'subject'	=> "[SUPPORT] New Request #".$request->code." from ".$request->customer->full_name(),
						'body'		=> $requestorBody
					)
				);
				$message->html(true);
				$request->customer->notify($message);
				
				if ($role->error) app_log("Error sending request notification: ".$role->error);
				$page->success = 'Support request '.$request->code.' submitted.  A representative will follow up shortly';
			}
		}
	}

	$productlist = new \Product\ItemList();
	$products = $productlist->find(array('type' => array('inventory','unique','kit')));
