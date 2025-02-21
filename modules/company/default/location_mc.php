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
			// Sanitize inputs
			$request = [
				'name' => $location->sanitize($_REQUEST['name'], 'text'),
				'address_1' => $location->sanitize($_REQUEST['address_1'], 'address'),
				'address_2' => $location->sanitize($_REQUEST['address_2'], 'address'),
				'city' => $location->sanitize($_REQUEST['city'], 'text'),
				'state_id' => $location->sanitize($_REQUEST['state_id'], 'integer'),
				'company_id' => $location->sanitize($_REQUEST['company_id'], 'integer'),
				'domain_id' => $location->sanitize($_REQUEST['domain_id'], 'integer'),
			];

			$company = new \Company\Company($request['company_id']);
			$domain = new \Company\Domain($request['domain_id']);
			if ($domain->error()) $page->addError($domain->error());

			if (empty($company->id)) {
				$page->addError("Company not found");
				$request['company_id'] = null;
			}
			elseif (empty($domain->id)) {
				$page->addError("Domain not found");
				$request['domain_id'] = null;
			}
			elseif (! empty($request['address_1']) && ! $location->validAddressLine($request['address_1'])) {
				$page->addError("Invalid Address Line 1");
				$request['address_1'] = null;
			}
			elseif (!empty($request["address_2"]) && ! $location->validAddressLine($request["address_2"])) {
				$page->addError("Invalid Address Line 2");
				$request['address_2'] = null;
			}
			elseif (!empty($request["city"]) && ! $location->validCity($request["city"])) {
				$page->addError("Invalid City Name");
				$request['city'] = null;
			}
			else {
				$parameters = array(
					"name"			=> $request["name"],
					"address_1"		=> $request["address_1"],
					"address_2" 	=> $request["address_2"],
					"city"			=> $request["city"],
					"state_id"		=> $request["state_id"],
					"company_id"	=> $request["company_id"],
					"domain_id"		=> $request["domain_id"],
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