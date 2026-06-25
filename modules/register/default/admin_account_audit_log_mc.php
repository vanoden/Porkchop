<?php
######################################################
## admin_account_audit_log_mc.php                  ###
## This program displays the audit log tab for     ###
## customer account management.                    ###
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

$auditClient = new \Site\AuditLog();
$classList = $auditClient->classes();
sort($classList);
$current_class = null;
if (!empty($_REQUEST['class_name']) && in_array($_REQUEST['class_name'], $classList, true)) {
	$current_class = $_REQUEST['class_name'];
}

app_log($GLOBALS['_SESSION_']->customer->code . " accessing account audit log for customer " . $customer_id, 'notice', __FILE__, __LINE__);

if ($customer_id) {
	$customer = new \Register\Customer($customer_id);
}

$auditRecords = [];
$totalRecords = 0;
$show_start = 0;
$show_end = 0;
$pagination = new \Site\Page\Pagination();
$pagination->baseURI = PATH.'/_register/admin_account_audit_log';
$pagination->startElemName('start');
$pagination->sizeElemName('page_size');

if (!empty($customer->id)) {
	$totalRecords = $auditClient->countEvents($customer->id, $current_class);
	if ($auditClient->error()) {
		$page->addError($auditClient->error());
	}

	if ($totalRecords > 0) {
		$max_start_offset = max(0, (intval(ceil($totalRecords / $page_size)) - 1) * $page_size);
		if ($start_offset > $max_start_offset) $start_offset = $max_start_offset;
	} else {
		$start_offset = 0;
	}

	$auditList = new \Site\AuditLog\EventList();
	$find_parameters = ['instance_id' => $customer->id];
	if ($current_class) {
		$find_parameters['class_name'] = $current_class;
	}
	$auditRecords = $auditList->find(
		$find_parameters,
		[
			'sort' => 'event_date',
			'order' => 'desc',
			'limit' => $page_size,
			'offset' => $start_offset
		]
	);
	if ($auditList->error()) {
		$page->addError($auditList->error());
	}

	$show_start = $totalRecords > 0 ? $start_offset + 1 : 0;
	$show_end = min($totalRecords, $start_offset + count($auditRecords));

	$pagination->startId($start_offset);
	$pagination->size($page_size);
	$pagination->count($totalRecords);
	$pagination->forwardParameters(array('customer_id', 'class_name'));
}
if ($totalRecords == 0) {
	$show_end = 0;
}

if (!isset($target)) $target = '';

$page->title = "Customer Account Details - Audit Log";
$page->addBreadcrumb("Customer");
$page->addBreadcrumb("Organizations", "/_register/organizations");
$organization = $customer->organization();
if (isset($organization->id)) $page->addBreadcrumb($organization->name, "/_register/admin_organization?id=" . $organization->id);
if (isset($customer->id)) $page->addBreadcrumb($customer->full_name(), "/_register/admin_account?customer_id=" . $customer->id);
