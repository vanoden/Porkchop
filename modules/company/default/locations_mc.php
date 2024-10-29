<?php
	$page = new \Site\Page();
	$page->requirePrivilege("configure site");

	$locationList = new \Company\LocationList();
	$locations = $locationList->find();
	if ($locationList->error()) $page->addError($locationList->error());

	$page->AddBreadcrumb('Company');
	$page->AddBreadcrumb('Locations');
