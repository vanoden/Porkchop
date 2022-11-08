<?php
	$page = new \Site\Page();

	$companyList = new \Company\CompanyList();
	list($company) = $companyList->find();
	if ($companyList->error()) {
		$page->addError($companyList->error());
	}
	if (empty($company)) {
		$company = new stdClass();
		$company->name = "us";
	}