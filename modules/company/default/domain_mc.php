<?php
	$page = new \Site\Page();
	$page->requirePrivilege("configure site");

	// Initialize domain object and validate parameters
	$domain = new \Company\Domain();
	
	// Validate and load by id or name, but not both
	if (!empty($_REQUEST['id'])) {
		if ($domain->validInteger($_REQUEST['id'])) {
			$domain = new \Company\Domain($_REQUEST['id']);
		} else {
			$page->addError("Invalid domain ID"); 
			$_REQUEST['id'] = null;
		}
	} elseif (!empty($_REQUEST['name'])) {
		if ($domain->validHostname($_REQUEST['name'])) {
			if (!$domain->get($_REQUEST["name"])) $page->addError("Hostname not found");
		} else {
			$page->addError("Invalid hostname format");
			$_REQUEST['name'] = null;
		}
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
				// Validate domain name using the domain class's validDomainName method (only if provided)
				elseif (!empty($_REQUEST["domain_name"]) && !$domain->validDomainName($_REQUEST["domain_name"])) {
					$page->addError("Invalid domain name");
					$_REQUEST['domain_name'] = null;
				}
				// Validate domain registrar using the domain class's validRegistrar method (only if provided)
				elseif (!empty($_REQUEST['domain_registrar']) && !$domain->validRegistrar($_REQUEST['domain_registrar'])) {
					$page->addError("Invalid domain registrar name");
					$_REQUEST['domain_registrar'] = null;
				}
				// Validate date_registered using the BaseClass validDate method (only if provided)
				elseif (!empty($_REQUEST["date_registered"]) && !$domain->validDate($_REQUEST["date_registered"])) {
					$page->addError("Invalid date registered");
					$_REQUEST['date_registered'] = null;
				}
				// Validate date_expires using the BaseClass validDate method (only if provided)
				elseif (!empty($_REQUEST["date_expires"]) && !$domain->validDate($_REQUEST["date_expires"])) {
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
						// Ensure domain object is loaded
						if (!($domain->id ?? null)) {
							$page->addError("Domain not found for update");
						} else {
							if (!$domain->update($parameters)) {
								$error_msg = $domain->error() ? $domain->error() : "Update failed";
								$page->addError("Error updating domain: " . $error_msg);
							} else {
								$page->success = "Domain updated successfully!";
							}
						}
					} else {
						if (!$domain->add($parameters)) {
							$error_msg = $domain->error() ? $domain->error() : "Add failed";
							$page->addError("Error adding domain: " . $error_msg);
						} else {
							$page->success = "Domain added successfully!";
						}
					}
				}
			}
		}
	}

	// Set default values if domain is new or invalid
	if (!($domain->id ?? null)) {
		$domain->name = '';
		$domain->registrar = '';
		$domain->date_registered = '';
		$domain->date_expires = '';
		$domain->company_id = null;
		$domain->location_id = null;
	}

	$page->title("Domain");
	$page->setAdminMenuSection("Company");  // Keep Company section open

	$page->AddBreadCrumb("Company");
	$page->AddBreadCrumb("Domains","/_company/domains");
	if ($domain->id ?? null) {
		$page->AddBreadCrumb($domain->name,"/_company/domain?id=".$domain->id);
	} else {
		$page->AddBreadCrumb("New Domain","/_company/domain");
	}
