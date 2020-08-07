<?php
	$page = new \Site\Page();
	$page->requireAuth();
	
    // apply any filters
    $searchQuery = array('customer_id' => $GLOBALS ['_SESSION_']->customer->id);
    if (isset($_REQUEST['status']) && !empty($_REQUEST['status'])) $searchQuery['status'] = array($_REQUEST['status']);
    if (isset($_REQUEST['serial']) && !empty($_REQUEST['serial'])) $searchQuery['serial_number'] = $_REQUEST['serial'];
    if (isset($_REQUEST['toDate']) && !empty($_REQUEST['toDate'])) $searchQuery['max_date'] = $_REQUEST['toDate'];
    if (isset($_REQUEST['fromDate']) && !empty($_REQUEST['fromDate'])) $searchQuery['min_date'] = $_REQUEST['fromDate'];

    // apply requested sort
    if (isset($_REQUEST['sortBy']) && !empty($_REQUEST['sortBy'])) {
        if ($_REQUEST['sortBy'] == "date") $searchQuery['sort_by'] = "requested";
        if ($_REQUEST['sortBy'] == "serial") $searchQuery['sort_by'] = "serial";
        if ($_REQUEST['sortBy'] == "ticket") $searchQuery['sort_by'] = "ticket_id";
        if ($_REQUEST['sortBy'] == "status") $searchQuery['sort_by'] = "status";
        if ($_REQUEST['sortBy'] == "requestor") $searchQuery['sort_by'] = "requestor";
    }
    
	$supportItemRequest = new Support\Request\ItemList();
	$supportItems = $supportItemRequest->find($searchQuery);
	
