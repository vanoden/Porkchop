<?php
	$page = new \Site\Page();
	$page->requireRole('engineering user');
	$projectlist = new \Engineering\ProjectList();
	$projects = $projectlist->find();
