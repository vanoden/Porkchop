<?php
	$page = new \Site\Page();
	$page->requirePrivilege("configure site");

	$domainList = new \Company\DomainList();
	$domains = $domainList->find();
	if ($domainList->error()) $page->addError($domainList->error());

	$page->AddBreadcrumb('Company');
	$page->AddBreadcrumb('Domains');