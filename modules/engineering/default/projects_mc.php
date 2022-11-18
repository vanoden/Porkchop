<?php
	$page = new \Site\Page();
	$page->requirePrivilege('see engineering projects');
	$projectlist = new \Engineering\ProjectList();
	$projects = $projectlist->find();
