<?php
	$page = new \Site\Page();
	$page->requireRole('engineering user');

    // get new and active tasks for the 'prerequisite' field
	$tasklist = new \Engineering\TaskList();
	$tasklist = $tasklist->find(array('status'=>array('NEW', 'ACTIVE')));

    // create new task or get existing if the "code" is passed
	$task = new \Engineering\Task();
	if ($_REQUEST['task_id']) {
		$task = new \Engineering\Task($_REQUEST['task_id']);
	} elseif (isset($_REQUEST['code'])) {
		$task->get($_REQUEST['code']);
		if ($task->error) $page->addError($task->error);
	} elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$code = $GLOBALS['_REQUEST_']->query_vars_array[0];
		$task->get($code);
	}

	if (isset($_REQUEST['btn_submit']) || isset($_REQUEST['btn_add_event'])) {
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
		$old_release = $task->release();
		if (isset($_REQUEST['release_id']) && $_REQUEST['release_id'] > 0 && $old_release->id != $_REQUEST['release_id']) {
			$new_release = new \Engineering\Release($_REQUEST['release_id']);
			array_push($msgs,"Release changed from ".$old_release->title." to ".$new_release->title);
			$parameters['release_id'] = $_REQUEST['release_id'];
		}
		$old_tech = $task->assignedTo();
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
		$old_product = $task->product();
		if ($old_product->id != $_REQUEST['product_id']) {
			$new_product = new \Engineering\Product($_REQUEST['product_id']);
			array_push($msgs,"Product changed from ".$old_product->title." to ".$new_product->title);
			$parameters['product_id'] = $_REQUEST['product_id'];
		}
		if (isset($_REQUEST['date_due']) && $task->date_due != get_mysql_date($_REQUEST['date_due'])) {
			array_push($msgs,"Due Date changed from ".$task->date_due." to ".$_REQUEST['date_due']);
			$parameters['date_due'] = $_REQUEST['date_due'];
		}
		$old_project = $task->project();
		if ($old_project->id != $_REQUEST['project_id']) {
			$new_project = new \Engineering\Project($_REQUEST['project_id']);
			array_push($msgs,"Project changed from '".$old_project->title."' to '".$new_project->title."'");
			$parameters['project_id'] = $_REQUEST['project_id'];
		}
		app_log("Submitted task form",'debug',__FILE__,__LINE__);
		if ($task->id) {
			if ($task->update($parameters)) {
				$page->success = "Updates applied";
				app_log("Task updated, status now ".$parameters['status'],'debug',__FILE__,__LINE__);
			} else {
				$page->addError("Error saving updates: ".$task->error());
			}
			if (count($msgs) > 0) {
				$event = new \Engineering\Event();
				$event->add(array(
					'task_id'	=> $task->id,
					'person_id'	=> $GLOBALS['_SESSION_']->customer->id,
					'date_added'	=> date('Y-m-d H:i:s'),
					'description'	=> join('<br>',$msgs)
				));
				if ($event->error()) $page->addError("Error creating event: ".$event->error());
			}
		}
		else {
			if ($task->add($parameters)) {
				$page->success = "Task Created";
				$event = new \Engineering\Event();
				$event->add(array(
					'task_id'	=> $task->id,
					'person_id'	=> $GLOBALS['_SESSION_']->customer->id,
					'date_added'	=> date('Y-m-d H:i:s'),
					'description'	=> 'Task created'
				));
				app_log("Task created",'debug',__FILE__,__LINE__);
			} else {
				$page->addError("Error creating task: ".$task->error());
			}
		}
		
		// Email if Assignment changed
		if ($tech_status) {
			$tech = $task->assignedTo();
			$product = $task->product();
			$project = $task->project();
			$requestedBy = $task->requestedBy();
			$message = new \Email\Message(
				array(
					'from'	=> 'service@spectrosinstruments.com',
					'subject'	=> "[ENGINEERING] Task #".$task->id." assigned to you",
					'body'		=> "The following task was $tech_status to you:
Task Title: ".$task->title."<br>
Product: ".$product->title."<br>
Project: ".$project->title."<br>
Priority: ".$task->priority."<br>
Type: ".$task->type."<br>
Due: ".$task->date_due."<br>
Requested By: ".$requestedBy->full_name()."<br>
Link: http://".$GLOBALS['_config']->site->hostname."/_engineering/task/".$task->code."<br>
Description: ".$task->description
				)
			);
			$message->html(true);
			$tech->notify($message);
		}

		if (isset($_REQUEST['notes']) && strlen($_REQUEST['notes'])) {
			if (strtoupper($_REQUEST['new_status']) != $task->status) {
				$old_status = $task->status;
				$task->update(array('status'=>$_REQUEST['new_status']));
				if ($task->error) {
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
				'description'	=> $_REQUEST['notes']
			));
			if ($event->error()) $page->addError($event->error());
		} else {
		    // if they tried to do an event update without description, then show error
		    if (isset($_REQUEST['btn_add_event'])) $page->addError("'Event Update' could not be added, 'Event Description' is required.");
		}
	}

	$peopleList = new \Register\CustomerList();
	$people = $peopleList->find(array("status" => array('NEW','ACTIVE')));
	if ($peoplelist->error) $page->addError($peoplelist->error);

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
	} else {
		$task->date_added = 'now';
	}
