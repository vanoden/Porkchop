<?php
$page = new \Site\Page();
$page->requirePrivilege("configure site");

// Initialize location object
$location = new \Company\Location();

// Validate and load by id
if ($location->validInteger($_REQUEST['id'] ?? null)) {
	$location = new \Company\Location($_REQUEST['id']);
	if ($location->error()) $page->addError($location->error());
} else {
	$page->addError("Invalid location ID");
	$_REQUEST['id'] = null;
}

// Load required data for dropdowns
$stateList = new \Geography\ProvinceList();
$states = $stateList->find();

$companyList = new \Company\CompanyList();
$companies = $companyList->find();

$domainList = new \Company\DomainList();
$domains = $domainList->find();

// Handle form submission
if ($_REQUEST['btn_submit'] ?? null) {
	if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'] ?? '')) {
		$page->addError("Invalid Token");
	} else {
		// Validate required fields
		$can_proceed = true;
		
		if (empty($_REQUEST['name'])) {
			$page->addError("Name is required");
			$can_proceed = false;
		}
		
		if (empty($_REQUEST['company_id']) || !$location->validInteger($_REQUEST['company_id'])) {
			$page->addError("Valid company is required");
			$can_proceed = false;
		}
		
		if ($can_proceed) {
			$parameters = array(
				"name" => $_REQUEST['name'] ?? '',
				"address_1" => $_REQUEST['address_1'] ?? '',
				"address_2" => $_REQUEST['address_2'] ?? '',
				"city" => $_REQUEST['city'] ?? '',
				"state_id" => $_REQUEST['state_id'] ?? null,
				"company_id" => $_REQUEST['company_id'] ?? null,
				"domain_id" => $_REQUEST['domain_id'] ?? null,
			);

			// Update or add location
			if (($location->id ?? null) && $location->id > 0) {
				if (!$location->update($parameters)) $page->addError("Error updating location: " . $location->error());
				else $page->success = "Location updated successfully!";
			} else {
				if (!$location->add($parameters)) $page->addError("Error adding location: " . $location->error());
				else {
					$page->success = "Location added successfully!";
					// Redirect to the new location
					header("Location: /_company/location?id=" . $location->id);
					exit;
				}
			}
		}
	}
}

// Set default values if location is new
if (!($location->id ?? null)) {
	$location->name = '';
	$location->address_1 = '';
	$location->address_2 = '';
	$location->city = '';
	$location->state_id = null;
	$location->company_id = null;
	$location->domain_id = null;
}

$page->title("Location");

$page->AddBreadCrumb("Company");
$page->AddBreadCrumb("Locations", "/_company/locations");
if ($location->id) {
	$page->AddBreadCrumb($location->name, "/_company/location?id=" . $location->id);
} else {
	$page->AddBreadCrumb("New Location", "/_company/location");
}
