<?php
	if (! $GLOBALS['_SESSION_']->customer->has_role('engineering user')) {
		$page->error = "Permission Denied";
		return;
	}
	$page = new \Site\Page();
	$release = new \Engineering\Release();
	
	if ($_REQUEST['release_id']) {
		$release = new \Engineering\Release($_REQUEST['release_id']);
	}
	elseif (isset($_REQUEST['code'])) {
		$release->get($_REQUEST['code']);
		if ($release->error) $page->error = $release->error;
	}
	elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$code = $GLOBALS['_REQUEST_']->query_vars_array[0];
		$release->get($code);
	}

	if (isset($_REQUEST['btn_submit'])) {
		$parameters = array();
		if (isset($_REQUEST['title'])) $parameters['title'] = $_REQUEST['title'];
		else {
			$page->error = "Title required";
		}
		if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
		if (isset($_REQUEST['description'])) $parameters['description'] = $_REQUEST['description'];
		if (isset($_REQUEST['code'])) $parameters['code'] = $_REQUEST['code'];
		if (isset($_REQUEST['date_released'])) $parameters['date_released'] = $_REQUEST['date_released'];
		if (isset($_REQUEST['date_scheduled'])) $parameters['date_scheduled'] = $_REQUEST['date_scheduled'];

		app_log("Submitted task form",'debug',__FILE__,__LINE__);
		if ($release->id) {
			if ($release->update($parameters)) {
				$page->success = "Updates applied";
				app_log("Release updated",'debug',__FILE__,__LINE__);
			}
			else {
				$page->error = "Error saving updates: ".$release->error();
			}
		}
		else {
			if ($release->add($parameters)) {
				$page->success = "Release Created";
				app_log("Release created",'debug',__FILE__,__LINE__);
			}
			else {
				$page->error = "Error creating task: ".$release->error();
			}
		}
	}

	if ($release->id) {
		$release->details();
		$form['code'] = $release->code;
		$form['title'] = $release->title;
		$form['date_released'] = $release->date_released;
		$form['date_scheduled'] = $release->date_scheduled;
		$form['status'] = $release->status;
		$form['description'] = $release->description;
	}
	elseif ($page->error) {
		$form['code'] = $_REQUEST['code'];
		$form['title'] = $_REQUEST['title'];
		$form['date_released'] = $_REQUEST['date_released'];
		$form['date_scheduled'] = $_REQUEST['date_scheduled'];
		$form['status'] = $_REQUEST['status'];
		$form['description'] = $_REQUEST['description'];
	}
	else {
	}
?>