 <?php
	###################################################
	### accounts_mc.php								###
	### This program collects registration info		###
	### for the user.								###
	### A. Caravello 11/12/2002						###
	###################################################
    $site = new \Site();
    $page = $site->page();
	// $page = new \Site\Page();
	$page->requirePrivilege("manage customers");

	// Initialize Parameter Array
	$find_parameters = array();

	// Customers to display at a time
	if (is_numeric($_REQUEST['recordsPerPage']))
		$recordsPerPage = $_REQUEST['recordsPerPage'];
	else
		$recordsPerPage = 20;

	if (isset($_REQUEST['start']) && ! preg_match('/^\d+$/',$_REQUEST['start'])) $_REQUEST['start'] = 0;

	// Security - Only Register Module Operators or Managers can see other customers
	if ($GLOBALS['_SESSION_']->customer->can('manage customers')) {
		// Ok
	}
	elseif (!empty($GLOBALS['_SESSION_']->customer->organization()->id))
		$find_parameters['organization_id'] = $GLOBLAS['_SESSION_']->customer->organization()->id;
	else 
		return 403;

	$customerList = new \Register\CustomerList();

	if (!empty($_REQUEST['search']) && !preg_match('/^\*?[\w\-\.\_\s]+\*?$/',$_REQUEST['search'])) {
		$page->addError("Invalid search string");
		$_REQUEST['search'] = null;
	}

	$find_parameters['status'] = array('NEW','ACTIVE');
	if (isset($_REQUEST['deleted']) && $_REQUEST['deleted'] == 1) array_push($find_parameters['status'],'DELETED');
	if (isset($_REQUEST['expired']) && $expired['deleted'] == 1) array_push($find_parameters['status'],'EXPIRED');
	if (isset($_REQUEST['hidden']) && $_REQUEST['hidden'] == 1) array_push($find_parameters['status'],'HIDDEN');
	if (isset($_REQUEST['blocked']) && $_REQUEST['blocked'] == 1) array_push($find_parameters['status'],'BLOCKED');
	if (isset($_REQUEST['search']) && strlen($_REQUEST['search'])) $find_parameters['_search'] = $_REQUEST['search'];

	// Get Count before Pagination
	$customerList->find($find_parameters,array('count' => true));
	$total_customers = $customerList->count();

	// Add Pagination to Query
	$controls["limit"] = $recordsPerPage;
	$controls["offset"] = isset($_REQUEST['pagination_start_id']) ? $_REQUEST['pagination_start_id']: 0;

    $totalRecords = $customerList->count();
	$customers = $customerList->find($find_parameters,$controls);
	if ($customerList->error()) $page->addError("Error finding customers: ".$customerList->error());

	if (isset($_REQUEST['start']) && $_REQUEST['start'] < $recordsPerPage)
		$prev_offset = 0;
	else
		$prev_offset = $_REQUEST['start'] - $recordsPerPage;
	$next_offset = $_REQUEST['start'] + $recordsPerPage;
	app_log("$total_customers - $recordsPerPage",'trace',__FILE__,__LINE__);
	$last_offset = $total_customers - $recordsPerPage;

	$page->title = "Accounts";

	// paginate results
    $pagination = new \Site\Page\Pagination();
    $pagination->forwardParameters(array('search','hidden','expired','blocked','deleted','sort_field','sort_direction','recordsPerPage'));
    $pagination->size($recordsPerPage);
    $pagination->count($totalRecords);
