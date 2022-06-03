<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage customers');

	$roleList = new \Register\RoleList();
	$roles = $roleList->find();
