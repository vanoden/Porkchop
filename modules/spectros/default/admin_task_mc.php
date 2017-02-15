<?
	require_module('action');
	require_module('register');
	require_module('monitor');

	$statii = array('NEW','CANCELLED','ASSIGNED','OPEN','PENDING CUSTOMER','PENDING VENDOR','COMPLETE');
	# Request code from command line or query string
	if ($_REQUEST["task"]) {
		$task_id = $_REQUEST["task"];
	}
	else {
		$task_id = $GLOBALS['_REQUEST_']->query_vars_array[0];
	}
	$task = new ActionTask($task_id);

	$request = new ActionRequest();
	if ($task->request_id) {
		$request->details($task->request_id);
	}
	else if ($_REQUEST["request"]) {
		$request->get($_REQUEST["request"]);
	}

	###################################################
	### Form Submitted								###
	###################################################
	if ($_REQUEST['submit'] == 'Update Task') {
		app_log("Updating task ".$task->id.", status now ".$_REQUEST['status'],'notice',__FILE__,__LINE__);
		$task->update(
			array(
				"status"		=> $_REQUEST['status'],
				"user_assigned"	=> $_REQUEST['user_assigned'],
				"asset_id"		=> $_REQUEST['asset_id']
			)
		);
		if ($task->error) {
			app_log("Error updating task: ".$task->error,'error',__FILE__,__LINE__);
			$GLOBALS['_page']->error = "Error Updating Task";
		}
		else {
			$GLOBALS['_page']->success = "Task Updated";
		}
	}
	if ($_REQUEST['submit'] == 'Add Event') {
		$user = new RegisterPerson($_REQUEST["user"]);
		$task->add_event(
			array(
				"timestamp"	=> date('Y-m-d H:i:s'),
				"user"	=> $user->first_name." ".$user->last_name,
				"description"	=> $_REQUEST["description"]
			)
		);
		if ($_REQUEST['status'] != $task->status) {
			app_log("Updating task ".$task->id.", status now ".$_REQUEST['status'],'notice',__FILE__,__LINE__);
			$task->update(
				array(
					"status"		=> $_REQUEST['status']
				)
			);
			if ($task->error) {
				app_log("Error updating task: ".$task->error,'error',__FILE__,__LINE__);
				$GLOBALS['_page']->error = "Error Updating Task";
			}
			else {
				$GLOBALS['_page']->success .= "<br>Task Updated";
			}
		}
	}

	###################################################
	### Populate Page								###
	###################################################
	# Get Rest of Request Details
	$requester = new RegisterCustomer($task->user_requested);
	$_tech = new RegisterAdmin();
	$techs = $_tech->find(array("_sort" => "last_name"));
	if ($request->user_assigned) $tech = $_tech->details($request->user_assigned);
	else $tech = array();
	
	$_asset = new MonitorAsset();
	$assets = $_asset->find();
	
	$taskEvents = $task->events();
?>