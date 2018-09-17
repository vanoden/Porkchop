<?php
	if (! $GLOBALS['_SESSION_']->customer->has_role('engineering user')) {
		$page->error = "Permission Denied";
		return;
	}
	$page = new \Site\Page();
	$project = new \Engineering\Project();
	
	if ($_REQUEST['project_id']) {
		$project = new \Engineering\Project($_REQUEST['project_id']);
	}
	elseif (isset($_REQUEST['code'])) {
		$project->get($_REQUEST['code']);
		if ($project->error) $page->error = $project->error;
	}
	elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$code = $GLOBALS['_REQUEST_']->query_vars_array[0];
		$project->get($code);
	}
	else {
	}

	if (isset($_REQUEST['btn_submit'])) {
		$parameters = array();
		if (isset($_REQUEST['title'])) {
			$parameters['title'] = $_REQUEST['title'];
			if (isset($_REQUEST['description'])) $parameters['description'] = $_REQUEST['description'];
			if (isset($_REQUEST['manager_id'])) $parameters['manager_id'] = $_REQUEST['manager_id'];
			if (isset($_REQUEST['code'])) $parameters['code'] = $_REQUEST['code'];

			app_log("Submitted project form",'debug',__FILE__,__LINE__);
			if ($project->id) {
				if ($project->update($parameters)) {
					$page->success = "Updates applied";
					app_log("Project updated",'debug',__FILE__,__LINE__);
				}
				else {
					$page->error = "Error saving updates to project ".$project->id.": ".$project->error();
				}
			}
			else {
				if ($project->add($parameters)) {
					$page->success = "Project Created";
					app_log("Project created",'debug',__FILE__,__LINE__);
				}
				else {
					$page->error = "Error creating project: ".$project->error();
				}
			}
		}
		else {
			$page->error = "Title required";
		}
	}

	if ($project->id) {
		$project->details();
		$form['code'] = $project->code;
		$form['title'] = $project->title;
		$form['description'] = $project->description;
		$form['manager_id'] = $project->manager->id;
	}
	elseif ($page->error) {
		$form['code'] = $_REQUEST['code'];
		$form['title'] = $_REQUEST['title'];
		$form['description'] = $_REQUEST['description'];
		$form['manager_id'] = $_REQUEST['manager_id'];
	}
	else {
	}

	$managerList = new \Register\CustomerList();
	$managers = $managerList->find();
?>
