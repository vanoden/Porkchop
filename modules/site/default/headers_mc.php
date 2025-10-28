<?php

	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage headers');

	$headerList = new \Site\HeaderList();
	$headers = $headerList->find();

	$page->title('Site HTTP Headers');
	$page->setAdminMenuSection("Site");  // Keep Site section open
	$page->addBreadcrumb('Headers', '/_site/headers');