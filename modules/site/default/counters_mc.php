<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('see site reports');

	$counterList = new \Site\CounterList();
	$counters = $counterList->find();

	$page->title('Counters');
	$page->addBreadcrumb('Counters', '/_site/counters');