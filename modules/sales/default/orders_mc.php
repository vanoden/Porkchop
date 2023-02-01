<?php
	$page = new \Site\Page();
	$page->fromRequest();
	$page->requirePrivilege('see sales quotes');
	
	// get orders based on current search
	$parameters = array();
	$parameters['status'] = array();
	if ($_REQUEST["new"]) array_push($parameters['status'],'NEW');
	if ($_REQUEST["quote"]) array_push($parameters['status'],'QUOTE');
	if ($_REQUEST["cancelled"]) array_push($parameters['status'],'CANCELLED');
	if ($_REQUEST["approved"]) array_push($parameters['status'],'APPROVED');
	if ($_REQUEST["complete"]) array_push($parameters['status'],'COMPLETE');
	if ($_REQUEST["accepted"]) array_push($parameters['status'],'ACCEPTED');

	// find orders
	$orderslist = new \Sales\OrderList();
	$orders = $orderslist->find($parameters);
