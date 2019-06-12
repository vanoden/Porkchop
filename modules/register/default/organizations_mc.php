<?PHP
	###################################################
	### organizations_mc.php						###
	### This program collects registration info		###
	### for the user.								###
	### A. Caravello 11/12/2002						###
	###################################################
	$page = new \Site\Page();
	$page->requireRole('administrator');

	# Customers to display at a time
	if (preg_match('/^\d+$/',$_REQUEST['page_size']))
		$organizations_per_page = $_REQUEST['page_size'];
	else
		$organizations_per_page = 18;
	if (! preg_match('/^\d+$/',$_REQUEST['start'])) $_REQUEST['start'] = 0;

	# Security - Only Register Module Operators or Managers can see other customers
	if ($GLOBALS['_SESSION_']->customer->has_role('register reporter') || $GLOBALS['_SESSION_']->customer->has_role('register manager')) {
		$organizationlist = new \Register\OrganizationList();

		# Initialize Parameter Array
		$find_parameters = array();
		if ($_REQUEST['name']) {
			$find_parameters['name'] = $_REQUEST['name'];
			$find_parameters['_like'] = array('name');
		}
		
		$find_parameters['status'] = array('NEW','ACTIVE');
		if ($_REQUEST['deleted']) array_push($find_parameters['status'],'DELETED');
		if ($_REQUEST['expired']) array_push($find_parameters['status'],'EXPIRED');
		if ($_REQUEST['hidden']) array_push($find_parameters['status'],'HIDDEN');

		# Get Count before Pagination
		$organizationlist->find($find_parameters,false);
		$total_organizations = $organizationlist->count;

		# Add Pagination to Query
		$find_parameters["_limit"] = $organizations_per_page;
		$find_parameters["_offset"] = $_REQUEST['start'];

		# Get Records
		$organizations = $organizationlist->find($find_parameters);
		if ($organizationlist->error) $GLOBALS['_page']->error = "Error finding organizations: ".$organizationlist->error;

		if ($_REQUEST['start'] < $organizations_per_page)
			$prev_offset = 0;
		else
			$prev_offset = $_REQUEST['start'] - $organizations_per_page;
		$next_offset = $_REQUEST['start'] + $organizations_per_page;
		$last_offset = $total_organizations - $organizations_per_page;

		if ($next_offset > count($organizations)) $next_offset = $_REQUEST['start'] + count($organizations);
	}
	else {
		$page->error = "You are not permitted to see this view";
		$organizations = array();
	}
?>
