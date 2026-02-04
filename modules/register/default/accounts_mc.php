<?php
	#######################################################
	### accounts_mc.php									###
	### This view lists all account associated with a	###
	### provide set of filters.							###
	### A. Caravello 11/12/2002							###
	#######################################################

	// Initalize the Page
	$site = new \Site();
    $page = $site->page();
	$page->requirePrivilege("manage customers",\Register\PrivilegeLevel::ORGANIZATION_MANAGER);

	// Initialize Parameter Array
	$find_parameters = array();

	// Configure Pagination - See https://sites.google.com/rootseven.com/porkchop/content-management-system/pagination
    $pagination = new \Site\Page\Pagination();
    $pagination->forwardParameters(array('search','hidden','expired','blocked','deleted','sort_field','sort_direction'));

	// Security - Only Register Module Operators or Managers can see other customers
	$organization = $GLOBALS['_SESSION_']->customer->organization();
	if ($organization->exists()) {
		$find_parameters['organization_id'] = $organization->id;
	}
	else 
		return 403;

	$customerList = new \Register\CustomerList();

	if (!empty($_REQUEST['search']) && !preg_match('/^\*?[\w\-\.\_\s]+\*?$/',$_REQUEST['search'])) {
		$page->addError("Invalid search string");
		$_REQUEST['search'] = null;
	}

	$find_parameters['status'] = array();
	
	// If no filters are selected, show only NEW and ACTIVE by default
	if (empty($_REQUEST['deleted']) && empty($_REQUEST['expired']) && empty($_REQUEST['hidden']) && empty($_REQUEST['blocked'])) {
		$find_parameters['status'] = array('NEW', 'ACTIVE');
	} else {
		// Only add the statuses that are checked
		if (isset($_REQUEST['deleted']) && $_REQUEST['deleted'] == 1) array_push($find_parameters['status'], 'DELETED');
		if (isset($_REQUEST['expired']) && $_REQUEST['expired'] == 1) array_push($find_parameters['status'], 'EXPIRED');
		if (isset($_REQUEST['hidden']) && $_REQUEST['hidden'] == 1) array_push($find_parameters['status'], 'HIDDEN');
		if (isset($_REQUEST['blocked']) && $_REQUEST['blocked'] == 1) array_push($find_parameters['status'], 'BLOCKED');
	}
	if (isset($_REQUEST['search']) && strlen($_REQUEST['search'])) $find_parameters['_search'] = $_REQUEST['search'];

	// Get Count before Pagination
	$customerList->find($find_parameters,['ids' => true]);
	$totalRecords = $customerList->count();
    $pagination->count($totalRecords);

	$customers = $customerList->find($find_parameters,['limit'=>$pagination->size(),'offset'=>$pagination->startId]);
	if ($customerList->error()) $page->addError("Error finding customers: ".$customerList->error());

	$page->title = "Accounts";
	$page->setAdminMenuSection("Customer");  // Keep Customer section open
	$page->addBreadCrumb("Customer");
	$page->addBreadCrumb("Accounts","/_register/accounts");