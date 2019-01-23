<?php
	$page = new \Site\Page();
	$page->fromRequest();
	$page->requireRole('support user');

	$adminlist = new \Register\CustomerList();
	$admins = $adminlist->find(array('role' => 'support user'));

	$actionlist = new \Support\Request\Item\ActionList();
	if ($_REQUEST['filtered']) {
		$parameters = array(
			'assigned_id'	=> $_REQUEST['assigned_id'],
			'status'		=> array()
		);
		if ($_REQUEST['status_new']) array_push($parameters['status'],'NEW');
		if ($_REQUEST['status_active']) array_push($parameters['status'],'ACTIVE');
		if ($_REQUEST['status_pending_customer']) array_push($parameters['status'],'PENDING CUSTOMER');
		if ($_REQUEST['status_pending_vendor']) array_push($parameters['status'],'PENDING VENDOR');
		if ($_REQUEST['status_cancelled']) array_push($parameters['status'],'CANCELLED');
		if ($_REQUEST['status_complete']) array_push($parameters['status'],'COMPLETE');
	}
	else {
		$parameters = array(
			'status'	=> array(
				'NEW','ACTIVE','PENDING CUSTOMER','PENDING VENDOR'
			)
		);
		$_REQUEST['status_new'] = true;
		$_REQUEST['status_active'] = true;
		$_REQUEST['status_pending_customer'] = true;
		$_REQUEST['status_pending_vendor'] = true;
	}
	$actions = $actionlist->find($parameters);
	if ($actionlist->error()) $page->addError($actionlist->error());
