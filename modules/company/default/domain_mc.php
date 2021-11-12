<?php
	$page = new \Site\Page();
	$page->fromRequest();
	$page->requireRole("administrator");

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
		$parameters = array(
			"name"	=> $_REQUEST["domain_name"],
			"registrar"	=> $_REQUEST["domain_registrar"],
			"date_registered" => $_REQUEST["date_registered"],
			"date_expires"	=> $_REQUEST["date_expires"],
			"company_id" => $_REQUEST["company_id"],
			"location_id" => $_REQUEST["location_id"],
		);

		if (isset($_REQUEST['id']) && $_REQUEST['id'] > 0) {
			if (! $domain->update($parameters)) {
				$page->addError("Error updating domain");
			}
			$page->success = "Updated!";
		}
		else {
			if(! $domain->add($parameters)) {
				$page->addError("Error adding domain");
			}
			$page->success = "Added!";
		}
	}

	if ($domain->id) $domain_name = $domain->name;
	else $domain_name = "New Domain";
