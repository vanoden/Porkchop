<?php
	$page = new \Site\Page();
	$page->requireRole('manage engineering releases');

	$release = new \Engineering\Release();
	
	if ($_REQUEST['release_id']) {
		$release = new \Engineering\Release($_REQUEST['release_id']);
	} elseif (isset($_REQUEST['code'])) {
		$release->get($_REQUEST['code']);
		if ($release->error) $page->error = $release->error;
	} elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$code = $GLOBALS['_REQUEST_']->query_vars_array[0];
		$release->get($code);
	}
	
	if (isset($_REQUEST['btn_submit']) && $_REQUEST['btn_submit'] == 'Submit') {
		$parameters = array();
		if (isset($_REQUEST['title'])) $parameters['title'] = $_REQUEST['title'];
		else {
			$page->addError("Title required");
		}
		if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
		if (isset($_REQUEST['description'])) $parameters['description'] = $_REQUEST['description'];
		if (isset($_REQUEST['code'])) $parameters['code'] = $_REQUEST['code'];
		if (isset($_REQUEST['date_released'])) $parameters['date_released'] = $_REQUEST['date_released'];
		if (isset($_REQUEST['date_scheduled'])) $parameters['date_scheduled'] = $_REQUEST['date_scheduled'];
		
		// handle package version integer
		$parameters['package_version_id'] = 0;
		if (isset($_REQUEST['package_version_id']) && is_int(isset($_REQUEST['package_version_id']))) $parameters['package_version_id'] = $_REQUEST['package_version_id'];
    
		app_log("Submitted task form",'debug',__FILE__,__LINE__);
		if ($release->id) {
			if ($release->update($parameters)) {
				$page->success = "Updates applied";
				app_log("Release updated",'debug',__FILE__,__LINE__);
			} else {
				$page->addError("Error saving updates: ".$release->error());
			}
		}
		else {
			if ($release->add($parameters)) {
				$page->success = "Release Created";
				app_log("Release created",'debug',__FILE__,__LINE__);
			} else {
				$page->addError("Error creating task: ".$release->error());
			}
		}
	}

	// if a request to postpone a task from the release, then process
	if (isset($_REQUEST['postpone'])) {
	
	    // get task in question to postpone from release, 0 means remove/set null
		$task = new \Engineering\Task();
		$task->get($_REQUEST['postpone']);
		$task->update(array('release_id'=> 0));
		$page->success = "Task " . $_REQUEST['postpone'] . " has been postponed from this release.";
		app_log("Task Postponed",'debug',__FILE__,__LINE__);
	}

	if ($release->id) {
		$release->details();
		$form['code'] = $release->code;
		$form['title'] = $release->title;
		$form['date_released'] = $release->date_released;
		$form['date_scheduled'] = $release->date_scheduled;
		$form['status'] = $release->status;
		$form['description'] = $release->description;
		$tasklist = new \Engineering\TaskList();
		$tasks = $tasklist->find(array('release_id' => $release->id));
	} elseif ($page->errorCount()) {
		$form['code'] = $_REQUEST['code'];
		$form['title'] = $_REQUEST['title'];
		$form['date_released'] = $_REQUEST['date_released'];
		$form['date_scheduled'] = $_REQUEST['date_scheduled'];
		$form['status'] = $_REQUEST['status'];
		$form['description'] = $_REQUEST['description'];
	}
	
    // upload files if upload button is pressed
    $configuration = new \Site\Configuration('engineering_attachments_s3');
    $repository = $configuration->value();
    if (isset($_REQUEST['btn_submit']) && $_REQUEST['btn_submit'] == 'Upload') {

	    $file = new \Storage\File();
	    $parameters = array();
        $parameters['repository_name'] = $_REQUEST['repository_name'];
        $parameters['type'] = $_REQUEST['type'];
        $parameters['ref_id'] = $release->id;
	    $uploadResponse = $file->upload($parameters);
	    
	    if (!empty($file->error)) $page->addError($file->error);
	    if (!empty($file->success)) $page->success = $file->success;
	}
	
	$filesList = new \Storage\FileList();
	$filesUploaded = $filesList->find(array('type' => 'engineering release', 'ref_id' => $release->id));

	$packageList = new \Package\PackageList();
	$packages = $packageList->find();

	if ($release->package_version_id) {
		$version = new \Package\Version($release->package_version_id);
		$_REQUEST['package_version_id'] = $version->id;
		$_REQUEST['package_id'] = $version->package_id;
	}

	if ($_REQUEST['package_id'] > 0) {
		$versionList = new \Package\VersionList();
		$versions = $versionList->find(array("package_id" => $_REQUEST['package_id']));
	}
	else {
		$versions = array();
	}
