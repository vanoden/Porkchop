<?php
	$page = new \Site\Page();
	$page->fromRequest();
	$page->requireRole("administrator");

	$domainList = new \Company\DomainList();
	$domains = $domainList->find();
