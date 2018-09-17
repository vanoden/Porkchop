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
	if ($_REQUEST["project_id"]) $parameters['project_id'] = $_REQUEST['project_id'];
	if ($_REQUEST["assigned_id"]) $parameters['assigned_id'] = $_REQUEST['assigned_id'];

	$tasks = $tasklist->find($parameters);
	if ($tasklist->error()) {
		$page->error = $tasklist->error();
		return;
	}

	$role = new \Register\Role();
	$role->get("engineering user");
	$assigners = $role->members();

	$projectlist = new \Engineering\ProjectList();
	$projects = $projectlist->find();
?>
