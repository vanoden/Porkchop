<?php
	#######################################################
	### support::request								###
	### Form for customers to submit support requests.	###
	### Requires authentication.						###
	### A. Caravello 10/26/2014							###
	#######################################################

	$page = new \Site\Page();
	$page->requireAuth();

	if (isset($_REQUEST['btn_submit'])) {
	
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
			$body = "The following service request was submitted:
	Request: ".$request->code."<br>
	Type: ".$_REQUEST['type']."<br>
	URL: http://".$GLOBALS['_config']->site->hostname."/_support/request_detail/".$request->code;

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
	Ticket: ".$item->id."<br>
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

            $body .= "<br>
	Ticket: ".$item->id."<br>
	&nbsp;&nbsp;".$item->description;
        }

			$message = new \Email\Message(
				array(
					'from'	=> 'service@spectrosinstruments.com',
					'subject'	=> "[SUPPORT] New Request #".$request->code." from ".$request->customer->full_name(),
					'body'		=> $body
				)
			);
			$message->html(true);
			
            $role = new \Register\Role();
            $role->get('support user');
            $role->notify($message);
            
            if ($role->error) app_log("Error sending request notification: ".$role->error);
			$page->success = 'Support request '.$request->code.' submitted.  A representative will follow up shortly';
		}
	}

	$productlist = new \Product\ItemList();
	$products = $productlist->find(array('type' => array('inventory','unique','kit')));
