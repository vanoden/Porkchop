<?php
	$page = new \Site\Page();

	$companyList = new \Company\CompanyList();
	list($company) = $companyList->find();
	if (empty($company)) {
		$company = new stdClass();
		$company->name = "us";
	}
