<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage customers');
	
	if (!empty($_REQUEST['id']) && empty($_REQUEST['organization_id'])) $_REQUEST['organization_id'] = $_REQUEST['id'];

	# Security - Only Register Module Operators or Managers can see other customers
	if ($GLOBALS['_SESSION_']->customer->can('manage customers')) {
		if (isset($_REQUEST['organization_id']) && preg_match('/^\d+$/',$_REQUEST['organization_id'])) {
			$organization = new \Register\Organization($_REQUEST['organization_id']);
			if ($organization->error()) $page->addError("Unable to load organization: ".$organization->error());
		}
		elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0]) && preg_match('/^[\w\-\.\_]+$/',$GLOBALS['_REQUEST_']->query_vars_array[0])) {
			$code = $GLOBALS['_REQUEST_']->query_vars_array[0];
			$organization = new \Register\Organization();
			if ($organization->validCode($code)) {
				$organization->get($code);
				if (! $organization->id) $page->addError("Organization not found");
			}
			else {
				$page->addError("Invalid organization code");
			}
		}
		else $organization = new \Register\Organization();
	}
	else $organization = $GLOBALS['_SESSION_']->customer->organization();

	// Load audit log data
	if ($organization->id) {
		$parameters = array('organization_id' => $organization->id);
		$recordList = new \Register\OrganizationAuditEventList();
		$records = $recordList->find($parameters, array('sort' => 'event_date', 'order' => 'desc'));
		if ($recordList->error()) $page->addError($recordList->error());
		
		// Load users for display
		$users = array();
		if (!empty($records)) {
			foreach ($records as $record) {
				if (!isset($users[$record->admin_id])) {
					$user = $record->user();
					$users[$record->admin_id] = $user;
				}
			}
		}
	}
