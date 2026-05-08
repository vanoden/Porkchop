<?php
	###################################################
	### organizations_report_mc.php					###
	### This program finds duplicate organizations	###
	### based on normalized name matching			###
	###################################################
	$page = new \Site\Page();
	$page->requirePrivilege('manage customers');

	// Default parameters
	$match_length = isset($_REQUEST['match_length']) ? intval($_REQUEST['match_length']) : 10;
	$min_matches = isset($_REQUEST['min_matches']) ? intval($_REQUEST['min_matches']) : 2;
	$match_string = isset($_REQUEST['match_string']) ? $_REQUEST['match_string'] : null;

	// Validate parameters
	if ($match_length < 1 || $match_length > 50) {
		$page->addError("Match length must be between 1 and 50");
		$match_length = 10;
	}
	if ($min_matches < 2 || $min_matches > 100) {
		$page->addError("Minimum matches must be between 2 and 100");
		$min_matches = 2;
	}

	$duplicate_groups = array();
	$organizations = array();
	
	// Use OrganizationList class methods to get data
	$organizationList = new \Register\OrganizationList();
	
	if ($match_string) {
		// Drill down into specific match string
		$organizations = $organizationList->findByMatchString($match_string, $match_length);
		if ($organizations === null) {
			if ($organizationList->error()) {
				$page->addError("Error finding organizations: " . $organizationList->error());
			} else {
				$page->addError("Error finding organizations");
			}
			$organizations = array();
		}
	} else {
		// Get list of duplicate groups
		$duplicate_groups = $organizationList->findDuplicateGroups($match_length, $min_matches);
		if ($duplicate_groups === null) {
			if ($organizationList->error()) {
				$page->addError("Error finding duplicates: " . $organizationList->error());
			} else {
				$page->addError("Error finding duplicates");
			}
			$duplicate_groups = array();
		}
	}

	$page->title("Organizations Duplicate Report");
	$page->setAdminMenuSection("Customer");  // Keep Customer section open
	$page->instructions = "Find duplicate organizations based on normalized name matching. Adjust the match length and minimum matches to refine results.";
	$page->addBreadcrumb("Customer");
	$page->addBreadcrumb("Organizations","/_register/organizations");
	$page->addBreadcrumb("Duplicate Report","/_register/organizations_report");
