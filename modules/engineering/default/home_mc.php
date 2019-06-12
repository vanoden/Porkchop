<?php
	$page = new \Site\Page();
	$page->requireRole('engineering user');

	$tasklist = new \Engineering\TaskList();
	$unassigned_tasks = $tasklist->find(array("assigned_id" => '0',"_limit" => 4));
	$my_tasks = $tasklist->find(array("assigned_id" => $GLOBALS['_SESSION_']->customer->id,"status" => array('NEW','ACTIVE'),"_limit" => 4));
	if ($tasklist->error()) $page->error = $tasklist->error();

	$releaselist = new \Engineering\ReleaseList();
	$releases = $releaselist->find(array("_limit" => 4));

	$productlist = new \Engineering\ProductList();
	$products = $productlist->find(array("_limit" => 4));

	$projectlist = new \Engineering\ProjectList();
	$projects = $projectlist->find(array("_limit" => 4));
