<?php
	$site = new \Site();
	$page = $site->page();;
	$page->requirePrivilege('see audit logging');
    
	$parameters = array();
	$parameters['status'] = array();

	// extract sort and order parameters from request
	$sort_direction = isset($_REQUEST['sort_by']) ? $_REQUEST['sort_by'] : '';
	$order_by = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : 'desc';
	$parameters['order_by'] = $order_by;
	$parameters['sort_direction']= $sort_direction;

	// get audits based on current search
	if (! isset($_REQUEST['btn_submit'])) {
		$_REQUEST["add"] = 1;
		$_REQUEST["update"] = 1;
		$_REQUEST["delete"] = 1;
	}
	if (isset($_REQUEST["add"]) && !empty($_REQUEST["add"])) array_push($parameters['status'],'add');
    if (isset($_REQUEST["update"]) && !empty($_REQUEST["update"])) array_push($parameters['status'],'update');
    if (isset($_REQUEST["delete"]) && !empty($_REQUEST["delete"])) array_push($parameters['status'],'delete');
	if (!isset($_REQUEST['pagination_start_id'])) $_REQUEST['pagination_start_id'] = 0;

	// find audits
	$auditList = new \Site\AuditLog\EventList();
	$audits = $auditList->find($parameters);

	// paginate results
	$pageNumber = isset($_GET['pagination_start_id']) && is_numeric($_GET['pagination_start_id']) ? (int)$_GET['pagination_start_id'] : 1;
	$recordsPerPage = 10;
	$offset = ($pageNumber - 1) * $recordsPerPage;
	$totalResults = count($audits);
	$auditsCurrentPage = array_slice($audits, $offset, $recordsPerPage);
	$totalPages = ceil($totalResults / $recordsPerPage);

    if (!isset($_REQUEST['start'])) $_REQUEST['start'] = 0;
	if ($_REQUEST['start'] < $recordsPerPage)
		$prev_offset = 0;
	else
		$prev_offset = $_REQUEST['start'] - $recordsPerPage;
		
	$next_offset = $_REQUEST['start'] + $recordsPerPage;
	$last_offset = $totalResults - $recordsPerPage;

	if ($next_offset > $totalResults) $next_offset = $_REQUEST['pagination_start_id'] + $totalResults;

    $pagination = new \Site\Page\Pagination();
    $pagination->forwardParameters(array('add','update','delete','btn_submit','sort_by','order_by'));
    $pagination->size($recordsPerPage);
    $pagination->count($totalResults);