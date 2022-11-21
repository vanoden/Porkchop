<?php
    $page = new \Site\Page();
    $page->isSearchResults = true;
	$page->fromRequest();
    $page->requirePrivilege('see support requests');

    // clean user input and search away
    $searchTerm = preg_replace("/[^A-Za-z0-9\- ]/", '', $_REQUEST['search']);    
    $searchTermArray = array('searchTerm'=>$searchTerm);

    // search service items that match
    $supportRequestList = new \Support\RequestList();
    $supportRequestList = $supportRequestList->find($searchTermArray);
    $supportItemList = new Support\Request\ItemList();
    $supportItemList = $supportItemList->find($searchTermArray);   
	$actionlist = new \Support\Request\Item\ActionList();
	$actions = $actionlist->find($searchTermArray);
    $customerList = new \Register\CustomerList();
    $customers = $customerList->find($searchTermArray);

	// get current registrations
	$registrationQueueList = new \Support\RegistrationQueueList();

    // get results
    app_log("Find Pending Product Registrations");
    $queuedProductRegistrations = $registrationQueueList->find($searchTermArray);
