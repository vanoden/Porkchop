<?php
	###################################################
	### organizations_mc.php						###
	### This program collects registration info		###
	### for the user.								###
	### A. Caravello 11/12/2002						###
	###################################################
	$page = new \Site\Page();
	$page->requirePrivilege('manage customers');

	# Configure Pagination
    $pagination = new \Site\Page\Pagination();
    $pagination->forwardParameters(array('hidden','deleted','expired','name','searchedTag'));

	// Security - Only Register Module Operators or Managers can see other customers
	$organizationlist = new \Register\OrganizationList();
	$organization = new \Register\Organization();

	// Initialize Parameter Array
	$find_parameters = array();
	if (isset($_REQUEST['name'])) {
		$find_parameters['name'] = $_REQUEST['name'];
		$controls['like'] = array('name');
	}

	// Initialize status array based on checkboxes
	$find_parameters['status'] = array();
	
	// If no filters are selected, show only NEW and ACTIVE by default
	if (empty($_REQUEST['deleted']) && empty($_REQUEST['expired']) && empty($_REQUEST['hidden'])) {
		$find_parameters['status'] = array('NEW', 'ACTIVE');
	} else {
		// Only add the statuses that are checked
		if (isset($_REQUEST['deleted']) && $_REQUEST['deleted'] == 1) array_push($find_parameters['status'], 'DELETED');
		if (isset($_REQUEST['expired']) && $_REQUEST['expired'] == 1) array_push($find_parameters['status'], 'EXPIRED');
		if (isset($_REQUEST['hidden']) && $_REQUEST['hidden'] == 1) array_push($find_parameters['status'], 'HIDDEN');
	}
	
	if (!empty($_REQUEST['searchedTag'])) $find_parameters['searchedTag'] = $_REQUEST['searchedTag'];

	// Get Count before Pagination
	$organizationlist->find($find_parameters,['ids' => true]);
	$total_organizations = $organizationlist->count($find_parameters);
	if ($organizationlist->error()) $page->addError($organizationlist->error());

	// Add Pagination to Query
	$controls["limit"] = $pagination->size();
	$controls["offset"] = $pagination->startId();

	// Get Records
	$organizations = $organizationlist->find($find_parameters,$controls);
	if ($organizationlist->error()) $page->addError("Error finding organizations: ".$organizationlist->error());

    // get tags for organization
    $registerTagList = new \Register\TagList();
    $organizationTags = $registerTagList->getDistinct();
	if ($registerTagList->error()) $page->addError($registerTagList->error());

	//if (is_array($organizations) && $next_offset > count($organizations)) $next_offset = (isset($_REQUEST['start']) ? $_REQUEST['start'] : 0) + count($organizations);

	$page->title("Organizations");
	$page->instructions = "Fill in the search field.  Use * for a wildcard.  Or click an organization code to see details.";
    $page->addBreadcrumb("Customer");
    $page->addBreadcrumb("Organizations","/_register/organizations");

    $pagination->count($total_organizations);