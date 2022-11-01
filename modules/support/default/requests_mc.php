<?php
	$page = new \Site\Page();
	$page->requirePrivilege('browser support tickets');

	$parameters = array('status' => array('NEW','OPEN'));
	if (isset($_REQUEST['btn_all'])) $parameters = array();

	$requestList = new \Support\RequestList();
	if (isset($_REQUEST['sort_direction'])) $parameters['sort_direction'] = ($_REQUEST['sort_direction'] == 'desc') ? 'desc' : 'asc';
	if (isset($_REQUEST['sort_by']) && (preg_match('/^[\w\_]+$/',$_REQUEST['sort_by']))) $parameters['sort_by'] = $_REQUEST['sort_by'];
	else $_REQUEST['sort_by'] = '';

	$requests = $requestList->find($parameters);
	if ($requestList->error()) $page->addError($requestList->error());
