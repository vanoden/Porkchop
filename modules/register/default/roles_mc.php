<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage customers');

	$roleList = new \Register\RoleList();
	$roles = $roleList->find();
	if ($roleList->error()) $page->addError($roleList->error());