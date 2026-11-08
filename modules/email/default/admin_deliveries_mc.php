<?php
	$page = new \Site\Page();
	$page->requireRole('email manager');
	$page->title("Email Deliveries");
	$page->setAdminMenuSection("Site");
	$page->addBreadcrumb("Email");
	$page->addBreadcrumb("Deliveries", "/_email/admin_deliveries");
	$page->instructions = "Each row is one recipient. QUEUED and ERROR rows are what the processor sends. A seventh failure is marked FAILED.";

	$parameters = array();
	foreach (array(
		'message_code',
		'from_address',
		'to_address',
		'date_queued_start',
		'date_queued_end',
		'last_attempt_start',
		'last_attempt_end',
	) as $key) {
		if (!isset($_REQUEST[$key]) || is_array($_REQUEST[$key]) || $_REQUEST[$key] === '') continue;
		$parameters[$key] = $_REQUEST[$key];
	}
	if (!empty($_REQUEST['status']) && is_array($_REQUEST['status'])) {
		$parameters['status'] = $_REQUEST['status'];
	}

	$queue = new \Email\MailQueue\Delivery();
	$deliveries = $queue->find($parameters);
	if ($deliveries === null) {
		$page->addError($queue->error() ?: "Unable to find deliveries");
		$deliveries = array();
	}

	$selectedStatuses = array();
	if (!empty($_REQUEST['status']) && is_array($_REQUEST['status'])) {
		foreach ($_REQUEST['status'] as $status) $selectedStatuses[] = strtoupper((string)$status);
	}
	$deliveryStatuses = array('QUEUED', 'ERROR', 'CANCELLED', 'FAILED', 'DELIVERED');
