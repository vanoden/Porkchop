<?php
	$page = new \Site\Page();
	$page->requireRole('email manager');
	$page->title("Email Messages");
	$page->setAdminMenuSection("Site");
	$page->addBreadcrumb("Email");
	$page->addBreadcrumb("Messages", "/_email/admin_messages");
	$page->instructions = "Filter queued messages, then open one to see each recipient delivery.";

	$parameters = array();
	foreach (array(
		'user_code',
		'from_address',
		'date_attempt_start',
		'date_attempt_end',
		'date_queued_start',
		'date_queued_end',
		'mime_type',
		'count_attempt_more',
		'count_attempt_less',
	) as $key) {
		if (!isset($_REQUEST[$key]) || is_array($_REQUEST[$key]) || $_REQUEST[$key] === '') continue;
		$parameters[$key] = $_REQUEST[$key];
	}

	$queue = new \Email\MailQueue\Message();
	$messages = $queue->find($parameters);
	if ($messages === null) {
		$page->addError($queue->error() ?: "Unable to find messages");
		$messages = array();
	}
