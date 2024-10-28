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
	$page->requirePrivilege("manage customers");

	// Initialize Parameter Array
	$find_parameters = array();

	// Customers to display at a time
	$recordsPerPage = 20;
	$startId = 0;

	// Check for Pagination Parameters
	if (array_key_exists('recordsPerPage',$_REQUEST) && is_numeric($_REQUEST['recordsPerPage'])) $recordsPerPage = $_REQUEST['recordsPerPage'];
	if (array_key_exists('pagination_start_id',$_REQUEST) && is_numeric($_REQUEST['pagination_start_id'])) $startId = $_REQUEST['pagination_start_id'];

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
	$customers = $customerList->find($find_parameters,array('limit'=>$recordsPerPage,'offset'=>$startId));
    $totalRecords = $customerList->count();
	if ($customerList->error()) $page->addError("Error finding customers: ".$customerList->error());

	if ($startId < $recordsPerPage) $prev_offset = 0;
	else $prev_offset = $_REQUEST['start'] - $recordsPerPage;

	$next_offset = $_REQUEST['start'] + $recordsPerPage;
	$last_offset = $totalRecords - $recordsPerPage;

	// paginate results
    $pagination = new \Site\Page\Pagination();
	$pagination->startId($startId);
    $pagination->forwardParameters(array('search','hidden','expired','blocked','deleted','sort_field','sort_direction','recordsPerPage'));
    $pagination->size($recordsPerPage);
    $pagination->count($totalRecords);

	$page->title = "Accounts";