<?php
	$page = new \Site\Page();
	$page->requirePrivilege("configure site");

	// Initialize domain object and validate parameters
	$domain = new \Company\Domain();
	
	// Validate and load by id or name
	if ($domain->validInteger($_REQUEST['id'] ?? null)) {
		$domain = new \Company\Domain($_REQUEST['id']);
	} else {
		$page->addError("Invalid domain ID"); 
		$_REQUEST['id'] = null;
	}
	
	// Validate hostname
	if ($domain->validHostname($_REQUEST['name'] ?? null)) {
		if (!$domain->get($_REQUEST["name"])) $page->addError("Hostname not found");
	} else {
		$page->addError("Invalid hostname format");
		$_REQUEST['name'] = null;
	}

	$companyList = new \Company\CompanyList();
	$companies = $companyList->find();

	$locationList = new \Company\LocationList();
	$locations = $locationList->find();

	if ($_REQUEST['btn_submit'] ?? null) {
		if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'] ?? '')) {
			$page->addError("Invalid Token");
		}
		else {
			// Validate company_id
			$validCompanyId = false;
			$company = new \Company\Company();
			if ($company->validInteger($_REQUEST['company_id'] ?? null)) {
				$company = new \Company\Company($_REQUEST['company_id']);
				$validCompanyId = true;
			} else {
				$page->addError("Invalid company ID");
				$_REQUEST['company_id'] = null;
			}
			
			// Validate location_id
			$validLocationId = false;
			$location = new \Company\Location();
			if ($location->validInteger($_REQUEST['location_id'] ?? null)) {
				$location = new \Company\Location($_REQUEST['location_id']);
				$validLocationId = true;
				if ($location->error()) $page->addError($location->error());
			} else {
				$page->addError("Invalid location ID");
				$_REQUEST['location_id'] = null;
			}

			// Continue with other validations only if company and location are valid
			if ($validCompanyId && $validLocationId) {
				if (!($company->id ?? null)) {
					$page->addError("Company not found");
					$_REQUEST['company_id'] = null;
				}
				elseif (!($location->id ?? null)) {
					$page->addError("Location not found");
					$_REQUEST['location_id'] = null;
				}
				// Validate domain name using the domain class's validDomainName method
				elseif (!$domain->validDomainName($_REQUEST["domain_name"] ?? null)) {
					$page->addError("Invalid domain name");
					$_REQUEST['domain_name'] = null;
				}
				// Validate domain registrar using the domain class's validRegistrar method
				elseif (!$domain->validRegistrar($_REQUEST['domain_registrar'] ?? null)) {
					$page->addError("Invalid domain registrar name");
					$_REQUEST['domain_registrar'] = null;
				}
				// Validate date_registered using the BaseClass validDate method
				elseif (!$domain->validDate($_REQUEST["date_registered"] ?? null)) {
					$page->addError("Invalid date registered");
					$_REQUEST['date_registered'] = null;
				}
				// Validate date_expires using the BaseClass validDate method
				elseif (!$domain->validDate($_REQUEST["date_expires"] ?? null)) {
					$page->addError("Invalid date expires");
					$_REQUEST['date_expires'] = null;
				}
				else {
					$parameters = array(
						"name"	=> $_REQUEST["domain_name"] ?? null,
						"registrar"	=> $_REQUEST["domain_registrar"] ?? null,
						"date_registered" => $_REQUEST["date_registered"] ?? null,
						"date_expires"	=> $_REQUEST["date_expires"] ?? null,
						"company_id" => $_REQUEST["company_id"] ?? null,
						"location_id" => $_REQUEST["location_id"] ?? null,
					);

					// Update or add in a single if statement
					if ($domain->validInteger($_REQUEST['id'] ?? null)) {
						if (!$domain->update($parameters)) $page->addError("Error updating domain");
						else $page->success = "Updated!";
					} else {
						if (!$domain->add($parameters)) $page->addError("Error adding domain");
						else $page->success = "Added!";
					}
				}
			}
		}
	}

	if (!($domain->name ?? null)) $domain->name = "[null]";
	$page->title("Domain");

	$page->AddBreadCrumb("Company");
	$page->AddBreadCrumb("Domains","/_company/domains");
	$page->AddBreadCrumb($domain->name,"/_company/domain?id=".$domain->id);

	if ($domain->id ?? null) $domain_name = $domain->name;
	else $domain_name = "New Domain";
