<?php
	###################################################
	### organizations_mc.php						###
	### This program collects registration info		###
	### for the user.								###
	### A. Caravello 11/12/2002						###
	###################################################
	$page = new \Site\Page();
	$page->requirePrivilege('manage customers');

	// Customers to display at a time
	if (isset($_REQUEST['page_size']) && preg_match('/^\d+$/',$_REQUEST['page_size']))
		$organizations_per_page = $_REQUEST['page_size'];
	else
		$organizations_per_page = 20;
	if (isset($_REQUEST['start']) && ! preg_match('/^\d+$/',$_REQUEST['start'])) $_REQUEST['start'] = 0;

	// Security - Only Register Module Operators or Managers can see other customers
	$organizationlist = new \Register\OrganizationList();
	$organization = new \Register\Organization();

	// Initialize Parameter Array
	$find_parameters = array();
	if (isset($_REQUEST['name'])) {
		if ($organizationlist->validSearchString($_REQUEST['name'])) {
			$find_parameters['string'] = $_REQUEST['name'];
			$find_parameters['_like'] = array('name');
		}
		else {
			$page->addError("Invalid search string");
			$_REQUEST['name'] = noXSS($_REQUEST['name']);
		}
	}

	$find_parameters['status'] = array('NEW','ACTIVE');
	if (!empty($_REQUEST['deleted'])) array_push($find_parameters['status'],'DELETED');
	if (!empty($_REQUEST['expired'])) array_push($find_parameters['status'],'EXPIRED');
	if (!empty($_REQUEST['hidden'])) array_push($find_parameters['status'],'HIDDEN');
	if (!empty($_REQUEST['searchedTag'])) $find_parameters['searchedTag'] = $_REQUEST['searchedTag'];

	// Get Count before Pagination
	$organizationlist->search($find_parameters,true);
	$total_organizations = $organizationlist->count();
	if ($organizationlist->error()) $page->addError($organizationlist->error());

	// Add Pagination to Query
	$find_parameters["_limit"] = $organizations_per_page;
	$find_parameters["_offset"] = isset($_REQUEST['start']) ? $_REQUEST['start']: 0;

	// Get Records
	$organizations = $organizationlist->search($find_parameters,true);
	if ($organizationlist->error()) $page->addError("Error finding organizations: ".$organizationlist->error());

	if (!empty($_REQUEST['start'])) {
		if ($_REQUEST['start'] < $organizations_per_page) $prev_offset = 0;
		else $prev_offset = $_REQUEST['start'] - $organizations_per_page;
	}
	$next_offset = $_REQUEST['start'] + $organizations_per_page + 1;
	if ($next_offset > $total_organizations - $organizations_per_page) $next_offset = $total_organizations - $organizations_per_page;
	if ($total_organizations >= $organization_per_page) $last_offset = $total_organizations - $organizations_per_page;
	else $last_offset = 0;

    // get tags for organization
    $registerTagList = new \Register\TagList();
    $organizationTags = $registerTagList->getDistinct();
	if ($registerTagList->error()) $page->addError($registerTagList->error());

	if (is_array($organizations) && $next_offset > count($organizations)) $next_offset = (isset($_REQUEST['start']) ? $_REQUEST['start'] : 0) + count($organizations);

	$page->title = "Organizations";
	$page->instructions = "Fill in the search field.  Use * for a wildcard.  Or click an organization code to see details.";