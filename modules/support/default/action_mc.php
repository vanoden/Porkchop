<?php
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
	
	if (isset($_REQUEST['btn_add_event'])) {
		if ($_REQUEST['status'] != $action->status) $_REQUEST['description'] .= "<br>Status changed from ".$action->status." to ".$_REQUEST['status'];
		$action->update(array('status' => 'ACTIVE'));
		$parameters = array(
			'action_id'		=> $action->id,
			'date_event'	=> $_REQUEST['date_event'],
			'user_id'		=> $_REQUEST['user_id'],
			'description'	=> $_REQUEST['description']
		);
		
		if ($action->addEvent($parameters)) {
			$page->success = "Event created";
			if ($_REQUEST['status'] != $action->status) $action->update(array('status' => $_REQUEST['status']));
		} else {
			$page->addError($action->error());
		}
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
					'description'	=> "Action assigned to ".$user->full_name()
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
