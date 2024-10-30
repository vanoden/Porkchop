<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('see sales quotes');

	$parameters = array();
	$parameters['status'] = array();
	$recordsPerPage = 10;

	$_REQUEST['pagination_start_id'] = isset($_REQUEST['pagination_start_id']) ? $_REQUEST['pagination_start_id'] : 0;

	// extract sort and order parameters from request
	$controls['order'] = isset($_REQUEST['sort_by']) ? $_REQUEST['sort_by'] : '';
	$controls['sort'] = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : 'desc';
	$controls['limit'] = $recordsPerPage;
	$controls['offset'] = $_REQUEST['pagination_start_id'];

	// get orders based on current search
	if (empty($_REQUEST['btn_submit'])) {
		$_REQUEST["new"] = 1;
		$_REQUEST["quote"] = 1;
		$_REQUEST["accepted"] = 1;
	}
	if (!empty($_REQUEST["new"])) array_push($parameters['status'],'NEW');
	if (!empty($_REQUEST["quote"])) array_push($parameters['status'],'QUOTE');
	if (!empty($_REQUEST["cancelled"])) array_push($parameters['status'],'CANCELLED');
	if (!empty($_REQUEST["approved"])) array_push($parameters['status'],'APPROVED');
	if (!empty($_REQUEST["complete"])) array_push($parameters['status'],'COMPLETE');
	if (!empty($_REQUEST["accepted"])) array_push($parameters['status'],'ACCEPTED');

	// find orders
	$orderslist = new \Sales\OrderList();
	$orders = $orderslist->find($parameters, $controls);
	$totalRecords = $orderslist->count();
	if ($orderslist->error()) $page->addError($orderslist->error());

	$pageNumber = $controls['offset'] / $recordsPerPage + 1;

	// paginate results
    $pagination = new \Site\Page\Pagination();
    $pagination->forwardParameters(array('search','status_active','status_hidden','status_deleted','sort_by', 'order_by'));
    $pagination->size($recordsPerPage);
    $pagination->count($totalRecords);