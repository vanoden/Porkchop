<?php
	$page = new \Site\Page();
	$page->requireRole('engineering user');

	if (! isset($_REQUEST['btn_submit'])) {
		$_REQUEST['new'] = 1;
		$_REQUEST['active'] = 1;
		$_REQUEST['broken'] = 1;
	}

	$tasklist = new \Engineering\TaskList();
	$parameters = array();
	$parameters['status'] = array();
	if ($_REQUEST["new"]) array_push($parameters['status'],'NEW');
	if ($_REQUEST["active"]) array_push($parameters['status'],'ACTIVE');
	if ($_REQUEST["complete"]) array_push($parameters['status'],'COMPLETE');
	if ($_REQUEST["cancelled"]) array_push($parameters['status'],'CANCELLED');
	if ($_REQUEST["broken"]) array_push($parameters['status'],'BROKEN');
	if ($_REQUEST["testing"]) array_push($parameters['status'],'TESTING');
	if ($_REQUEST["hold"]) array_push($parameters['status'],'HOLD');
	if ($_REQUEST["duplicate"]) $parameters['duplicate'] = $_REQUEST['duplicate'];
	if ($_REQUEST["project_id"]) $parameters['project_id'] = $_REQUEST['project_id'];
	if ($_REQUEST["product_id"]) $parameters['product_id'] = $_REQUEST['product_id'];
	if ($_REQUEST["assigned_id"]) $parameters['assigned_id'] = $_REQUEST['assigned_id'];
	if ($_REQUEST["role_id"]) $parameters['role_id'] = $_REQUEST['role_id'];

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

	$productlist = new \Engineering\ProductList();
	$products = $productlist->find();
	
    // get roles set for engineering to apply to tasks
	$roleList = new \Register\RoleList();
	$engineeringRoles = $roleList->find();
