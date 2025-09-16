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
		
		// Validate login code if provided
		if (!empty($_REQUEST['login']) && !preg_match('/^[a-zA-Z0-9_-]+$/', $_REQUEST['login'])) {
			$page->addError("Login code can only contain letters, numbers, hyphens, and underscores");
			$can_proceed = false;
		}
		
		// Validate status
		$valid_statuses = ['ACTIVE', 'INACTIVE', 'SUSPENDED'];
		if (!empty($_REQUEST['status']) && !in_array($_REQUEST['status'], $valid_statuses)) {
			$page->addError("Invalid status value");
			$can_proceed = false;
		}
		
		if ($can_proceed) {
			// Prepare update parameters
			$update_params = array('name' => $_REQUEST['name']);
			
			// Add optional fields if provided
			if (isset($_REQUEST['login'])) {
				$update_params['login'] = $_REQUEST['login'];
			}
			if (isset($_REQUEST['status'])) {
				$update_params['status'] = $_REQUEST['status'];
			}
			if (isset($_REQUEST['deleted'])) {
				$update_params['deleted'] = 1;
			} else {
				$update_params['deleted'] = 0;
			}
			
			$company->update($update_params);
			if ($company->error()) {
				$page->addError("Error updating company: " . $company->error());
			} else {
				$page->appendSuccess("Company configuration updated successfully");
			}
		}
	}