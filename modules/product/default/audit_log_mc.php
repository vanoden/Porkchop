<?php
	$site = new \Site();
	$page = $site->page();;
	$page->requirePrivilege('see audit logging');

	$parameters = array();
	$parameters['status'] = array();
	$can_proceed = true;

	$request = new \HTTP\Request();
	$auditClass = new \Site\AuditLog();

	// Validation Class
	$validationClass = new \Product\Item();

	// Validate item by ID
	if (!empty($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
		$item = new \Product\Item($_REQUEST['id']);
		if (!$item->id) $page->addError("Item not found");
	}
	// Validate item by code
	elseif ($validationClass->validCode($_REQUEST['code'] ?? null)) {
		$item = new \Product\Item();
		$item->get($_REQUEST['code']);
		if (!$item->id) $page->addError("Item not found");
	}
	// Validate item by query vars
	elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0]) && $validationClass->validCode($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$item = new \Product\Item();
		$item->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
		if (!$item->id) $page->addError("Item not found");
	}

	// Initialize $item variable if not already set
	if (!isset($item)) {
		$item = new \Product\Item();
	}

	// extract sort and order parameters from request
	$sort_direction = $_REQUEST['sort_by'] ?? '';
	$order_by = $_REQUEST['order_by'] ?? 'desc';
	$parameters['order_by'] = $order_by;
	$parameters['sort_direction']= $sort_direction;

	$class_name = 'Product\Item';
	if (! class_exists($class_name)) {
		$page->addError("Class does not exist.");
	}
	else {
		$class = new $class_name();
		$parameters['class_name'] = $class_name;
		$parameters['instance_id'] = $item->id;
		$instance = new $class_name($item->id);

		$pagination_start_id = $_REQUEST['pagination_start_id'] ?? 0;
		if (!$request->validInteger($pagination_start_id)) $pagination_start_id = 0;

		// find audits
		$auditList = new \Site\AuditLog\EventList();
		$audits = $auditList->find($parameters,array('sort' => $sort_direction, 'order' => $order_by));
		if ($auditList->error()) {
			$page->addError("Error retrieving audit log: " . $auditList->error());
			$can_proceed = false;
		}

		// paginate results
		$pageNumber = isset($_GET['pagination_start_id']) && is_numeric($_GET['pagination_start_id']) ? (int)$_GET['pagination_start_id'] : 1;
		$recordsPerPage = 10;
		$offset = ($pageNumber - 1) * $recordsPerPage;
		$totalResults = count($audits);
		$auditsCurrentPage = array_slice($audits, $offset, $recordsPerPage);
		$totalPages = ceil($totalResults / $recordsPerPage);

		$start = $_REQUEST['start'] ?? 0;
		if (!$request->validInteger($start)) $start = 0;
				
		if ($start < $recordsPerPage)
			$prev_offset = 0;
		else
			$prev_offset = $start - $recordsPerPage;
			
		$next_offset = $start + $recordsPerPage;
		$last_offset = $totalResults - $recordsPerPage;

		if ($next_offset > $totalResults) $next_offset = $pagination_start_id + $totalResults;

		$pagination = new \Site\Page\Pagination();
		$pagination->forwardParameters(array('add','update','delete','btn_submit','sort_by','order_by'));
		$pagination->size($recordsPerPage);
		$pagination->count($totalResults);
		$display_results = true;
	}

	$page->title = "Product Audit Log for " . $item->code;
	$page->addBreadcrumb('Product', '/_spectros/admin_product/' . $item->code);
	$page->addBreadcrumb('Product Audit Log', '/_product/audit_log/' . $item->code);
