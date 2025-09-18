<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege("configure company");
	$can_proceed = true;

	// Create company object for validation
	$company = new \Company\Company();
	$companyList = new \Company\CompanyList();

	// Load company
	if (!empty($_REQUEST['id'])) {
		if (!$company->validInteger($_REQUEST['id'])) {
			$page->addError("Invalid company ID format");
			$can_proceed = false;
		} else {
			$company = new \Company\Company($_REQUEST['id']);
			if (!$company->exists()) {
				$page->addError("Company not found");
				$can_proceed = false;
			}
		}
	} else {
		list($company) = $companyList->find();
		if ($companyList->error()) {
			$page->addError("Error loading company: " . $companyList->error());
			$can_proceed = false;
		}
	}

	// Handle form submission
	if (isset($_REQUEST['btn_submit'])) {
		// Validate CSRF token
		if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'] ?? '')) {
			$page->addError("Invalid Request");
			$can_proceed = false;
		}
		
		// Validate company name
		if (empty($_REQUEST['name'])) {
			$page->addError("Company name is required");
			$can_proceed = false;
		} elseif (!$company->validName($_REQUEST['name'])) {
			$page->addError("Invalid company name format");
			$can_proceed = false;
		}
		
		if ($can_proceed) {
			$company->update(array('name' => $_REQUEST['name']));
			if ($company->error()) {
				$page->addError("Error updating company: " . $company->error());
			} else {
				$page->appendSuccess("Updated company name");
			}
		}
	}
