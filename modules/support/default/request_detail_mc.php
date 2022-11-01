<?php
	$page = new \Site\Page();
	$page->fromRequest();
	$page->requirePrivilege('browse support tickets');

	if ($_REQUEST['code']) {
		$request = new \Support\Request();
		$request->get($_REQUEST['code']);
	} elseif ($_REQUEST['id']) {
		$request = new \Support\Request($_REQUEST['id']);
	} else {
		$request = new \Support\Request();
		$request->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
	}

    // flag if we have an actual request or not to show for the UI
    $hasRequest = true;
	if (empty($request->id)) $hasRequest = false;
	if ($_REQUEST['btn_add_item']) {
	    if (!empty($_REQUEST['serial_number']) && !empty($_REQUEST['item_description'])) {
            $parameters = array(
	            'line'			=> $request->nextLine(),
	            'product_id'	=> $_REQUEST['product_id'],
	            'serial_number'	=> $_REQUEST['serial_number'],
	            'status'		=> $_REQUEST['item_status'],
	            'description'	=> $_REQUEST['item_description'],
	            'quantity'		=> 0
            );
		    $request->addItem($parameters);
		    if ($request->error()) $page->addError($request->error());
	    } else {
    	    $page->addError('Serial Number and Description required for adding a ticket');
	    }
	}

	if ($_REQUEST['btn_cancel']) {
		if ($request->openItems() > 0) {
			$page->addError("Request still has open items!");
		} else {
			$request->update(array('status' => 'CANCELLED'));
			if ($request->error()) $page->addError($request->error());
		}
	}
	if ($_REQUEST['btn_close']) {
		if ($request->openItems() > 0) {
			$page->addError("Request still has open items!");
		} else {
			$request->update(array('status' => 'CLOSED'));
			if ($request->error()) $page->addError($request->error());
		}
	}

	if ($_REQUEST['btn_reopen']) $request->update(array('status' => 'OPEN'));
    if ($hasRequest) {
	    $items = $request->items();

	    $productlist = new \Product\ItemList();
	    $products = $productlist->find(array('type' => array('inventory','kit','unique')));

	    $item = new \Support\Request\Item();
	    $statuses = $item->statuses();

	    $adminlist = new \Register\CustomerList();
	    $admins = $adminlist->find(array('role' => 'support user','_sort' => 'full_name'));
        
        // get all the actions related to this request
        $actionlist = new \Support\Request\Item\ActionList();
        $itemRequestActionIds = array();
        foreach ($items as $itemRequest) $itemRequestActionIds[] = $itemRequest->id;
        $actions = array();
        if (!empty($itemRequestActionIds)) $actions = $actionlist->find(array('searchAllItems'=> true, 'itemIds' => $itemRequestActionIds ));
        
        // get the events for an action, to display as an expandable list
        $events = array();  
        foreach ($actions as $action) {
	        $eventlist = new \Support\Request\Item\Action\EventList();
	        $events[$action->id] = $eventlist->find(array('action_id' => $action->id));
        }

        // get the comments
        $supportItemComments = array();
        foreach ($itemRequestActionIds as $itemRequestID) {
	        $commentlist = new \Support\Request\Item\CommentList();
	        $supportItemComments[] = $commentlist->find(array('item_id' => $itemRequestID));
        }
    } else {
        $items = array();
        $actions = array();
        $page->addError('Request could not be found');
    }
    
    // upload files if upload button is pressed
    $configuration = new \Site\Configuration('support_attachments_s3');
    $repository = $configuration->value();
    if (isset($_REQUEST['btn_submit']) && $_REQUEST['btn_submit'] == 'Upload') {

	    $file = new \Storage\File();
	    $parameters = array();
        $parameters['repository_name'] = $_REQUEST['repository_name'];
        $parameters['type'] = $_REQUEST['type'];
        $parameters['ref_id'] = $request->id;
	    $uploadResponse = $file->upload($parameters);
	    
	    if (!empty($file->error)) $page->addError($file->error);
	    if (!empty($file->success)) $page->success = $file->success;
	}
	
	$filesList = new \Storage\FileList();
	$filesUploaded = $filesList->find(array('type' => 'support request', 'ref_id' => $request->id));
