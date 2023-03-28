<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('see sales quotes');

	$parameters = array();
	$parameters['status'] = array();

	// extract sort and order parameters from request
	$sort_direction = isset($_REQUEST['sort_by']) ? $_REQUEST['sort_by'] : '';
	$order_by = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : 'desc';
	$parameters['order_by'] = $order_by;
	$parameters['sort_direction']= $sort_direction;

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

	// paginate results
	$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
	$recordsPerPage = 10;
	$offset = ($page - 1) * $recordsPerPage;
	$totalResults = count($orders);
	$orderCurrentPage = array_slice($orders, $offset, $recordsPerPage);
	$totalPages = ceil($totalResults / $recordsPerPage);