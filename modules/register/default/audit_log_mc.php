<?php
	$site = new \Site();
	$page = $site->page();

	$page->requirePrivilege('manage customers');

	$records = [];
	if (isset($_REQUEST['btn_submit']) || isset($_REQUEST['user_id']) || isset($_REQUEST['admin_id'])) {
		$parameters = [];
		if (!empty($_REQUEST['user_id']) && preg_match('/^\d+$/', $_REQUEST['user_id'])) $parameters['instance_id'] = $_REQUEST['user_id'];
		if (!empty($_REQUEST['admin_id']) && preg_match('/^\d+$/', $_REQUEST['admin_id'])) $parameters['customer_id'] = $_REQUEST['admin_id'];
		if (!empty($_REQUEST['event_class'])) $parameters['description'] = $_REQUEST['event_class'];

		$recordList = new \Site\AuditLog\EventList();
		$records = $recordList->find($parameters, array('sort' => 'event_date', 'order' => 'desc'));
		if ($recordList->error()) $page->addError($recordList->error());
	}
