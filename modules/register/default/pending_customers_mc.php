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
	        case 'NEW':
	            $color =  "#28a745";
	            break;
	        case 'ACTIVE':
	            $color =  "#007bff";
	            break;
	        case 'EXPIRED':
	            $color =  "#333333";
	            break;
	        case 'HIDDEN':
	            $color =  "#999999";
	            break;
	        case 'DELETED':
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
	
    // get queued customers based on search
    $queuedCustomers = new Register\QueueList();
    $searchTerm = '';
    if ($_REQUEST['search']) $searchTerm = $_REQUEST['search'];
    $queuedCustomersList = $queuedCustomers->find(
        array(
            'searchAll'=> $searchTerm, 
        )
    );
