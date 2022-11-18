<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage request RMAs');

	if ($_REQUEST['rma_code']) {
		$rma = new \Support\Request\Item\RMA();
		$rma->get($_REQUEST['rma_code']);
	} elseif ($_REQUEST['id']) {
		$rma = new \Support\Request\Item\RMA($_REQUEST['id']);
	} else {
		$rma = new \Support\Request\Item\RMA();
		$rma->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
	}

	if (! $rma->id) {
		$page->addError("RMA Not Found");
	} else {
        $item = $rma->item();
		$tech = $rma->approvedBy();
        $customer = $item->request()->customer;
		$shipment = $rma->shipment();
		if ($GLOBALS['_config']->site->https) $url = "https://".$GLOBALS['_config']->site->hostname."/_support/rma_form/".$rma->code;
		else $url = "http://".$GLOBALS['_config']->site->hostname."/_support/rma_form/".$rma->code;
		$contact = $rma->billingContact();
	}
	
	// email again the customer if requested
	if ($_REQUEST['email_customer']) {
	
        // Make Sure Notification Template is available
		$return_notification = $GLOBALS['_config']->support->return_notification;
		if (! isset($return_notification) || empty($return_notification)) {
			$page->addError("Return notification template not configured");
			app_log("config->support->return_notification not set!",'error');
			return false;
		} elseif (! file_exists($return_notification->template)) {
			$page->addError("Return Notification Email Template '".$return_notification->template."' not found");
			app_log("File '".$return_notification->template."' not found! Set in config->support->return_notification setting",'error');
			return false;
		}

        // get any known emails to ensure if they have notifications set to recieve RMA details via email
        $requestedBy = $item->request()->customer;
        $rmaCustomerEmails = $item->request->customer->contacts(array('type'=>'email'));
        $hasEmailNotifications = false;
        foreach ($rmaCustomerEmails as $customerEmail) {
            if ($customerEmail->notify) $hasEmailNotifications = true;   
        }
        if (!$hasEmailNotifications){
			$page->addError("Error: RMA Could not be processed, customer has <strong>no email address</strong> set to receive RMA notifications.");
			return false;
        }
        
		// Create Template
		app_log("Populating notification email");
		if ($GLOBALS['_config']->site->https) $url = "https://".$GLOBALS['_config']->site->hostname."/_support/rma_form/".$rma->code;
		else $url = "http://".$GLOBALS['_config']->site->hostname."/_support/rma_form/".$rma->code;

		$notice_template = new \Content\Template\Shell();
		$notice_template->load($return_notification->template);
		$notice_template->addParam('CUSTOMER.FIRST_NAME',$requestedBy->first_name);
		$notice_template->addParam('CUSTOMER.LAST_NAME',$requestedBy->last_name);
		$notice_template->addParam('URL',$url);
		$notice_template->addParam('PRODUCT.SERIAL_NUMBER',$item->serial_number);

		app_log("Notifying customer of return authorization");
		$message = new \Email\Message();
		$message->from($return_notification->from);
		$message->subject($return_notification->subject);
		$message->html(true);
		$message->body($notice_template->output());
		if ($requestedBy->notify($message)) {
			$page->success = "Message delivered to ".$requestedBy->login;
			app_log("Notification email delivered to ".$requestedBy->login);
		}
		else {
			$page->addError("Error delivering notification: ".$requestedBy->error());
			app_log("Error delivering notification: ".$requestedBy->error());
		}
	}
	
    // upload files if upload button is pressed
    $configuration = new \Site\Configuration('support_attachments_s3');
    $repository = $configuration->value();
    if (isset($_REQUEST['btn_submit']) && $_REQUEST['btn_submit'] == 'Upload') {

	    $file = new \Storage\File();
	    $parameters = array();
        $parameters['repository_name'] = $_REQUEST['repository_name'];
        $parameters['type'] = $_REQUEST['type'];
        $parameters['ref_id'] = $rma->id;
	    $uploadResponse = $file->upload($parameters);
	    
	    if (!empty($file->error)) $page->addError($file->error);
	    if (!empty($file->success)) $page->success = $file->success;
	}
	
	$filesList = new \Storage\FileList();
	$filesUploaded = $filesList->find(array('type' => 'support rma', 'ref_id' => $rma->id));
