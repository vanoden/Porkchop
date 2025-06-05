<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage terms of use');
	$can_proceed = true;

	// Initialize objects for validation
	$tou = new \Site\TermsOfUse();
	
	// Validate ToU ID
	$tou_id = $_REQUEST['tou_id'] ?? null;
	if (empty($tou_id)) {
		$page->addError("Terms of use ID is required");
		$can_proceed = false;
	} elseif (!$tou->validInteger($tou_id)) {
		$page->addError("Invalid terms of use ID format");
		$can_proceed = false;
	} else {
		$tou = new \Site\TermsOfUse($tou_id);
		if (!$tou->id) {
			$page->addError("Terms of use not found");
			$can_proceed = false;
		}
	}

	// Handle method actions (publish/retract)
	$method = $_REQUEST['method'] ?? '';
	if ($can_proceed && !empty($method)) {
		// Validate CSRF Token
		$csrfToken = $_REQUEST['csrfToken'] ?? '';
		if (!$GLOBALS['_SESSION_']->verifyCSRFToken($csrfToken)) {
			$page->addError("Invalid Token");
			$can_proceed = false;
		}
		
		// Validate version ID
		$version_id = $_REQUEST['id'] ?? null;
		if (empty($version_id)) {
			$page->addError("Version ID is required");
			$can_proceed = false;
		} elseif (!$tou->validInteger($version_id)) {
			$page->addError("Invalid version ID format");
			$can_proceed = false;
		} else {
			$version = new \Site\TermsOfUseVersion($version_id);
			if (!$version->id) {
				$page->addError("Version not found");
				$can_proceed = false;
			} else if ($method == 'publish') {
				if ($version->publish()) {
					$page->appendSuccess("Published version " . $version->date_created());
				} else {
					$page->addError("Error publishing version: " . $version->error());
				}
			} elseif ($method == 'retract') {
				if ($version->retract()) {
					$page->appendSuccess("Retracted version " . $version->date_created());
				} else {
					$page->addError("Error retracting version: " . $version->error());
				}
			} else {
				$page->addError("Invalid method: " . $method);
			}
		}
	}
	
	// Get versions if ToU is valid
	$versions = [];
	if ($can_proceed && $tou->id) {
		$versions = $tou->versions();
		if ($tou->error()) {
			$page->addError($tou->error());
			$can_proceed = false;
		} elseif (empty($versions) || count($versions) < 1) {
			$page->addError("No versions found");
		}
	}

	if ($tou->id) {
		$page->instructions = "Update values and click Submit to update this Terms of Use record";
	} else {
		$page->instructions = "Fill out form and click Submit to create a new Terms Of Use record";
	}
	
	$page->addBreadCrumb("Terms of Use", "/_site/terms_of_use");
	if ($tou->id) {
		$page->addBreadCrumb($tou->name, "/_site/term_of_use?id=" . $tou->id);
	}
	$page->addBreadCrumb("Versions");
