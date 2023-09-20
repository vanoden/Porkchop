<?php
	$site = new \Site();
	$page = $site->page();

	$page->requirePrivilege('manage customers');

	if (isset($_REQUEST['btn_submit']) || isset($_REQUEST['user_id']) || isset($_REQUEST['admin_id'])) {
		$parameters = [];
		if (!empty($_REQUEST['user_id'])) $parameters['user_id'] = $_REQUEST['user_id'];
		if (!empty($_REQUEST['admin_id'])) $parameters['admin_id'] = $_REQUEST['admin_id'];
		if (!empty($_REQUEST['event_class'])) $parameters['event_class'] = $_REQUEST['event_class'];

		$recordList = new \Register\UserAuditEventList();
		$records = $recordList->find($parameters,array('sort' => 'event_date', 'order' => 'desc'));
		if ($recordList->error()) $page->addError($recordList->error());
	}
