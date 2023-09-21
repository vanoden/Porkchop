<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege("configure company");

	if ($_REQUEST['id']) {
		$company = new \Company\Company($_REQUEST['id']);
	}
	else {
		$companyList = new \Company\CompanyList();
		list($company) = $companyList->find();
	}

	if (!empty($_REQUEST['btn_submit'])) {
		if (!empty($_REQUEST['name'])) {
			if (! $company->validName($_REQUEST['name'])) $page->addError("Invalid Name");
			else {
				$company->update(array('name' => $_REQUEST['name']));
				if ($company->error()) $page->addError($company->error());
				else $page->appendSuccess("Updated company name");
			}
		}
	}