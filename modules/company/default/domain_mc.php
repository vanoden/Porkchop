<?php
	$page = new \Site\Page();
	$page->requirePrivilege("configure site");

	$domain = new \Company\Domain();
	if (isset($_REQUEST['id'])) {
		$domain = new \Company\Domain($_REQUEST['id']);
	}
	else if (isset($_REQUEST['name']) && ! $domain->get($_REQUEST["name"])) {
		$page->addError("Hostname not found");
	}

	$companyList = new \Company\CompanyList();
	$companies = $companyList->find();

	$locationList = new \Company\LocationList();
	$locations = $locationList->find();

	if (isset($_REQUEST['btn_submit'])) {
		if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
			$page->addError("Invalid Token");
		}
		else {
			// Sanitize inputs
			$request = [
				'domain_name' => $domain->sanitize($_REQUEST['domain_name'], 'text'),
				'domain_registrar' => $domain->sanitize($_REQUEST['domain_registrar'], 'text'),
				'date_registered' => $domain->sanitize($_REQUEST['date_registered'], 'date'),
				'date_expires' => $domain->sanitize($_REQUEST['date_expires'], 'date'),
				'company_id' => $domain->sanitize($_REQUEST['company_id'], 'integer'),
				'location_id' => $domain->sanitize($_REQUEST['location_id'], 'integer'),
			];

			$company = new \Company\Company($request['company_id']);
			$location = new \Company\Location($request['location_id']);
			if ($location->error()) $page->addError($location->error());

			if (empty($company->id)) {
				$page->addError("Company not found");
				$request['company_id'] = null;
			}
			elseif (empty($location->id)) {
				$page->addError("Location not found");
				$request['location_id'] = null;
			}
			elseif (! filter_var($request["domain_name"], FILTER_VALIDATE_DOMAIN, array(FILTER_NULL_ON_FAILURE))) {
				$page->addError("Invalid domain name");
				$request['domain_name'] = null;
			}
			elseif (! empty($request['domain_registrar']) && ! preg_match("/^\w[\w\-\.]+$/", $request['domain_registrar'])) {
				$page->addError("Invalid domain registrar name");
				$request['domain_registrar'] = null;
			}
			elseif (!empty($request["date_registered"]) && ! get_mysql_date($request["date_registered"])) {
				$page->addError("Invalid date registered");
				$request['date_registered'] = null;
			}
			elseif (!empty($request["date_expires"]) && ! get_mysql_date($request["date_expires"])) {
				$page->addError("Invalid date expires");
				$request['date_expires'] = null;
			}
			else {
				$parameters = array(
					"name"	=> $request["domain_name"],
					"registrar"	=> $request["domain_registrar"],
					"date_registered" => $request["date_registered"],
					"date_expires"	=> $request["date_expires"],
					"company_id" => $request["company_id"],
					"location_id" => $request["location_id"],
				);

				if (isset($_REQUEST['id']) && $_REQUEST['id'] > 0) {
					if (! $domain->update($parameters)) $page->addError("Error updating domain");
					else $page->success = "Updated!";
				}
				else {
					if (! $domain->add($parameters)) $page->addError("Error adding domain");
					else $page->success = "Added!";
				}
			}
		}
	}

	if (empty($domain->name)) $domain->name = "[null]";

	$page->title("Domain");

	$page->AddBreadCrumb("Company");
	$page->AddBreadCrumb("Domains","/_company/domains");
	$page->AddBreadCrumb($domain->name,"/_company/domain?id=".$domain->id);

	if ($domain->id) $domain_name = $domain->name;
	else $domain_name = "New Domain";