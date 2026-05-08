<?php
	$site = new \Site();
	$page = $site->page();

	$companyList = new \Company\CompanyList();
	list($company) = $companyList->find();
	if ($companyList->error()) {
		$page->addError($companyList->error());
	}
	if (empty($company)) {
		$company = new stdClass();
		$company->name = "us";
	}

	$send_from = $site->configuration('send_from');
	if (empty($send_from)) {
		$domain = $site->configuration('hostname');
		if (preg_match('/([\w\-]+\.[\w\-]+)$/',$domain,$matches)) {
			$domain = $matches[1];
		}

		$send_from = 'no-reply@'.$domain;
	}
