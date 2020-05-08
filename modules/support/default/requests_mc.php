<?php
	$page = new \Site\Page();
	$page->fromRequest();
	$page->requireRole('support user');

	$parameters = array('status' => array('NEW','OPEN'));
	if (isset($_REQUEST['btn_all'])) $parameters = array();

	$requestList = new \Support\RequestList();
	$requests = $requestList->find($parameters);
	if ($requestList->error()) $page->addError($requestList->error());
