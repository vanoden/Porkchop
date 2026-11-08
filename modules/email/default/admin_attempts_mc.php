<?php
	$page = new \Site\Page();
	$page->requireRole('email manager');
	$page->title("Email Attempts");
	$page->setAdminMenuSection("Site");
	$page->addBreadcrumb("Email");
	$page->addBreadcrumb("Attempts", "/_email/admin_attempts");
	$page->instructions = "Each row is one try against the mail server, including the response code and the raw server reply.";

	$parameters = array();
	foreach (array(
		'message_code',
		'delivery_code',
		'successful',
		'date_attempt_start',
		'date_attempt_end',
	) as $key) {
		if (!isset($_REQUEST[$key]) || is_array($_REQUEST[$key]) || $_REQUEST[$key] === '') continue;
		$parameters[$key] = $_REQUEST[$key];
	}

	$queue = new \Email\MailQueue\Attempt();
	$attempts = $queue->find($parameters);
	if ($attempts === null) {
		$page->addError($queue->error() ?: "Unable to find attempts");
		$attempts = array();
	}
