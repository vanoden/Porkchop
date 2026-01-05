<?php
######################################################
## admin_account_register_audit_mc.php             ###
## This program displays the register audit tab   ###
## for customer account management.               ###
## Created 11/2025                                 ###
######################################################

$page = new \Site\Page(array("module" => 'register', "view" => 'account'));
$page->requirePrivilege('manage customers');
$page->setAdminMenuSection("Customer");  // Keep Customer section open
$customer = new \Register\Customer();

if (isset($_REQUEST['customer_id']) && preg_match('/^\d+$/', $_REQUEST['customer_id'])) {
	$customer_id = $_REQUEST['customer_id'];
} elseif (preg_match('/^[\w\-\.\_]+$/', $GLOBALS['_REQUEST_']->query_vars_array[0])) {
	$code = $GLOBALS['_REQUEST_']->query_vars_array[0];
	$customer->get($code);
	if ($customer->id)
		$customer_id = $customer->id;
	else
		$page->addError("Customer not found");
} else {
	$customer_id = $GLOBALS['_SESSION_']->customer->id;
}

$page_size_options = [10, 25, 50, 100];
$page_size = 10;
if (!empty($_REQUEST['page_size']) && preg_match('/^\d+$/', $_REQUEST['page_size'])) {
	$page_size_candidate = intval($_REQUEST['page_size']);
	if (in_array($page_size_candidate, $page_size_options, true)) {
		$page_size = $page_size_candidate;
	}
}

$start_offset = 0;
if (!empty($_REQUEST['start']) && preg_match('/^\d+$/', $_REQUEST['start'])) {
	$start_offset = max(0, intval($_REQUEST['start']));
}

app_log($GLOBALS['_SESSION_']->customer->code . " accessing register audit for customer " . $customer_id, 'notice', __FILE__, __LINE__);

if ($customer_id) {
	$customer = new \Register\Customer($customer_id);
}

$authFailureRecords = [];
$totalRecords = 0;
$current_page = 1;
$total_pages = 1;
$prev_offset = 0;
$next_offset = 0;
$last_offset = 0;
$show_start = 0;
$show_end = 0;

if (!empty($customer->id) && !empty($customer->code)) {
	// Query register_auth_failures based on user's login
	$authFailureList = new \Register\AuthFailureList();
	
	// Get total count using a direct COUNT query
	$database = new \Database\Service();
	$count_query = "
		SELECT	COUNT(*)
		FROM	register_auth_failures
		WHERE	login = ?
	";
	$database->AddParam($customer->code);
	$rs = $database->Execute($count_query);
	if ($rs && $row = $rs->FetchRow()) {
		$totalRecords = intval($row[0]);
	} else {
		$page->addError("Error counting records: " . $database->ErrorMsg());
		$totalRecords = 0;
	}
	
	if ($totalRecords > 0) {
		$total_pages = intval(ceil($totalRecords / $page_size));
		$max_start_offset = max(0, ($total_pages - 1) * $page_size);
		if ($start_offset > $max_start_offset) $start_offset = $max_start_offset;
	} else {
		$total_pages = 1;
		$start_offset = 0;
	}
	
	// Get paginated results
	$controls = [
		'limit' => $page_size,
		'offset' => $start_offset
	];
	$authFailureRecords = $authFailureList->findAdvanced(['login' => $customer->code], [], $controls);
	if ($authFailureList->error()) {
		$page->addError($authFailureList->error());
	}
	
	$current_page = $page_size > 0 ? intval(floor($start_offset / $page_size)) + 1 : 1;
	$last_offset = max(0, ($total_pages - 1) * $page_size);
	$prev_offset = $start_offset > 0 ? max(0, $start_offset - $page_size) : 0;
	$next_offset = min($last_offset, $start_offset + $page_size);
	$show_start = $totalRecords > 0 ? $start_offset + 1 : 0;
	$show_end = min($totalRecords, $start_offset + count($authFailureRecords));
}
if ($totalRecords == 0) {
	$show_end = 0;
}

if (!isset($target)) $target = '';

$page->title = "Customer Account Details - Register Audit";
$page->addBreadcrumb("Customer");
$page->addBreadcrumb("Organizations", "/_register/organizations");
$organization = $customer->organization();
if (isset($organization->id)) $page->addBreadcrumb($organization->name, "/_register/admin_organization?id=" . $organization->id);
if (isset($customer->id)) $page->addBreadcrumb($customer->full_name(), "/_register/admin_account?customer_id=" . $customer->id);

