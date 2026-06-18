<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('see sales quotes');
	$can_proceed = true;

	$parameters = array();
	$parameters['status'] = array();
	$recordsPerPage = 10;

	$requestedPaginationStart = array_key_exists('pagination_start_id', $_GET)
		|| array_key_exists('pagination_start_id', $_POST);
	$paginationStart = $_REQUEST['pagination_start_id'] ?? 0;

	// Validate pagination start ID
	if (!is_numeric($paginationStart)) {
		$page->addError("Invalid pagination start ID");
		$can_proceed = false;
	}

	// extract sort and order parameters from request (sort = column, order = direction for DocumentList)
	$controls['sort'] = $_REQUEST['sort_by'] ?? 'id';
	$controls['order'] = $_REQUEST['order_by'] ?? 'desc';
	$controls['limit'] = $recordsPerPage;
	$controls['offset'] = (int)$paginationStart;

	// Validate sort and order parameters
	$valid_sort_fields = ['id', 'code', 'status', 'customer_id', 'salesperson_id'];
	if (!empty($controls['sort']) && !in_array($controls['sort'], $valid_sort_fields)) {
		$page->addError("Invalid sort field");
		$can_proceed = false;
	}
	if (!in_array(strtolower($controls['order'] ?? ''), ['asc', 'desc'])) {
		$controls['order'] = 'desc';
	}

	// get orders based on current search
	$hasFilterActivity = !empty($_REQUEST['btn_submit'])
		|| !empty($_REQUEST['btn_search'])
		|| $requestedPaginationStart
		|| isset($_REQUEST['sort_by'])
		|| array_key_exists('new', $_GET) || array_key_exists('new', $_POST)
		|| array_key_exists('quote', $_GET) || array_key_exists('quote', $_POST)
		|| array_key_exists('cancelled', $_GET) || array_key_exists('cancelled', $_POST)
		|| array_key_exists('approved', $_GET) || array_key_exists('approved', $_POST)
		|| array_key_exists('accepted', $_GET) || array_key_exists('accepted', $_POST)
		|| array_key_exists('complete', $_GET) || array_key_exists('complete', $_POST);

	if ($can_proceed && !$hasFilterActivity) {
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

	if ($can_proceed) {
		// find orders
		$orderslist = new \Sales\SalesOrderList();
		$orders = $orderslist->find($parameters, $controls);
		$totalRecords = $orderslist->count();
		if ($orderslist->error()) {
			$page->addError($orderslist->error());
		}

		$pageNumber = $controls['offset'] / $recordsPerPage + 1;

		// paginate results
		$pagination = new \Site\Page\Pagination();
		$pagination->forwardParameters(array('new','quote','cancelled','approved','accepted','complete','sort_by','order_by'));
		$pagination->size($recordsPerPage);
		$pagination->count($totalRecords);
		$page->isSearchResults = $hasFilterActivity;
	}