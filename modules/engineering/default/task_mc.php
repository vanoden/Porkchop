<?php
	if (! $GLOBALS['_SESSION_']->customer->has_role('engineering user')) {
		$page->error = "Permission Denied";
		return;
	}
	$page = new \Site\Page();
	$task = new \Engineering\Task();
	
	if ($_REQUEST['task_id']) {
		$task = new \Engineering\Task($_REQUEST['task_id']);
	}
	elseif (isset($_REQUEST['code'])) {
		$task->get($_REQUEST['code']);
		if ($task->error) $page->error = $task->error;
	}
	elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$code = $GLOBALS['_REQUEST_']->query_vars_array[0];
		$task->get($code);
	}

	if (isset($_REQUEST['btn_submit']) || isset($_REQUEST['btn_add_event'])) {
		$parameters = array();
		if (isset($_REQUEST['title'])) $parameters['title'] = $_REQUEST['title'];
		else {
			$page->error = "Title required";
		}
		if (isset($_REQUEST['type'])) $parameters['type'] = $_REQUEST['type'];
		if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
		if (isset($_REQUEST['estimate'])) $parameters['estimate'] = $_REQUEST['estimate'];
		if (isset($_REQUEST['priority'])) $parameters['priority'] = $_REQUEST['priority'];
		if (isset($_REQUEST['description'])) $parameters['description'] = $_REQUEST['description'];
		if (isset($_REQUEST['requested_id'])) $parameters['requested_id'] = $_REQUEST['requested_id'];
		if (isset($_REQUEST['release_id']) && $_REQUEST['release_id'] > 0) $parameters['release_id'] = $_REQUEST['release_id'];
		if (isset($_REQUEST['assigned_id']) && $_REQUEST['assigned_id']) $parameters['assigned_id'] = $_REQUEST['assigned_id'];
		if (isset($_REQUEST['product_id'])) $parameters['product_id'] = $_REQUEST['product_id'];
		if (isset($_REQUEST['code'])) $parameters['code'] = $_REQUEST['code'];
		if (isset($_REQUEST['date_added'])) $parameters['date_added'] = $_REQUEST['date_added'];
		if (isset($_REQUEST['date_due'])) $parameters['date_due'] = $_REQUEST['date_due'];
		if (isset($_REQUEST['project_id'])) $parameters['project_id'] = $_REQUEST['project_id'];

		app_log("Submitted task form",'debug',__FILE__,__LINE__);
		if ($task->id) {
			if ($task->update($parameters)) {
				$page->success = "Updates applied";
				app_log("Task updated",'debug',__FILE__,__LINE__);
			}
			else {
				$page->error = "Error saving updates: ".$task->error();
			}
		}
		else {
			if ($task->add($parameters)) {
				$page->success = "Task Created";
				app_log("Task created",'debug',__FILE__,__LINE__);
			}
			else {
				$page->error = "Error creating task: ".$task->error();
			}
		}

		if (isset($_REQUEST['notes']) && strlen($_REQUEST['notes'])) {
			$event = new \Engineering\Event();
			$event->add(array(
				'task_id'		=> $task->id,
				'person_id'		=> $_REQUEST['event_person_id'],
				'date_added'	=> $_REQUEST['date_event'],
				'description'	=> $_REQUEST['notes']
			));
			if ($event->error()) {
				$page->error = $event->error();
			}
			else {
				if ($_REQUEST['new_status'] != $task->status) {
					$task->update(array('status'=>$_REQUEST['new_status']));
					if ($task->error) {
						$page->error = $task->error;
					}
					else {
						$page->success = "Updated applied successfully";
					}
				}
				else {
					$page->success = "Updated applied successfully";
				}
			}
		}
	}

	$peopleList = new \Register\CustomerList();
	$people = $peopleList->find(array("status" => array('NEW','ACTIVE')));
	if ($peoplelist->error) $page->error = $peoplelist->error;

	$role = new \Register\Role();
	$role->get("engineering user");
	$techs = $role->members();

	$productlist = new \Engineering\ProductList();
	$products = $productlist->find();
	if ($productlist->error()) $page->error = $productlist->error();

	$releaselist = new \Engineering\ReleaseList();
	$releases = $releaselist->find();
	if ($releaselist->error()) $page->error = $releaselist->error();

	$projectlist = new \Engineering\ProjectList();
	$projects = $projectlist->find();
	if ($projectlist->error()) $page->error = $projectlist->error();
	
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

		$eventlist = new \Engineering\EventList();
		$events = $eventlist->find(array('task_id'=> $task->id));
		if ($eventlist->error()) $page->error = $eventlist->error();
	}
	elseif ($page->error) {
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
	}
	else {
		$task->date_added = date('m/d/Y');
	}
?>
