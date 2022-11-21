<?php
	$page = new \Site\Page();
	$page->fromRequest();
	$page->requirePrivilege('manage registrations');

    // get current registrations
	$registrationQueueList = new \Support\RegistrationQueueList();

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

    // update warranty notes from UI request
    app_log("updateNotes pending registrations");
	if ($_REQUEST['action'] == 'updateNotes') {
	    $queuedRegistration = new Support\RegistrationQueue($_REQUEST['id']);
	    $queuedRegistration->update(array('notes' => $_REQUEST['notes']));
        $page->success = true;
	}

    // update warranty status from UI request
    app_log("updateStatus");
	if ($_REQUEST['action'] == 'updateStatus') {
	    $queuedRegistration = new Support\RegistrationQueue($_REQUEST['id']);	    
	    $queuedRegistration->update(array('status' => $_REQUEST['status']));
        $page->success = true;
	}

    // get queued warranty list based on search
    app_log("QueueList for warranty");
    $queuedCustomers = new Support\RegistrationQueueList();
    $dateStart = '';
    $dateEnd = '';
    $statusFiltered = array();

    // process form posted filters for results
    app_log("Filters for warranty registration");
    if ($_REQUEST['VERIFYING']) $statusFiltered[] = $_REQUEST['VERIFYING'];
    if ($_REQUEST['PENDING']) $statusFiltered[] = $_REQUEST['PENDING'];
    if ($_REQUEST['APPROVED']) $statusFiltered[] = $_REQUEST['APPROVED'];
    if ($_REQUEST['DENIED']) $statusFiltered[] = $_REQUEST['DENIED'];
    if ($_REQUEST['dateStart']) $dateStart = $_REQUEST['dateStart'];
    if ($_REQUEST['dateEnd']) $dateEnd = $_REQUEST['dateEnd'];

    // set to default of no options selected
    if (empty($statusFiltered)) $_REQUEST['PENDING'] = $statusFiltered[] = 'PENDING';

    // get results
    app_log("Find Pending Product Registrations");
    $queuedProductRegistration = $registrationQueueList->find( 
        array(
            'status' => $statusFiltered, 
            'dateStart'=> $dateStart,
            'dateEnd'=> $dateEnd
        )
    );
