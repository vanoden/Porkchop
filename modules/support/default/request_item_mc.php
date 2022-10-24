<?php
    global $_config;
	$page = new \Site\Page();
	$page->requireRole('support user');
	
	if ($_REQUEST['item_id']) {
		$item = new \Support\Request\Item($_REQUEST['item_id']);
	} elseif ($_REQUEST['request_id'] && $_REQUEST['line']) {
		$item = new \Support\Request\Item();
		$item->get($_REQUEST['request_id'],$_REQUEST['line']);
	} elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$item = new \Support\Request\Item($GLOBALS['_REQUEST_']->query_vars_array[0]);
	}
	$request = $item->request;

	if ($_REQUEST['btn_complete']) {
		if ($item->openActions() > 0) {
			$page->addError("Item has open actions!");
		} else {
			$item->update(array('status' => 'COMPLETE'));
			if ($item->error()) $page->addError($item->error());
		}
	}
	if ($_REQUEST['btn_close_item']) {
		if ($item->openActions() > 0) {
			$page->addError("Item has open actions!");
		} else {
			$item->update(array('status' => 'CLOSED'));
			if ($item->error()) {
    			$page->addError($item->error());
			} else {
			
		        // Update Customer an action has been closed
		        if ($request->customer->id) {
		            $supportRequest = new \Support\Request($item->request_id);

                    // Update Customer the ticket has been closed
			        $message = new \Email\Message (
				        array (
					        'from'	=> 'service@spectrosinstruments.com',
					        'subject'	=> "[SUPPORT] A Ticket #" . $item->id  . " on your request " . $supportRequest->code. " has been completed.",
					        'body'		=> "[SUPPORT] A Ticket #" . $item->id  . " on your request " . $supportRequest->code. " has been completed."
				        )
			        );
			        $message->html(true);
                    $request->customer->notify($message); 
		        }		        
			}
		}
	}
	
	if ($_REQUEST['btn_reopen_item']) $item->update(array('status' => 'ACTIVE'));
	if ($_REQUEST['btn_add_action'] || $_REQUEST['btn_add_edit_action']) {
		$parameters = array(
			'type'				=> $_REQUEST['action_type'],
			'date_requested'	=> $_REQUEST['action_date_request'],
			'requested_id'		=> $_REQUEST['action_requested_by'],
			'assigned_id'		=> $_REQUEST['action_assigned_to'],
			'status'			=> $_REQUEST['action_status'],
			'description'		=> $_REQUEST['action_description']
		);

		$action = $item->addAction($parameters);
		if ($item->error()) {
			$page->addError($item->error());
		} else {
			$page->success = "Action #".$action->id." added";
		}
		if ($item->status == "NEW") $item->update(array('status' => 'ACTIVE'));
		
		// get assigned to and requester to notify
		$assigned_to = new \Register\Customer($_REQUEST['action_assigned_to']);
		
		// update the assigned to user they have a new ticket
		if ($assigned_to->id) {
			$message = new \Email\Message (
				array (
					'from'	=> 'service@spectrosinstruments.com',
					'subject'	=> "[SUPPORT] Action #".$action->id." assigned to you",
					'body'		=> "The following action was assigned to you:
                                    Request: <a href='https://".$_config->site->hostname."/_support/request_detail/".$action->item->request->code."'>".$action->item->request->code."</a><br>
                                    Item: ".$action->item->line."<br>
                                    Type: ".$action->type."<br>
                                    Product: ".$action->item->product->code."<br>
                                    Serial: ".$action->item->serial_number."<br>
                                    Description: ".$action->description
				)
			);
			$message->html(true);
			$assigned_to->notify($message);
		}

		// Update Customer an action has been created
		if ($request->customer->id) {
			$message = new \Email\Message (
				array (
					'from'	=> 'service@spectrosinstruments.com',
					'subject'	=> "[SUPPORT] Action #".$action->id." has been added to your Support Request.",
					'body'		=> "Request: ".$action->item->request->code."
                                    Item: ".$action->item->line."<br>
                                    Type: ".$action->type."<br>
                                    Product: ".$action->item->product->code."<br>
                                    Serial: ".$action->item->serial_number."<br>
                                    Description: ".$action->description
				)
			);
			$message->html(true);
			$request->customer->notify($message);
		}

		// if they click add AND edit, the directly redirect to action
		if ($_REQUEST['btn_add_edit_action'] && !empty($_REQUEST['btn_add_edit_action'])) {
	        header("Location: /_support/action/".$action->id);
            die();				
		}	
	}
	
	if ($_REQUEST['btn_add_rma']) {
	
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

		// We create an RMA record.  Shipment is created by customer when they ship, or admin when received
		$parameters = array(
			'approved_id'	=> $GLOBALS['_SESSION_']->customer->id,
			'date_approved'	=> date('Y-m-d H:i:s'),
			'item_id'		=> $item->id
		);
		$rma = $item->addRMA($parameters);
		if ($item->error()) {
			$page->addError("Unable to create RMA: ".$item->error());
			return false;
		}
		app_log("RMA ".$rma->code." created",'info');

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
		} else {
			$page->addError("Error delivering notification: ".$requestedBy->error());
			app_log("Error delivering notification: ".$requestedBy->error());
		}
	}
	if ($_REQUEST['btn_transfer_item']) {
	    $transferItemErrors = false;
		$organization = new \Register\Organization($_REQUEST['transfer_to']);
		if ($organization->id) {
			$parameters = array(
				'type'				=> 'Transfer Ownership',
				'date_requested'	=> date('Y-m-d H:i:s'),
				'requested_id'		=> $item->request->customer->id,
				'assigned_id'		=> $GLOBALS['_SESSION_']->customer->id,
				'status'			=> 'NEW',
				'description'		=> 'Transfer Device'
			);

			$action = $item->addAction($parameters);
			if ($item->error()) {
				$page->addError($item->error());
				$transferItemErrors = true;
			} else {
				$page->success = "Action #".$action->id." added";
			}
			if ($item->status == "NEW") $item->update(array('status' => 'ACTIVE'));
			$asset = new \Monitor\Asset();
			if ($asset->get($item->serial_number,$item->product->id)) {
				if ($asset->transfer($organization_id,$_REQUEST['transfer_reason'])) {
					$_REQUEST['description'] = "Device transferred to ".$organization->name." because: ".$_REQUEST['transfer_reason'];
					$action->update(array('status' => 'ACTIVE'));
					$parameters = array(
						'action_id'		=> $action->id,
						'date_event'	=> date('Y-m-d H:i:s'),
						'user_id'		=> $GLOBALS['_SESSION_']->customer->id,
						'description'	=> $_REQUEST['description'],
						'hours_worked'	=> 0
					);
					
					if ($action->addEvent($parameters)) {
						$page->success = "Device Transfered to ".$organization->name;
						if ($_REQUEST['status'] != $action->status) $action->update(array('status' => $_REQUEST['status']));
					} else {
						$page->addError($action->error());
					}
					$action->update(array('status' => 'COMPLETE'));
					$item->update(array('status' => 'COMPLETE'));
				} else {
					$page->addError($asset->error());
					$transferItemErrors = true;
				}
			} else {
				$page->addError($asset->error());
				$transferItemErrors = true;
			}
		} else {
			$page->addError("Organization not found");
			$transferItemErrors = true;
		}
		
	    // if not errors, then redirect to the new action created	
		if (!$transferItemErrors) {
            header("Location: /_support/action/".$action->id);
            die();				
		}
	}
	
	if ($_REQUEST['btn_add_shipment']) {
	
    	$shipItemErrors = false;
		$shipment = new \Shipping\Shipment($_REQUEST['shipment_id']);
		if ($shipment->error()) {
			$page->addError($shipment->error());
			$shipItemErrors = true;
		} elseif (! $shipment->id) {
			$page->addError("Shipment not found");
			$shipItemErrors = true;
		} else {
			$parameters = array(
				'user_id'		=> $GLOBALS['_SESSION_']->customer->id,
				'shipment_id'	=> $shipment->id,
			);
			$item->addToShipment($parameters);
			if ($item->error()) {
				$page->addError("Unable to associate shipment: ".$item->error());
				$shipItemErrors = true;
			} else {
				$page->success = "Item ready for pickup";
			}
		}
		
		if (!$shipItemErrors) {
            header("Location: /_shipping/admin_shipment/id=".$shipment->id);
            die();				
		}
	}
	
	if ($_REQUEST['btn_add_comment'] || $_REQUEST['btn_add_private_comment']) {
		$parameters = array(
			'author_id'	=> $GLOBALS['_SESSION_']->customer->id,
			'content'	=> $_REQUEST['content'],
			'status'	=> $_REQUEST['action_status']
		);
		
		if ($_REQUEST['btn_add_private_comment']) $parameters['private'] = 1;	
		$item->addComment($parameters);
		if ($item->error()) $page->addError("Unable to add comment: ".$item->error());
	}
	
	if (isset($_REQUEST['btn_submit'])) {
		$item->update(
			array(
				'serial_number'		=> $_REQUEST['serial_number'],
				'product_id'		=> $_REQUEST['product_id']
			)
		);
		if ($item->error()) $page->addError($item->error());
		
		// Update Customer an action has been created
		if ($request->customer->id) {
			$message = new \Email\Message (
				array (
					'from'	=> 'service@spectrosinstruments.com',
					'subject'	=> "[SUPPORT] Ticket #" . $request->code . " has been updated about your Support Request.",
					'body'		=> "Serial: " . $_REQUEST['serial_number']."<br>
                                    Product ID: " . $_REQUEST['product_id']
				)
			);
			$message->html(true);
			$request->customer->notify($message);
		}
	}
	
	// upload files if upload button is pressed
    $configuration = new \Site\Configuration('support_attachments_s3');
    $repository = $configuration->value();
    if ($_REQUEST['btn_submit'] == 'Upload') {

	    $file = new \Storage\File();
	    $parameters = array();
        $parameters['repository_name'] = $_REQUEST['repository_name'];
        $parameters['type'] = $_REQUEST['type'];
        $parameters['ref_id'] = $item->id;
	    $uploadResponse = $file->upload($parameters);
	    
	    if (!empty($file->error())) $page->addError($file->error);
	    if (!empty($file->success)) $page->success = $file->success;
	}

	$asset = new \Monitor\Asset();
	$asset->get($item->serial_number,$item->product->id);

	$filesList = new \Storage\FileList();
	$filesUploaded = $filesList->find(array('type' => 'support ticket', 'ref_id' => $item->id));

	$adminlist = new \Register\CustomerList();
	$admins = $adminlist->find(array('role' => 'support user'));
	$actionlist = new \Support\Request\Item\ActionList();	
	
	$actions = $actionlist->find(array('item_id' => $item->id));
	if ($actionlist->error()) $page->addError($actionlist->error());

	$productlist = new \Product\ItemList();
	$products = $productlist->find();
	if ($productlist->error()) $page->addError($productlist->error());

	$rmalist = new \Support\Request\Item\RMAList();
	$rmas = $rmalist->find(array('item_id' => $item->id));
	if ($rmalist->error()) $page->addError($rmalist->error());

	$commentlist = new \Support\Request\Item\CommentList();
	$comments = $commentlist->find(array('item_id' => $item->id));
	if ($commentlist->error()) $page->addError($commentlist->error());

	$organizationList = new \Register\OrganizationList();
	$organizations = $organizationList->find();
