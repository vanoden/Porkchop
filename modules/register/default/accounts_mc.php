 <?php
	###################################################
	### accounts_mc.php								###
	### This program collects registration info		###
	### for the user.								###
	### A. Caravello 11/12/2002						###
	###################################################
	$page = new \Site\Page();
	$page->requirePrivilege("manage customers");

	// Initialize Parameter Array
	$find_parameters = array();

	// Customers to display at a time
	if (isset($_REQUEST['page_size']) && preg_match('/^\d+$/',$_REQUEST['page_size']))
		$customers_per_page = $_REQUEST['page_size'];
	else
		$customers_per_page = 15;

	if (isset($_REQUEST['start']) && ! preg_match('/^\d+$/',$_REQUEST['start'])) $_REQUEST['start'] = 0;

	// Security - Only Register Module Operators or Managers can see other customers
	if ($GLOBALS['_SESSION_']->customer->can('manage customers')) {
		// Ok
	}
	elseif (!empty($GLOBALS['_SESSION_']->customer->organization->id))
		$find_parameters['organization_id'] = $GLOBLAS['_SESSION_']->customer->organization->id;
	else 
		return 403;

	$customer_list = new \Register\CustomerList();

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
	$customer_list->find($find_parameters,true);
	$total_customers = $customer_list->count;

	// Apply Pagination and Get Records
	$find_parameters["_limit"] = $customers_per_page;
	$find_parameters["_offset"] = isset($_REQUEST['start']) ? $_REQUEST['start'] : 0;
	$customers = $customer_list->find($find_parameters);
	if ($customer_list->error) $page->error = "Error finding customers: ".$customer_list->error;

	if (isset($_REQUEST['start']) && $_REQUEST['start'] < $customers_per_page)
		$prev_offset = 0;
	else
		$prev_offset = $_REQUEST['start'] - $customers_per_page;
	$next_offset = $_REQUEST['start'] + $customers_per_page;
	app_log("$total_customers - $customers_per_page",'trace',__FILE__,__LINE__);
	$last_offset = $total_customers - $customers_per_page;
