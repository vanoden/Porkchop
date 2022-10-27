<?php
    global $_config;
	$page = new \Site\Page();
	$page->fromRequest();
	$page->requireRole('support user');

	if (isset($_REQUEST['action_id'])) {
		$action = new \Support\Request\Item\Action($_REQUEST['action_id']);
	} elseif ($GLOBALS['_REQUEST_']->query_vars_array[0]) {
		$action = new \Support\Request\Item\Action($GLOBALS['_REQUEST_']->query_vars_array[0]);
	} else {
		$page->addError("Action not found!");
		return;
	}
	
	if ($action->error()) $page->addError($action->error());
	$item = $action->item;
	$request = $item->request;
    $date_calibration = date('m/d/Y H:i:s');
    
	// get number of other actions for current action
	$actionList = new \Support\Request\Item\ActionList();
	$actionItemsCount = 0;
	if (isset($action->item->id) && !empty($action->item->id)) {
    	$actionItems = $actionList->find(array('item_id' => $action->item->id));
    	foreach ($actionItems as $actionItem) $actionItemsCount = $actionItemsCount + 1;
	}
	
	if (isset($_REQUEST['btn_add_event'])) {
		if ($_REQUEST['status'] != $action->status) $_REQUEST['description'] .= "<br>Status changed from ".$action->status." to ".$_REQUEST['status'];
		$action->update(array('status' => 'ACTIVE'));
		$parameters = array(
			'action_id'		=> $action->id,
			'date_event'	=> $_REQUEST['date_event'],
			'user_id'		=> $_REQUEST['user_id'],
			'description'	=> $_REQUEST['description'],
			'hours_worked'	=> $_REQUEST['hours_worked']
		);
		
		if ($action->addEvent($parameters)) {
			$page->success = "Event created";
			if ($_REQUEST['status'] != $action->status) $action->update(array('status' => $_REQUEST['status']));
		} else {
			$page->addError($action->error());
		}

        if ($action->type == 'Calibrate Unit') {

            // Create Verification Record
            $verification = new \Spectros\CalibrationVerification();
            
            if ($verification->error) {
                app_error("Error initializing calibration verification: ".$verification->error,__FILE__,__LINE__);
                $page->addError("Error recording calibration verification");
                return;
            }

            // get the current monitor asset based on code/serial and product id
	        $asset = new \Monitor\Asset();
	        $asset->get($item->product->code,$action->item->product->id);
	        if (! $asset->id) {
		        $page->addError("Asset '".$item->product->code."' not found");
		        return;
	        }

            $verification->add(array("asset_id" => $asset->id,"date_request" => $date_calibration));
            if ($verification->error) {
                app_error("Error adding calibration verification: ".$verification->error,__FILE__,__LINE__);
                $page->addError("Error recording calibration verification");
            }

            // Add Metadata to Verification Record
            $verification->setMetadata("standard_manufacturer",$_REQUEST['standard_manufacturer']);
            if ($verification->error) {
                $page->addError("Error setting metadata for calibration verification: ".$verification->error);
            }
            $verification->setMetadata("standard_concentration",$_REQUEST['standard_concentration']);
            if ($verification->error) {
                $page->addError("Error setting metadata for calibration verification: ".$verification->error);
            }
            $verification->setMetadata("standard_expires",$_REQUEST['standard_expires']);
            if ($verification->error) {
                $page->addError("Error setting metadata for calibration verification: ".$verification->error);
            }
            $verification->setMetadata("monitor_reading",$_REQUEST['monitor_reading']);
            if ($verification->error) {
                $page->addError("Error setting metadata for calibration verification: ".$verification->error);
            }
            $verification->setMetadata("cylinder_number",$_REQUEST['cylinder_number']);
            if ($verification->error) {
                $page->addError("Error setting metadata for calibration verification: ".$verification->error);
            }
            $verification->setMetadata("detector_voltage",$_REQUEST['detector_voltage']);
            if ($verification->error) {
                $page->addError("Error setting metadata for calibration verification: ".$verification->error);
            }
            $verification->ready(); 
        }

        // close the overall request_item here if 'yes' set to close the parent ticke (request item) as well
        if (isset($_REQUEST['close_ticket_too']) && !empty($_REQUEST['close_ticket_too'])) {
            if ($_REQUEST['close_ticket_too'] == 'yes') {
                $requestItem = new \Support\Request\Item($action->item->id);
                $requestItem->update(array('status' => 'COMPLETE'));
                $supportRequest = new \Support\Request($requestItem->request_id);

                // Update Customer the ticket has been closed
			    $message = new \Email\Message (
				    array (
					    'from'	=> 'service@spectrosinstruments.com',
					    'subject'	=> "[SUPPORT] A Ticket #" . $requestItem->id  . " on your request " . $supportRequest->code. " has been completed.",
					    'body'		=> "[SUPPORT] A Ticket #" . $requestItem->id  . " on your request " . $supportRequest->code. " has been completed."
				    )
			    );
			    $message->html(true);
                $request->customer->notify($message);
            }
        }

        // Event Occured Customer Ticket Notification
        $supportRequest = new \Support\Request($requestItem->request_id);
	    $message = new \Email\Message (
		    array (
			    'from'	=> 'service@spectrosinstruments.com',
			    'subject'	=> "[SUPPORT] An action #" . $action->item->id  . " on your request " . $supportRequest->code. " has been updated.",
			    'body'		=> "[SUPPORT] An action #" . $action->item->id  . " on your request " . $supportRequest->code. " has been updated."
		    )
	    );
	    $message->html(true);
        $request->customer->notify($message);
	}
	
	if (isset($_REQUEST['btn_assign_action'])) {
		$user = new \Register\Customer($_REQUEST['assigned_id']);
		
		if ($user->error) {
			$page->addError($user->error);
		} elseif (! $user->id) {
			$page->addError("Cannot find assigned user");
		} else {
			$action->update(array('assigned_id' => $user->id));
			if ($action->error()) {
				$page->addError($action->error());
			} else {
				$parameters = array(
					'action_id'		=> $action->id,
					'date_event'	=> $_REQUEST['date_event'],
					'user_id'		=> $GLOBALS['_SESSION_']->customer->id,
					'description'	=> "Action assigned to ".$user->full_name(),
					'hours_worked'	=> $_REQUEST['hours_worked']
				);
				if ($action->addEvent($parameters)) {
					if ($_REQUEST['status'] != $action->status) $action->update(array('status' => $_REQUEST['status']));
				} else {
					$page->addError($action->error());
				}
				$page->success = "[SUPPORT] Action Assigned to ".$user->full_name();
				$message = new \Email\Message(
					array(
						'from'	=> 'service@spectrosinstruments.com',
						'subject'	=> "Action ".$action->id." assigned to you",
						'body'		=> "The following action was assigned to you:<br/>
                                        Request: ".$action->item->request->code."<br/>
                                        Item: ".$action->item->line."<br/>
                                        Type: ".$action->type."<br/>
                                        Product: ".$action->item->product->code."<br/>
                                        Serial: ".$action->item->serial_number."<br/>
                                        Description: ".$action->description
					)
				);
				$user->notify($message);
			}
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
        $parameters['ref_id'] = $action->id;
	    $uploadResponse = $file->upload($parameters);
	    
	    if (!empty($file->error)) $page->addError($file->error);
	    if (!empty($file->success)) $page->success = $file->success;
	}
	
	$filesList = new \Storage\FileList();
	$filesUploaded = $filesList->find(array('type' => 'support action', 'ref_id' => $action->id));
	
	$adminlist = new \Register\CustomerList();
	$admins = $adminlist->find(array('role'=> 'support user','_sort' => 'name'));

	$eventlist = new \Support\Request\Item\Action\EventList();
	$events = $eventlist->find(array('action_id' => $action->id));
	
	if ($action->assignedTo->id) $assignedTo = $action->assignedTo->full_name();
	else $assignedTo = "Unassigned";
	if (! $action->description) $action->description = 'None provided';
	
	// if the action type is 'Contact Customer' or 'Remote Evaluation', display the contact information for the ticket requestor: email address, phone number, etc.
    $contactList = new \Register\ContactList();
    $contactInfo = array();
    if ($action->type == "Contact Customer" || $action->type == "Remote Evaluation") $contactInfo = $contactList->find(array('user_id'=> $request->customer->id));
