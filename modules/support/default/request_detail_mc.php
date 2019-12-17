<?php
	$page = new \Site\Page();
	$page->fromRequest();
	$page->requireRole('support user');

	if ($_REQUEST['code']) {
		$request = new \Support\Request();
		$request->get($_REQUEST['code']);
	} elseif ($_REQUEST['id']) {
		$request = new \Support\Request($_REQUEST['id']);
	} else {
		$request = new \Support\Request();
		$request->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
	}
	
	if ($_REQUEST['btn_add_item']) {
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
    
    // get the comments 
    $supportItemComments = array();
    foreach ($itemRequestActionIds as $itemRequestID) {
	    $commentlist = new \Support\Request\Item\CommentList();
	    $supportItemComments[] = $commentlist->find(array('item_id' => $itemRequestID));
    }
