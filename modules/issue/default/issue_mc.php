<?php
	$statuses = array('NEW','HOLD','OPEN','CANCELLED','COMPLETE','REOPENED','APPROVED');
	$priorities = array('NORMAL','IMPORTANT','CRITICAL','EMERGENCY');

	if (isset($_REQUEST['id'])) {
		$issue = new \Issue\Issue($_REQUEST['id']);
	}
	elseif (isset($_REQUEST['code'])) {
		$issue = new \Issue\Issue();
		$issue->get($_REQUEST['code']);
	}
	elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$issue = new \Issue\Issue();
		$issue->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
	}
	else {
		$isse = new \Issue\Issue();
	}

	if ($_REQUEST['btn_submit']) {
		if ($issue->id) {
			$issue->update(
				array(
					'title'				=> $_REQUEST['title'],
					'description'		=> $_REQUEST['description'],
					'user_assigned_id'	=> $_REQUEST['user_assigned_id'],
					'status'			=> $_REQUEST['status'],
					'priority'			=> $_REQUEST['priority']
				)
			);
			if ($issue->error()) {
				$page->error = "Error updating issue: ".$issue->error();
			}
		}
		else {
			$issue->add(
				array(
					'title'				=> $_REQUEST['title'],
					'description'		=> $_REQUEST['description'],
					'status'			=> 'NEW',
					'priority'			=> $_REQUEST['priority']
				)
			);
			if ($issue->error()) {
				$page->error = "Error creating issue: ".$issue->error();
			}
		}
	}
	
	$techRole = new \Register\Role();
	$techRole->get('issue admin');
	$techlist = $techRole->members();
?>