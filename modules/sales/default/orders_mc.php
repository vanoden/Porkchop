<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('see sales quotes');

	$parameters = array();
	$parameters['status'] = array();
	// get orders based on current search
	if (! $_REQUEST['btn_submit']) {
		$_REQUEST["new"] = 1;
		$_REQUEST["quote"] = 1;
		$_REQUEST["accepted"] = 1;
	}
	if ($_REQUEST["new"]) array_push($parameters['status'],'NEW');
	if ($_REQUEST["quote"]) array_push($parameters['status'],'QUOTE');
	if ($_REQUEST["cancelled"]) array_push($parameters['status'],'CANCELLED');
	if ($_REQUEST["approved"]) array_push($parameters['status'],'APPROVED');
	if ($_REQUEST["complete"]) array_push($parameters['status'],'COMPLETE');
	if ($_REQUEST["accepted"]) array_push($parameters['status'],'ACCEPTED');

	// find orders
	$orderslist = new \Sales\OrderList();
	$orders = $orderslist->find($parameters);
