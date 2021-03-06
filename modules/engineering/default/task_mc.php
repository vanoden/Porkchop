<?php
	$page = new \Site\Page();
	$page->requireRole('engineering user');

    // get new and active tasks for the 'prerequisite' field
	$tasklist = new \Engineering\TaskList();
	$tasklist = $tasklist->find(array('status'=>array('NEW', 'ACTIVE')));

    // create new task or get existing if the "code" is passed
	$task = new \Engineering\Task();
	if (isset($_REQUEST['task_id'])) {
		$task = new \Engineering\Task($_REQUEST['task_id']);
	} elseif (isset($_REQUEST['code'])) {
		$task->get($_REQUEST['code']);
		if ($task->error) $page->addError($task->error);
	} elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$code = $GLOBALS['_REQUEST_']->query_vars_array[0];
		$task->get($code);
	}

    // add task comment
    if (isset($_REQUEST['method']) && $_REQUEST['method'] == 'Add Comment') {
        $msgs = array();
		if (isset($_REQUEST['content'])) {
            $engineeringComment = new \Engineering\Comment();
            $engineeringComment->add(array('user_id' => $GLOBALS['_SESSION_']->customer->id, 'code' => $task->code, 'content' => $_REQUEST['content']));
            if ($engineeringComment->error()) $page->addError("Error creating comment: ".$engineeringComment->error());
            array_push($msgs,"Comment added.");
			$event = new \Engineering\Event();
			$event->add(array(
				'task_id'	=> $task->id,
				'person_id'	=> $GLOBALS['_SESSION_']->customer->id,
				'date_added'	=> date('Y-m-d H:i:s'),
				'description'	=> join('<br>',$msgs),
			));
			if ($event->error()) $page->addError("Error creating event: ".$event->error());
		} else {
			$page->addError("Comment required");
		}
    }
    
    // add task testing details
    if (isset($_REQUEST['method']) && $_REQUEST['method'] == 'Testing') {
        $parameters = array();
        $parameters['testing_details'] = $_REQUEST['testing_details'];
        $task->update($parameters);
        $event = new \Engineering\Event();    
		$event->add(array(
			'task_id'	=> $task->id,
			'person_id'	=> $GLOBALS['_SESSION_']->customer->id,
			'date_added'	=> date('Y-m-d H:i:s'),
			'description'	=> 'Testing Instructions Updated',
		));
		if ($event->error()) $page->addError("Error creating event: ".$event->error());
    }

    // edit task or add event
	if (isset($_REQUEST['method']) && in_array($_REQUEST['method'],array('Submit','Add Event'))) {
		$msgs = array();
		$parameters = array();
		if (isset($_REQUEST['title'])) {
			if ($task->title != $_REQUEST['title']) {
				if ($task->title) array_push($msgs,"Title changed to ".$_REQUEST['title']);
				$parameters['title'] = $_REQUEST['title'];
			}
		} else {
			$page->addError("Title required");
		}
		if (! $task->id) {
			$parameters['code'] = $_REQUEST['code'];
			$parameters['status'] = $_REQUEST['status'];
			$parameters['date_added'] = $_REQUEST['date_added'];
			$parameters['requested_id'] = $_REQUEST['requested_id'];
		}
		if (isset($_REQUEST['type']) && $task->type != strtoupper($_REQUEST['type'])) {
			array_push($msgs,"Type changed from ".$task->type." to ".$_REQUEST['type']);
			$parameters['type'] = $_REQUEST['type'];
		}
		if (isset($_REQUEST['estimate']) && $task->estimate != $_REQUEST['estimate']) {
			array_push($msgs,"Estimate changed from ".$task->estimate." to ".$_REQUEST['estimate']);
			$parameters['estimate'] = $_REQUEST['estimate'];
		}
		if (isset($_REQUEST['priority']) && $task->priority != strtoupper($_REQUEST['priority'])) {
			array_push($msgs,"Priority changed from ".$task->priority." to ".$_REQUEST['priority']);
			$parameters['priority'] = $_REQUEST['priority'];
		}
		if (isset($_REQUEST['description']) && $task->description != $_REQUEST['description']) {
			array_push($msgs,"Description updated");
			$parameters['description'] = $_REQUEST['description'];
		}
		if (isset($_REQUEST['prerequisite_id']) && $task->prerequisite_id != $_REQUEST['prerequisite_id']) {
			array_push($msgs,"Prerequisite updated");
			$parameters['prerequisite_id'] = $_REQUEST['prerequisite_id'];
		}

		# Store Old Values to Recognize Change
		$old_id = $task->id;
		$old_release = $task->release();
		$old_tech = $task->assignedTo();
		$old_product = $task->product();
		$old_project = $task->project();

		if (isset($_REQUEST['release_id']) && $_REQUEST['release_id'] > 0 && $old_release->id != $_REQUEST['release_id']) {
			$new_release = new \Engineering\Release($_REQUEST['release_id']);
			array_push($msgs,"Release changed from ".$old_release->title." to ".$new_release->title);
			$parameters['release_id'] = $_REQUEST['release_id'];
		}

		if (isset($_REQUEST['assigned_id']) && $old_tech->id != $_REQUEST['assigned_id']) {
			$new_tech = new \Register\Admin($_REQUEST['assigned_id']);
			if (isset($old_tech->id) && $old_tech->id != $new_tech->id) {
				array_push($msgs,"Task re-assigned to ".$new_tech->first_name." ".$new_tech->last_name);
				$parameters['assigned_id'] = $_REQUEST['assigned_id'];
				$tech_status = "Re-Assigned";
			} else {
				array_push($msgs,"Task assigned to ".$new_tech->first_name." ".$new_tech->last_name);
				$parameters['assigned_id'] = $_REQUEST['assigned_id'];
				$tech_status = "Assigned";
			};
		}
		if ($old_product->id != $_REQUEST['product_id']) {
			$new_product = new \Engineering\Product($_REQUEST['product_id']);
			array_push($msgs,"Product changed from ".$old_product->title." to ".$new_product->title);
			$parameters['product_id'] = $_REQUEST['product_id'];
		}
		if (isset($_REQUEST['date_due']) && $task->date_due != get_mysql_date($_REQUEST['date_due'])) {
			array_push($msgs,"Due Date changed from ".$task->date_due." to ".$_REQUEST['date_due']);
			$parameters['date_due'] = $_REQUEST['date_due'];
		}
		if ($old_project->id != $_REQUEST['project_id']) {
			$new_project = new \Engineering\Project($_REQUEST['project_id']);
			array_push($msgs,"Project changed from '".$old_project->title."' to '".$new_project->title."'");
			$parameters['project_id'] = $_REQUEST['project_id'];
		}

		app_log("Submitted task form",'debug',__FILE__,__LINE__);
		if ($task->id) {
			# Task Exists, Update
			if ($task->update($parameters)) {
				$page->success = "Updates applied";
				$statusLogged = "";
				if (isset($parameters['status'])) $statusLogged = $parameters['status'];
				app_log("Task updated, status now ".$statusLogged,'debug',__FILE__,__LINE__);
			} else {
				$page->addError("Error saving updates: ".$task->error());
			}
			if (count($msgs) > 0) {
				$event = new \Engineering\Event();
				$event->add(array(
					'task_id'	=> $task->id,
					'person_id'	=> $GLOBALS['_SESSION_']->customer->id,
					'date_added'	=> date('Y-m-d H:i:s'),
					'description'	=> join('<br>',$msgs),
					'hours_worked'	=> 0
				));
				if ($event->error()) $page->addError("Error creating event: ".$event->error());
			}
		}
		else {
			# Create New Task
			if ($task->add($parameters)) {
				$page->success = "Task Created";
				$event = new \Engineering\Event();
				$event->add(array(
					'task_id'	=> $task->id,
					'person_id'	=> $GLOBALS['_SESSION_']->customer->id,
					'date_added'	=> date('Y-m-d H:i:s'),
					'description'	=> 'Task created',
					'hours_worked'	=> 0
				));
				app_log("Task created",'debug',__FILE__,__LINE__);
			} else {
				$page->addError("Error creating task: ".$task->error());
			}
		}

		if (is_numeric($task->id) && $task->id > 0) {
		
			/************************************/
			/* Email Internal Notifications		*/
			/************************************/
			app_log("Generating email notification from template");
			// Get Objects
			$product = $task->product();
			$project = $task->project();
			$requestedBy = $task->requestedBy();

			// Get Template File
			$internal_notification = $GLOBALS['_config']->engineering->internal_notification;
			if (! isset($GLOBALS['_config']->engineering->internal_notification)) {
				$page->error = "Notification template not configured";
				app_log("config->engineering->internal_notification not set!",'error');
			}
			elseif (! file_exists($internal_notification->template)) {
				$page->error = "Support Internal Email Template '".$internal_notification->template."' not found";
				app_log("File '".$internal_notification->template."' not found! Set in config->engineering->internal_notification setting",'error');
			}
			else {
				try {
					$notice_content = file_get_contents($internal_notification->template);
				}
				catch (Exception $e) {
					app_log("Email template load failed: ".$e->getMessage(),'error',__FILE__,__LINE__);
					$page->error = "Template load failed.  Try again later";
					return;
				}

				// Create Template
				app_log("Populating notification email");
				$url = '';
				if ($GLOBALS['_config']->site->https) $url = "https://".$GLOBALS['_config']->site->hostname."/_engineering/task/".$task->code;
				else "http://".$GLOBALS['_config']->site->hostname."/_engineering/task/".$task->code;
				if ($project->id > 0) $project_title = $project->title;
				else $project_title = "None Selected";
				if ($product->id > 0) $product_title = $product->title;
				else $product_title = "None Selected";
				$notice_template = new \Content\Template\Shell();
				$notice_template->content($notice_content);
				$notice_template->addParam('TASK.TITLE',$task->title);
				$notice_template->addParam('PRODUCT.TITLE',$product_title);
				$notice_template->addParam('PROJECT.TITLE',$project_title);
				$notice_template->addParam('TASK.PRIORITY',$task->priority);
				$notice_template->addParam('TASK.TYPE',$task->type);
				$notice_template->addParam('TASK.DATE_DUE',$task->date_due);
				$notice_template->addParam('TASK.REQUESTED_BY',$requestedBy->full_name());
				$notice_template->addParam('TASK.INTERNAL_LINK',$url);
				$notice_template->addParam('TASK.DESCRIPTION',$task->description);

				$message = new \Email\Message();
				$message->from($internal_notification->from);
				$message->html(true);
	
				if (isset($old_id)) {
					// Existing Task
					if (isset($tech_status)) {
						$tech = $task->assignedTo();
						app_log("Notifying tech ".$tech->login." of updated assignment");
						$notice_template->addParam('MESSAGE',"The following task was $tech_status to you");
						$message->subject("[ENGINEERING] Task #".$task->id." assigned to you");
						$message->body($notice_template->output());
						$tech->notify($message);
					}
				}
				else {
					// New Task
					$tech = $task->assignedTo();
					if ($tech->id > 0) {
						// Assigned
						app_log("Notifying tech ".$tech->login." of new assignment");
						$notice_template->addParam('MESSAGE',"The following new task was assigned to you");
						$message->subject("[ENGINEERING] Task #".$task->id." assigned to you");
						$message->body($notice_template->output());
						$tech->notify($message);
					}
					else {
						// Not Assigned
						$role = new \Register\Role();
						if ($role->get('engineering manager')) {
							app_log("Notifying ".$role->name." of new, unassigned task");
							$notice_template->addParam('MESSAGE',"The following new task is not assigned");
							$message->subject("[ENGINEERING] Task #".$task->id." was created");
							$message->body($notice_template->output());
							$role->notify($message);
						}
						else {
							$page->addError("engineering manager role doesn't exist so no notifications were sent");
							app_log("engineering manager role doesn't exist so no notifications were sent",'notice');
						}
					}
				}
			}
		}
    
		if (strtoupper($_REQUEST['new_status']) != $task->status) {
    		if (empty($_REQUEST['notes'])) $_REQUEST['notes'] = "";
			$old_status = $task->status;
			$task->update(array('status'=>$_REQUEST['new_status']));
			if (isset($task->error)) {
				$page->addError($task->error);
			} else {
				$_REQUEST['notes'] = "Status changed from $old_status to ".strtoupper($_REQUEST['new_status'])."<br>\n".$_REQUEST['notes'];
				$page->success = "Updated applied successfully";
			}
		} else {
			$page->success = "Updated applied successfully";
		}
		$event = new \Engineering\Event();
		$event->add(array(
			'task_id'		=> $task->id,
			'person_id'		=> $_REQUEST['event_person_id'],
			'date_added'	=> $_REQUEST['date_event'],
			'description'	=> $_REQUEST['notes'],
			'hours_worked'	=> $_REQUEST['hours_worked']
		));
		if ($event->error()) $page->addError($event->error());
	}

    // upload files if upload button is pressed
    $configuration = new \Site\Configuration('engineering_attachments_s3');
    $repository = $configuration->value();
    if (isset($_REQUEST['btn_upload']) && $_REQUEST['btn_upload'] == 'Upload') {
	    $file = new \Storage\File();
	    $parameters = array();
        $parameters['repository_name'] = $_REQUEST['repository_name'];
        $parameters['type'] = $_REQUEST['type'];
        $parameters['ref_id'] = $task->id;
	    $uploadResponse = $file->upload($parameters);
	    
	    if (!empty($file->error)) $page->addError($file->error);
	    if (!empty($file->success)) $page->success = $file->success;
	}
	
	$filesList = new \Storage\FileList();
	$filesUploaded = $filesList->find(array('type' => 'engineering task', 'ref_id' => $task->id));
	
	$peopleList = new \Register\CustomerList();
	$people = $peopleList->find(array("status" => array('NEW','ACTIVE')));
	
	if (isset($peoplelist->error)) $page->addError($peoplelist->error);

	$role = new \Register\Role();
	$role->get("engineering user");
	$techs = $role->members();

	$productlist = new \Engineering\ProductList();
	$products = $productlist->find();
	if ($productlist->error()) $page->addError($productlist->error());

	$releaselist = new \Engineering\ReleaseList();
	$releases = $releaselist->find();
	if ($releaselist->error()) $page->addError($releaselist->error());

	$projectlist = new \Engineering\ProjectList();
	$projects = $projectlist->find();
	if ($projectlist->error()) $page->addError($projectlist->error());
	
	// get engineering comments 
    $engineeringComments = new \Engineering\CommentList();
	$commentsList = $engineeringComments->find(array('code'=>$task->code));
	
    // get engineering hours logged 
    $hoursList = new \Engineering\HoursList();
	$hoursLoggedList = $hoursList->find(array('code'=>$task->code));
	
	$form = array();
	if ($task->id) {
		$task->details();
		$form['code'] = $task->code;
		$form['title'] = $task->title;
		$form['estimate'] = $task->estimate;
		$form['priority'] = $task->priority;
		$product = $task->product();
		$form['product_id'] = $product->id;
		$requestor = $task->requestedBy();
		$form['requested_id'] = $requestor->id;
		$worker = $task->assignedTo();
		$form['assigned_id'] = $worker->id;
		$form['date_added'] = $task->date_added;
		$form['date_due'] = $task->date_due;
		$form['type'] = $task->type;
		$form['status'] = $task->status;
		$form['description'] = $task->description;
		$release = $task->release();
		$form['release_id'] = $release->id;
		$project = $task->project();
		$form['project_id'] = $project->id;
        $form['prerequisite_id'] = $task->prerequisite_id;
        $form['testing_details'] = $task->testing_details;
		$eventlist = new \Engineering\EventList();
		$events = $eventlist->find(array('task_id'=> $task->id));
		if ($eventlist->error()) $page->addError($eventlist->error());
	} elseif ($page->errorCount()) {
		$form['code'] = $_REQUEST['code'];
		$form['title'] = $_REQUEST['title'];
		$form['estimate'] = $_REQUEST['estimate'];
		$form['priority'] = $_REQUEST['priority'];
		$form['product_id'] = $_REQUEST['product_id'];
		$form['requested_id'] = $_REQUEST['requested_id'];
		$form['assigned_id'] = $_REQUEST['assigned_id'];
		$form['date_added'] = $_REQUEST['date_added'];
		$form['date_due'] = $_REQUEST['date_due'];
		$form['type'] = $_REQUEST['type'];
		$form['status'] = $_REQUEST['status'];
		$form['description'] = $_REQUEST['description'];
		$form['release_id'] = $_REQUEST['release_id'];
		$form['prerequisite_id'] = $_REQUEST['prerequisite_id'];
		$form['testing_details'] = $_REQUEST['testing_details'];
	} else {
		$form['code'] = uniqid();
		$form['product_id'] = '';
		$form['date_added'] = 'now';
		$form['date_due'] = '';
		$form['estimate'] = 0;
		$form['type'] = '';
		$form['status'] = '';
		$form['priority'] = '';
		$form['assigned_id'] = '';
		$form['requested_id'] = $GLOBALS['_SESSION_']->customer->id;
		$form['description'] = '';
		$form['project_id'] = '';
		$form['prerequisite_id'] = '';
		$form['release_id'] = '';
		$task->date_added = 'now';
	}
