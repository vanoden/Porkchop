<?
	require_module('action');
	require_module('register');
	require_module('monitor');

	$statii = array("NEW","ASSIGNED","OPEN","CANCELLED","CLOSED");
	# Request code from command line or query string
	if (isset($_REQUEST["request_code"])) {
		$request_code = $_REQUEST["request_code"];
	}
	else {
		$request_code = $GLOBALS['_REQUEST_']->query_vars_array[0];
	}

	###################################################
	### Form Submitted								###
	###################################################
	$request = new ActionRequest();
	$request->get($request_code);
	if (isset($_REQUEST['submit']) && $_REQUEST['submit'] == 'Update Request') {
		app_log("Updating request ".$request->id,'notice',__FILE__,__LINE__);
		$request->update(
			array(
				"status"		=> $_REQUEST['status'],
				"user_assigned"	=> $_REQUEST['user_assigned']
			)
		);
		if ($request->error) {
			app_log("Error updating request: ".$request->error,'error',__FILE__,__LINE__);
			$GLOBALS['_page']->error = "Error Updating Request";
		}
		else {
			$GLOBALS['_page']->success = "Request Updated";
		}
	}
	else if (isset($_REQUEST['submit']) && $_REQUEST['submit'] == 'Add Task') {
		$request->add_task(
			array(
				"type_id"			=> $_REQUEST['type_id'],
				"date_request"		=> get_mysql_date($_REQUEST['date_request']),
				"user_assigned"		=> $_REQUEST['user_assigned'],
				"description"		=> $_REQUEST['description'],
				"status"			=> $_REQUEST['status'],
				"user_requested"	=> $_REQUEST['user_requested'],
				"asset_id"			=> $_REQUEST['asset_id'],
			)
		);
		if ($request->error){
			app_log("Error adding task: ".$request->error,'error',__FILE__,__LINE__);
			$GLOBALS['_page']->error = "Failed to add task";
		}
	}

	###################################################
	### Populate Page								###
	###################################################

	# Get Rest of Request Details
	$requester = new RegisterCustomer($request->user_requested);
	$_tech = new RegisterAdmin();
	$techs = $_tech->find(array("_sort" => "last_name"));
	if ($request->user_assigned) $tech = $_tech->details($request->user_assigned);
	else $tech = array();
	
	$_asset = new MonitorAsset();
	$assets = $_asset->find();
	$requestEvents = $request->events();
	
	$_types = new ActionTaskTypes();
	$types = $_types->find();
	
	$_tasks = new ActionTasks();
	$tasks = $_tasks->find(array("request_id" => $request->id));
?>
