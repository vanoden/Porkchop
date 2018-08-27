<?php
	if (! $GLOBALS['_SESSION_']->customer->has_role('engineering user')) {
		$page->error = "Permission Denied";
		return;
	}
	$tasklist = new \Engineering\TaskList();
	$tasks = $tasklist->find();
	if ($tasklist->error()) {
		$page->error = $tasklist->error();
		return;
	}
?>