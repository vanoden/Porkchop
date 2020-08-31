<?php
	$page = new \Site\Page();
	$page->requireRole('monitor admin');

	$dashboard = new \Monitor\Dashboard($_REQUEST['id']);

	if (isset($_REQUEST['btn_submit'])) {
		$parameters = array(
			'name'	=> $_REQUEST['name'],
			'template'	=> $_REQUEST['template'],
			'status'	=> $_REQUEST['status']
		);
		if (! $dashboard->add($parameters)) {
			$page->addError($dashboard->error());
		}
		header('location: /_monitor/admin_dashboard?id='.$dashboard->id);
		return;
	}
