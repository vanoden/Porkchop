<?php
	$page = new \Site\Page();
	$page->fromRequest();
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
	if ($_REQUEST['action'] == 'updateNotes') {
	    $queuedCustomer = new Register\Queue($_REQUEST['id']);
	    $queuedCustomer->update(array('notes' => $_REQUEST['notes']));
        $page->success = true;
	}
	
    // update customer status from UI request
	if ($_REQUEST['action'] == 'updateStatus') {
	    $queuedCustomer = new Register\Queue($_REQUEST['id']);	    
	    $queuedCustomer->update(array('status' => $_REQUEST['status']));
        $page->success = true;
	}
	
    // assign customer and/or generate new organization if needed
	if ($_REQUEST['action'] == 'denyCustomer') {
	    $queuedCustomer = new Register\Queue($_REQUEST['id']);	    
	    $queuedCustomer->update(array('status' => 'DELETED'));
        $page->success = true;
	}
	
    // assign customer and/or generate new organization if needed
	if ($_REQUEST['action'] == 'assignCustomer') {
	    $queuedCustomer = new Register\Queue($_REQUEST['id']);	    
	    $queuedCustomer->update(array('status' => 'ACTIVE'));
	    $queuedCustomer->syncLiveAccount();
        $page->success = true;
	}
	
    // get queued customers based on search
    $queuedCustomers = new Register\QueueList();
    $searchTerm = '';
    $dateStart = '';
    $dateEnd = '';
    $statusFiltered = array();
    
    // process form posted filters for results
    if ($_REQUEST['VERIFYING']) $statusFiltered[] = $_REQUEST['VERIFYING'];
    if ($_REQUEST['PENDING']) $statusFiltered[] = $_REQUEST['PENDING'];
    if ($_REQUEST['APPROVED']) $statusFiltered[] = $_REQUEST['APPROVED'];
    if ($_REQUEST['DENIED']) $statusFiltered[] = $_REQUEST['DENIED'];
    if ($_REQUEST['search']) $searchTerm = $_REQUEST['search'];
    if ($_REQUEST['dateStart']) $dateStart = $_REQUEST['dateStart'];
    if ($_REQUEST['dateEnd']) $dateEnd = $_REQUEST['dateEnd'];
    
    // set to default of no options selected
    if (empty($statusFiltered)) {
        $_REQUEST['VERIFYING'] = $statusFiltered[] = 'VERIFYING';
        $_REQUEST['PENDING'] = $statusFiltered[] = 'PENDING';
    }
    
    // get results
    $queuedCustomersList = $queuedCustomers->find(
        array(
            'searchAll'=> $searchTerm,
            'status' => $statusFiltered, 
            'dateStart'=> $dateStart,
            'dateEnd'=> $dateEnd
        )
    );
