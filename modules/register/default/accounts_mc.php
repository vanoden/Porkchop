 <?php
	###################################################
	### accounts_mc.php								###
	### This program collects registration info		###
	### for the user.								###
	### A. Caravello 11/12/2002						###
	###################################################
	$page = new \Site\Page('register','accounts');
	$page->requireRole('administrator');

	# Customers to display at a time
	if (isset($_REQUEST['page_size']) && preg_match('/^\d+$/',$_REQUEST['page_size']))
		$customers_per_page = $_REQUEST['page_size'];
	else
		$customers_per_page = 15;
	if (! preg_match('/^\d+$/',$_REQUEST['start'])) $_REQUEST['start'] = 0;

	# Security - Only Register Module Operators or Managers can see other customers
	if ($GLOBALS['_SESSION_']->customer->has_role('register reporter') or $GLOBALS['_SESSION_']->customer->has_role('register manager')) {
		$customer_list = new \Register\CustomerList();

		# Initialize Parameter Array
		$find_parameters = array();
		
		$find_parameters['status'] = array('NEW','ACTIVE');
		if ($_REQUEST['deleted']) array_push($find_parameters['status'],'DELETED');
		if ($_REQUEST['expired']) array_push($find_parameters['status'],'EXPIRED');
		if ($_REQUEST['hidden']) array_push($find_parameters['status'],'HIDDEN');
		if (isset($_REQUEST['search']) && strlen($_REQUEST['search'])) $find_parameters['_search'] = $_REQUEST['search'];

		# Get Count before Pagination
		$customer_list->find($find_parameters,true);
		$total_customers = $customer_list->count;

		# Apply Pagination and Get Records
		$find_parameters["_limit"] = $customers_per_page;
		$find_parameters["_offset"] = $_REQUEST['start'];
		$customers = $customer_list->find($find_parameters);
		if ($customer_list->error) $page->error = "Error finding customers: ".$customer_list->error;
		
		if ($_REQUEST['start'] < $customers_per_page)
			$prev_offset = 0;
		else
			$prev_offset = $_REQUEST['start'] - $customers_per_page;
		$next_offset = $_REQUEST['start'] + $customers_per_page;
		app_log("$total_customers - $customers_per_page",'trace',__FILE__,__LINE__);
		$last_offset = $total_customers - $customers_per_page;
	}
	else {
		$page->error = "You are not authorized to see this view";
		$customers = array();
	}
