<?php
	if (! $GLOBALS['_SESSION_']->customer->has_role('engineering user')) {
		$page->error = "Permission Denied";
		return;
	}
	$tasklist = new \Engineering\TaskList();
	$parameters = array();
	$parameters['status'] = array("NEW","ACTIVE");
	if ($_REQUEST["complete"]) array_push($parameters['status'],'COMPLETE');
	if ($_REQUEST["cancelled"]) array_push($parameters['status'],'CANCELLED');
	if ($_REQUEST["hold"]) array_push($parameters['status'],'HOLD');

	$tasks = $tasklist->find($parameters);
	if ($tasklist->error()) {
		$page->error = $tasklist->error();
		return;
	}
?>
