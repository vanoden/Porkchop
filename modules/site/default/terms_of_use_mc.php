<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage terms of use');

	$termsOfUseList = new \Site\TermsOfUseList();
	$termsOfUse = $termsOfUseList->find();
	if ($termsOfUseList->error()) {
		$page->addError($termsOfUseList->error());
	}