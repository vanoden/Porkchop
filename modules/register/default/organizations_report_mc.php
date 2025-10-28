<?php
	###################################################
	### organizations_report_mc.php					###
	### This program finds duplicate organizations	###
	### based on normalized name matching			###
	###################################################
	$page = new \Site\Page();
	$page->requirePrivilege('manage customers');

	// Initialize Database Service
	$database = new \Database\Service();

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

	if ($match_string) {
		// Drill down into specific match string
		$get_organizations_query = "
			SELECT	id, name, code, status, date_created,
					(SELECT COUNT(*) FROM users WHERE organization_id = register_organizations.id) as user_count
			FROM	register_organizations
			WHERE	status = 'ACTIVE'
			AND		SUBSTRING(REGEXP_REPLACE(REGEXP_REPLACE(LOWER(name),'&','and'),'[\\s\\.\\-\\;\\:]',''),1,?) = ?
			ORDER BY name
		";
		
		$rs = $database->Execute($get_organizations_query, array($match_length, $match_string));
		if ($database->ErrorMsg()) {
			$page->addError("Error finding organizations: " . $database->ErrorMsg());
		} else {
			while ($row = $rs->FetchRow()) {
				$organizations[] = array(
					'id' => $row[0],
					'name' => $row[1],
					'code' => $row[2],
					'status' => $row[3],
					'date_created' => $row[4],
					'user_count' => $row[5]
				);
			}
		}
	} else {
		// Get list of duplicate groups
		$get_duplicates_query = "
			SELECT	COUNT(*) as match_count,
					SUBSTRING(REGEXP_REPLACE(REGEXP_REPLACE(LOWER(name),'&','and'),'[\\s\\.\\-\\;\\:]',''),1,?) as nickname
			FROM	register_organizations
			WHERE	status = 'ACTIVE'
			GROUP BY nickname
			HAVING COUNT(*) >= ?
			ORDER BY match_count DESC, nickname
		";
		
		$rs = $database->Execute($get_duplicates_query, array($match_length, $min_matches));
		if ($database->ErrorMsg()) {
			$page->addError("Error finding duplicates: " . $database->ErrorMsg());
		} else {
			while ($row = $rs->FetchRow()) {
				$duplicate_groups[] = array(
					'match_count' => $row[0],
					'nickname' => $row[1]
				);
			}
		}
	}

	$page->title("Organizations Duplicate Report");
	$page->instructions = "Find duplicate organizations based on normalized name matching. Adjust the match length and minimum matches to refine results.";
	$page->addBreadcrumb("Customer");
	$page->addBreadcrumb("Organizations","/_register/organizations");
	$page->addBreadcrumb("Duplicate Report","/_register/organizations_report");
