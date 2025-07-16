<?php

	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage headers');

	$headerList = new \Site\HeaderList();
	$headers = $headerList->find();

	$page->title('Site HTTP Headers');
	$page->addBreadcrumb('Headers', '/_site/headers');