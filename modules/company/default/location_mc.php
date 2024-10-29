<?php
	$page = new \Site\Page();
	$page->requirePrivilege("configure site");

	$location = new \Company\Location();
	if (isset($_REQUEST['id'])) {
		$location = new \Company\Location($_REQUEST['id']);
	}
	else if (isset($_REQUEST['name']) && ! $location->get($_REQUEST["name"])) {
		$page->addError("Location not found");
	}

	$companyList = new \Company\CompanyList();
	$companies = $companyList->find();

	$domainList = new \Company\DomainList();
	$domains = $domainList->find();

	$stateList = new \Geography\AdminList();
	$states = $stateList->find();

	if (isset($_REQUEST['btn_submit'])) {
		if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
			$page->addError("Invalid Token");
		}
		else {
			$company = new \Company\Company($_REQUEST['company_id']);
			$domain = new \Company\Domain($_REQUEST['domain_id']);
			if ($domain->error()) $page->addError($domain->error());

			if (empty($company->id)) {
				$page->addError("Company not found");
				$_REQUEST['company_id'] = null;
			}
			elseif (empty($domain->id)) {
				$page->addError("Domain not found");
				$_REQUEST['domain_id'] = null;
			}
			elseif (! empty($_REQUEST['address_1']) && ! $this->validAddressLine($_REQUEST['address_1'])) {
				$page->addError("Invalid Address Line 1");
				$_REQUEST['domain_registrar'] = null;
			}
			elseif (!empty($_REQUEST["address_2"]) && ! $this->validAddressLine($_REQUEST["address_2"])) {
				$page->addError("Invalid Address Line 2");
				$_REQUEST['date_registered'] = null;
			}
			elseif (!empty($_REQUEST["city"]) && ! $this->validCity($_REQUEST["city"])) {
				$page->addError("Invalid City Name");
				$_REQUEST['date_expires'] = null;
			}
			else {
				$parameters = array(
					"name"			=> $_REQUEST["name"],
					"address_1"		=> $_REQUEST["address_1"],
					"address_2" 	=> $_REQUEST["address_2"],
					"city"			=> $_REQUEST["city"],
					"state_id"		=> $_REQUEST["state_id"],
					"company_id"	=> $_REQUEST["company_id"],
					"domain_id"		=> $_REQUEST["domain_id"],
				);

				if (isset($_REQUEST['id']) && $_REQUEST['id'] > 0) {
					if (! $location->update($parameters)) $page->addError("Error updating domain");
					else $page->success = "Location ".$location->id." Updated!";
				}
				else {
					if (! $location->add($parameters)) $page->addError("Error adding location");
					else $page->success = "Added!";
				}
			}
		}
	}

	if (empty($location->name)) $location->name = "[null]";

	$page->title("Location");

	$page->AddBreadCrumb("Locations","/_company/locations");
	$page->AddBreadCrumb($location->name,"/_company/location?id=".$location->id);

	if ($location->id) $name = $location->name;
	else $name = "New Location";