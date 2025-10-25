<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('see site reports');

	$counterList = new \Site\CounterList();
	$counters = $counterList->find();

	$page->title('Counters');
	$page->setAdminMenuSection("Site");  // Keep Site section open
	$page->addBreadcrumb('Counters', '/_site/counters');