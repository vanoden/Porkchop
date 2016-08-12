<?PHP
	###################################################
	### organizations_mc.php						###
	### This program collects registration info		###
	### for the user.								###
	### A. Caravello 11/12/2002						###
	###################################################

	# Customers to display at a time
	if (preg_match('/^\d+$/',$_REQUEST['page_size']))
		$organizations_per_page = $_REQUEST['page_size'];
	else
		$organizations_per_page = 18;

	# Security - Only Register Module Operators or Managers can see other customers
	if (role('register reporter') or role('register manager'))
	{
		if (! preg_match('/^\d+$/',$_REQUEST['start'])) $_REQUEST['start'] = 0;
		$_organization = new RegisterOrganization();
		
		# Initialize Parameter Array
		$find_parameters = array();
		if ($_REQUEST['name'])
		{
			$find_parameters['name'] = $_REQUEST['name'];
			$find_parameters['_like'] = array('name');
		}
		# Get Count before Pagination
		$total_organizations = $_organization->count($find_parameters);
		
		# Add Pagination to Query
		$find_parameters["_limit"] = $organizations_per_page;
		$find_parameters["_offset"] = $_REQUEST['start'];

		# Get Records
		$organizations = $_organization->find($find_parameters);
		if ($_organization->error) $GLOBALS['_page']->error = "Error finding organizations: ".$_organization->error;
		
		if ($_REQUEST['start'] < $organizations_per_page)
			$prev_offset = 0;
		else
			$prev_offset = $_REQUEST['start'] - $organizations_per_page;
		$next_offset = $_REQUEST['start'] + $organizations_per_page;
		$last_offset = $total_organizations - $organizations_per_page;
		
		if ($next_offset > count($organizations)) $next_offset = $_REQUEST['start'] + count($organizations);
	}
	else $organizations = array();
?>