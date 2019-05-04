<?php
	$page = new \Site\Page();
    $page->requireRole('package manager');

	$packagelist = new \Package\PackageList();

	$parameters = array();

	if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
	if (isset($_REQUEST['repository_code'])) $parameters['repository_code'] = $_REQUEST['repository_code'];
	if (isset($_REQUEST['license'])) $parameters['license'] = $_REQUEST['license'];
	if (isset($_REQUEST['platform'])) $parameters['platform'] = $_REQUEST['platform'];
	if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];

	$packages = $packagelist->find($parameters);
?>