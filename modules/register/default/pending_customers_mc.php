<?php
	$page = new \Site\Page();
	$page->requireRole('support user');
	
	/**
	 * get color codes HEX for given queued customer status
	 * @param $status
	 */
	function colorCodeStatus($status) {
	    $color =  "#28a745";
	    switch ($status) {
	        case 'VERIFYING':
	            $color =  "#007bff";
	            break;
	        case 'PENDING':
	            $color =  "#28a745";
	            break;
	        case 'APPROVED':
	            $color =  "#333333";
	            break;
	        case 'DENIED':
	            $color =  "#dc3545";
	            break;
	        default:
	            $color =  "#28a745";
	            break;
	    }
	    return $color;
	}

    // update customer notes from UI request
    app_log("updateNotes");
	if ( isset($_REQUEST['action']) && $_REQUEST['action'] == 'updateNotes') {
	    $queuedCustomer = new Register\Queue($_REQUEST['id']);
	    $queuedCustomer->update(array('notes' => $_REQUEST['notes']));
        $page->success = true;
	}

    // update customer status from UI request
    app_log("updateStatus");
	if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'updateStatus') {
	    $queuedCustomer = new Register\Queue($_REQUEST['id']);	    
	    $queuedCustomer->update(array('status' => $_REQUEST['status']));
	    if ($_REQUEST['status'] == 'APPROVED')$queuedCustomer->syncLiveAccount();
        $page->success = true;
	}

    // assign customer and/or generate new organization if needed
    app_log("denyCustomer");
	if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'denyCustomer') {
	    $queuedCustomer = new Register\Queue($_REQUEST['id']);	    
	    $queuedCustomer->update(array('status' => 'DENIED'));
        $page->success = true;
	}

    // assign customer and/or generate new organization if needed
    app_log("assignCustomer");
	if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'assignCustomer') {
	    $queuedCustomer = new Register\Queue($_REQUEST['id']);	    
	    $queuedCustomer->update(array('status' => 'APPROVED'));
	    $queuedCustomer->syncLiveAccount();
        $page->success = true;
	}	

    // get queued customers based on search
    app_log("QueueList");
    $queuedCustomers = new Register\QueueList();
    $searchTerm = '';
    $dateStart = '';
    $dateEnd = '';
    $statusFiltered = array();

    // process form posted filters for results
    app_log("Filters");
    if (isset($_REQUEST['VERIFYING'])) $statusFiltered[] = $_REQUEST['VERIFYING'];
    if (isset($_REQUEST['PENDING'])) $statusFiltered[] = $_REQUEST['PENDING'];
    if (isset($_REQUEST['APPROVED'])) $statusFiltered[] = $_REQUEST['APPROVED'];
    if (isset($_REQUEST['DENIED'])) $statusFiltered[] = $_REQUEST['DENIED'];
    if (isset($_REQUEST['search'])) $searchTerm = $_REQUEST['search'];
    if (isset($_REQUEST['dateStart'])) $dateStart = $_REQUEST['dateStart'];
    if (isset($_REQUEST['dateEnd'])) $dateEnd = $_REQUEST['dateEnd'];
    
    // set to default of no options selected
    if (empty($statusFiltered)) $_REQUEST['PENDING'] = $statusFiltered[] = 'PENDING';

    // get results
    app_log("Find");
    $queuedCustomersList = $queuedCustomers->find(
        array(
            'searchAll'=> $searchTerm,
            'status' => $statusFiltered, 
            'dateStart'=> $dateStart,
            'dateEnd'=> $dateEnd
        )
    );
