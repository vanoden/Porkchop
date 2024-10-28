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
			$company = new \Company\Company($_REQUEST['company_id']);
			$location = new \Company\Location($_REQUEST['location_id']);
			if ($location->error()) $page->addError($location->error());

			if (empty($company->id)) {
				$page->addError("Company not found");
				$_REQUEST['company_id'] = null;
			}
			elseif (empty($location->id)) {
				$page->addError("Location not found");
				$_REQUEST['location_id'] = null;
			}
			elseif (! filter_var($_REQUEST["domain_name"],FILTER_VALIDATE_DOMAIN,array(FILTER_NULL_ON_FAILURE))) {
				$page->addError("Invalid domain name");
				$_REQUEST['domain_name'] = null;
			}
			elseif (! empty($_REQUEST['domain_registrar']) && ! preg_match("/^\w[\w\-\.]+$/",$_REQUEST['domain_registrar'])) {
				$page->addError("Invalid domain registrar name");
				$_REQUEST['domain_registrar'] = null;
			}
			elseif (!empty($_REQUEST["date_registered"]) && ! get_mysql_date($_REQUEST["date_registered"])) {
				$page->addError("Invalid date registered");
				$_REQUEST['date_registered'] = null;
			}
			elseif (!empty($_REQUEST["date_expires"]) && ! get_mysql_date($_REQUEST["date_expires"])) {
				$page->addError("Invalid date expires");
				$_REQUEST['date_expires'] = null;
			}
			else {
				$parameters = array(
					"name"	=> $_REQUEST["domain_name"],
					"registrar"	=> $_REQUEST["domain_registrar"],
					"date_registered" => $_REQUEST["date_registered"],
					"date_expires"	=> $_REQUEST["date_expires"],
					"company_id" => $_REQUEST["company_id"],
					"location_id" => $_REQUEST["location_id"],
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