<?php
	$page = new \Site\Page();
	$page->requireRole('engineering user');

	$project = new \Engineering\Project();
	
	if ($_REQUEST['project_id']) $project = new \Engineering\Project($_REQUEST['project_id']);
	elseif (isset($_REQUEST['code'])) {
		$project->get($_REQUEST['code']);
		if ($project->error) $page->error = $project->error;
	} elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$code = $GLOBALS['_REQUEST_']->query_vars_array[0];
		$project->get($code);
	}

	if (isset($_REQUEST['btn_submit']) && $_REQUEST['btn_submit'] == "Submit") {
		$parameters = array();
		if (isset($_REQUEST['title'])) {
			$parameters['title'] = $_REQUEST['title'];
			if (isset($_REQUEST['description'])) $parameters['description'] = $_REQUEST['description'];
			if (isset($_REQUEST['manager_id'])) $parameters['manager_id'] = $_REQUEST['manager_id'];
			if (isset($_REQUEST['code'])) $parameters['code'] = $_REQUEST['code'];
			if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];

			app_log("Submitted project form",'debug',__FILE__,__LINE__);
			if ($project->id) {
				if ($project->update($parameters)) {
					$page->success = "Updates applied";
					app_log("Project updated",'debug',__FILE__,__LINE__);
				} else {
					$page->error = "Error saving updates to project ".$project->id.": ".$project->error();
				}
			}
			else {
				if ($project->add($parameters)) {
					$page->success = "Project Created";
					app_log("Project created",'debug',__FILE__,__LINE__);
				} else {
					$page->addError("Error creating project: ".$project->error());
				}
			}
		}
		else {
			$page->addError("Title required");
		}
	}

    $filesList = new \Storage\FileList();
    $filesUploaded = array();
	if ($project->id) {
		$project->details();
		$form['code'] = $project->code;
		$form['title'] = $project->title;
		$form['description'] = $project->description;
		$form['status'] = $project->status;
		$form['manager_id'] = $project->manager->id;
    	$filesUploaded = $filesList->find(array('type' => 'engineering project', 'ref_id' => $project->id));
	} elseif ($page->errorCount()) {
		$form['code'] = $_REQUEST['code'];
		$form['title'] = $_REQUEST['title'];
		$form['status'] = $_REQUEST['status'];
		$form['description'] = $_REQUEST['description'];
		$form['manager_id'] = $_REQUEST['manager_id'];
	}
	
	// upload files if upload button is pressed
	$configuration = new \Site\Configuration('engineering_attachments_s3');
	$repository = $configuration->value();

	if (isset($_REQUEST['btn_submit']) && $_REQUEST['btn_submit'] == 'Upload') {
		$file = new \Storage\File();
		$parameters = array();
		$parameters['repository_name'] = $_REQUEST['repository_name'];
		$parameters['type'] = $_REQUEST['type'];
		$parameters['ref_id'] = $project->id;
		$uploadResponse = $file->upload($parameters);
	    
		if (!empty($file->error)) $page->addError($file->error);
		if (!empty($file->success)) $page->success = $file->success;
	}

	$role = new \Register\Role();
	$role->get("engineering user");
	$managers = $role->members();

	$tasklist = new \Engineering\TaskList();
	$tasks = $tasklist->find(array('project_id' => $project->id));
	if ($tasklist->error()) $page->addError($tasklist->error());
