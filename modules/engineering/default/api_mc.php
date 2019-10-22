<?php
    ###############################################
    ### Handle API Request for the Engineering	###
    ### Module									###
    ### A. Caravello 8/22/2018               	###
    ###############################################
	$_package = array(
		"name"		=> "engineering",
		"version"	=> "0.2.2",
		"release"	=> "2019-11-18"
	);

	app_log("Request: ".print_r($_REQUEST,true),'debug',__FILE__,__LINE__);

	###############################################
	### Load API Objects						###
    ###############################################
	# Call Requested Event
	if (isset($_REQUEST["method"])) {
		$message = "Method ".$_REQUEST['method']." called by user ".$GLOBALS['_SESSION_']->customer->code;
		app_log($message,'debug',__FILE__,__LINE__);

		# Call the Specified Method
		$function_name = $_REQUEST["method"];
		$function_name();
		exit;
	}
	# Only Developers Can See The API
	elseif (! $GLOBALS['_SESSION_']->customer->has_role('engineering manager')) {
		header("location: /_engineering/home");
		exit;
	}

	###################################################
	### Just See if Server Is Communicating			###
	###################################################
	function ping() {
		$response = new \HTTP\Response();
		$response->header->session = $GLOBALS['_SESSION_']->code;
		$response->header->method = $_REQUEST["method"];
		$response->header->date = system_time();
		$response->message = "PING RESPONSE";
		$response->success = 1;

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Add a Product								###
	###################################################
	function addProduct() {
		if (! $GLOBALS[_SESSION_]->customer->has_role("engineering user")) {
			error("Permission denied");
		}
		$product = new \Engineering\Product();
		if ($product->error()) error("Error adding product: ".$product->error());
		$product->add(
			array(
				'title'			=> $_REQUEST['title'],
				'description'	=> $_REQUEST['description'],
			)
		);
		if ($product->error()) error("Error adding product: ".$product->error());
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->product = $product;

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Update a Product							###
	###################################################
	function updateProduct() {
		$response = new \HTTP\Response();
		if (! $GLOBALS[_SESSION_]->customer->has_role("engineering user")) {
			error("Permission denied");
		}
		if (isset($_REQUEST['code'])) {
			$product = new \Engineering\Product();
			if ($product->get($_REQUEST['code'])) {
				$parameters = array();
				if ($_REQUEST['description']) {
					$parameters['description'] = $_REQUEST['description'];
				}
				if ($_REQUEST['title']) {
					$parameters['title'] = $_REQUEST['title'];
				}
				if ($product->update($parameters)) {
					$response->success = 1;
					$response->product = $product;
				}
				else {
					$response->success = 0;
					$response->error = $product->error();
				}
			}
			else {
				$response->success = 0;
				$response->error = $product->error();
			}
		}
		else {
			$response->success = 0;
			$response->error = "Product code required";
		}

		api_log($response);
		print formatOutput($response);
	}
	###################################################
	### Get Product									###
	###################################################
	function getProduct() {
		$response = new \HTTP\Response();
		if (! $GLOBALS[_SESSION_]->customer->has_role("engineering user")) {
			error("Permission denied");
		}
		$product = new \Engineering\Product();
		if ($product->get($_REQUEST['code'])) {
			$response->success = 1;
			$response->product = $product;
		}
		elseif($product->error()) {
			$response->success = 0;
			$response->error = $product->error();
		}
		else {
			$response->success = 1;
		}

		api_log($response);
		print formatOutput($response);
	}
	###################################################
	### Find Products								###
	###################################################
	function findProducts() {
		$response = new \HTTP\Response();
		if (! $GLOBALS[_SESSION_]->customer->has_role("engineering user")) {
			error("Permission denied");
		}
		$productList = new \Engineering\ProductList();
		$products = $productList->find();
		
		if ($productList->error()) error("Error finding products: ".$productList->error());

		$response->success = 1;
		$response->product = $products;

		api_log($response);
		print formatOutput($response);
	}
	###################################################
	### Add a Release								###
	###################################################
	function addRelease() {
		if (! $GLOBALS[_SESSION_]->customer->has_role("engineering user")) {
			error("Permission denied");
		}
		$release = new \Engineering\Release();
		if ($release->error()) error("Error adding Release: ".$release->error());
		$release->add(
			array(
				'title'			=> $_REQUEST['title'],
				'description'	=> $_REQUEST['description'],
			)
		);
		if ($release->error()) error("Error adding Release: ".$release->error());
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->release = $release;

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Update a Release							###
	###################################################
	function updateRelease() {
		$response = new \HTTP\Response();
		if (! $GLOBALS[_SESSION_]->customer->has_role("engineering user")) {
			error("Permission denied");
		}
		if (isset($_REQUEST['code'])) {
			$release = new \Engineering\Release();
			if ($release->get($_REQUEST['code'])) {
				$parameters = array();
				if ($_REQUEST['description']) {
					$parameters['description'] = $_REQUEST['description'];
				}
				if ($_REQUEST['title']) {
					$parameters['title'] = $_REQUEST['title'];
				}
				if ($release->update($parameters)) {
					$response->success = 1;
					$response->Release = $Release;
				}
				else {
					$response->success = 0;
					$response->error = $Release->error();
				}
			}
			else {
				$response->success = 0;
				$response->error = $Release->error();
			}
		}
		else {
			$response->success = 0;
			$response->error = "Release code required";
		}

		api_log($response);
		print formatOutput($response);
	}
	###################################################
	### Get Release									###
	###################################################
	function getRelease() {
		$response = new \HTTP\Response();
		if (! $GLOBALS[_SESSION_]->customer->has_role("engineering user")) {
			error("Permission denied");
		}
		$release = new \Engineering\Release();
		if ($release->get($_REQUEST['code'])) {
			$response->success = 1;
			$response->release = $release;
		}
		elseif($Release->error()) {
			$response->success = 0;
			$response->error = $release->error();
		}
		else {
			$response->success = 1;
		}

		api_log($response);
		print formatOutput($response);
	}
	###################################################
	### Find Releases								###
	###################################################
	function findReleases() {
		$response = new \HTTP\Response();
		if (! $GLOBALS[_SESSION_]->customer->has_role("engineering user")) {
			error("Permission denied");
		}
		$releaseList = new \Engineering\ReleaseList();
		$releases = $releaseList->find();
		
		if ($releaseList->error()) error("Error finding Releases: ".$releaseList->error());

		$response->success = 1;
		$response->release = $releases;

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Add a Project								###
	###################################################
	function addProject() {
		if (! $GLOBALS[_SESSION_]->customer->has_role("engineering user")) {
			error("Permission denied");
		}
		$project = new \Engineering\Project();
		if ($project->error()) error("Error adding project: ".$project->error());
		$project->add(
			array(
				'title'			=> $_REQUEST['title'],
				'description'	=> $_REQUEST['description'],
			)
		);
		if ($project->error()) error("Error adding project: ".$project->error());
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->project = $project;

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Update a Project							###
	###################################################
	function updateProject() {
		$response = new \HTTP\Response();
		if (! $GLOBALS[_SESSION_]->customer->has_role("engineering user")) {
			error("Permission denied");
		}
		if (isset($_REQUEST['code'])) {
			$project = new \Engineering\Project();
			if ($project->get($_REQUEST['code'])) {
				$parameters = array();
				if ($_REQUEST['description']) {
					$parameters['description'] = $_REQUEST['description'];
				}
				if ($_REQUEST['title']) {
					$parameters['title'] = $_REQUEST['title'];
				}
				if ($project->update($parameters)) {
					$response->success = 1;
					$response->project = $project;
				}
				else {
					$response->success = 0;
					$response->error = $project->error();
				}
			}
			else {
				$response->success = 0;
				$response->error = $project->error();
			}
		}
		else {
			$response->success = 0;
			$response->error = "Project code required";
		}

		api_log($response);
		print formatOutput($response);
	}
	###################################################
	### Get Project									###
	###################################################
	function getProject() {
		$response = new \HTTP\Response();
		if (! $GLOBALS[_SESSION_]->customer->has_role("engineering user")) {
			error("Permission denied");
		}
		$project = new \Engineering\Project();
		if ($project->get($_REQUEST['code'])) {
			$response->success = 1;
			$response->project = $project;
		}
		elseif($project->error()) {
			$response->success = 0;
			$response->error = $project->error();
		}
		else {
			$response->success = 1;
		}

		api_log($response);
		print formatOutput($response);
	}
	###################################################
	### Find Projects								###
	###################################################
	function findProjects() {
		$response = new \HTTP\Response();
		if (! $GLOBALS[_SESSION_]->customer->has_role("engineering user")) {
			error("Permission denied");
		}
		$projectList = new \Engineering\ProjectList();
		$projects = $projectList->find();
		
		if ($projectList->error()) error("Error finding projects: ".$projectList->error());

		$response->success = 1;
		$response->project = $projects;

		api_log($response);
		print formatOutput($response);
	}
	###################################################
	### Add a Task									###
	###################################################
	function addTask() {
		$response = new \HTTP\Response();
		if (! $GLOBALS[_SESSION_]->customer->has_role("engineering user")) {
			error("Permission denied");
		}
		$product = new \Engineering\Product();
		$product->get($_REQUEST['product_code']);
		if ($product->error()) error("Error finding product: ".$product->error());
		if (! $product->id) error("No product found matching '".$_REQUEST['product_code']."'");

		if (isset($_REQUEST['requested_by']) && $_REQUEST['requested_by'] && $_REQUEST['requested_by'] != $GLOBALS['_SESSION_']->customer->code) {
			if ($GLOBALS['_SESSION_']->customer->has_role('engineering manager')) {
				$requester = new \Register\Customer();
				$requester->get($_REQUEST['requested_by']);
				if ($requester->error) error("Error finding requester: ".$requester->error);
				if (! $requester->id) error("No user found matching '".$_REQUEST['requested_by']."'");
			}
			else {
				error("Permission denied");
			}
		}
		else {
			$requester = new \Register\Customer($GLOBALS['_SESSION_']->customer->id);
		}
		if (isset($_REQUEST['status']) && in_array($_REQUEST['status'],array('NEW','HOLD','ACTIVE','CANCELLED','TESTING','COMPLETE'))) $status = $_REQUEST['status'];
		else $status = 'NEW';
		if (isset($_REQUEST['priority']) && in_array($_REQUEST['priority'],array('NORMAL','IMPORTANT','URGENT','CRITICAL'))) $priority = $_REQUEST['priority'];
		else $priority = 'NORMAL';
		if (isset($_REQUEST['type']) && in_array($_REQUEST['type'],array('BUG','FEATURE','TEST'))) $type = $_REQUEST['type'];
		else error("Valid type required");
		if (isset($_REQUEST['date_added']) && get_mysql_date($_REQUEST['date_added'])) $date_added = get_mysql_date($_REQUEST['date_added']);
		else $date_added = get_mysql_date('now');

		$task = new \Engineering\Task();
		if ($task->error()) error("Error adding task: ".$task->error());
		$parameters = array(
				'title'				=> $_REQUEST['title'],
				'date_added'		=> $date_added,
				'description'		=> $_REQUEST['description'],
				'status'			=> $_REQUEST['status'],
				'type'				=> $type,
				'requested_by'		=> $requester->id,
				'priority'			=> $priority,
				'product_id'		=> $product->id,
		);
		$task->add($parameters);
		if ($task->error()) error("Error adding task: ".$task->error());
		$response->success = 1;
		$response->task = $task;

		api_log($response);
		print formatOutput($response);
	}
	###################################################
	### Get a Task by Code							###
	###################################################
	function getTask() {
		$response = new \HTTP\Response();
		if (! $GLOBALS[_SESSION_]->customer->has_role("engineering user")) {
			error("Permission denied");
		}
		$task = new \Engineering\Task();
		if ($task->error()) error("Error initializing task: ".$task->error());
		if ($task->get($_REQUEST['code'])) {
			$response->success = 1;
			$response->task = $task;
		}
		else {
			$response->success = 0;
			$response->error = "Task not found";
		}

		api_log($response);
		print formatOutput($response);
	}
	###################################################
	### Find Tasks									###
	###################################################
	function findTasks() {
		if (! $GLOBALS[_SESSION_]->customer->has_role("engineering user")) {
			error("Permission denied");
		}
		$parameters = array();
		if (isset($_REQUEST['assigned_to']) && !empty($_REQUEST['assigned_to'])) {
			$assigned = new \Register\Customer();
			if ($assigned->get($_REQUEST['assigned_to'])) {
				$parameters['assigned_id'] = $assigned->id;
			}
			else {
				error("Assigned user not found");
			}
		}
		if (isset($_REQUEST['project_code']) && !empty($_REQUEST['project_code'])) {
			$project = new \Engineering\Project();
			if ($project->get($_REQUEST['project_code'])) {
				$parameters['project_id'] = $project->id;
			}
			else {
				error("Project not found");
			}
		}
		if (isset($_REQUEST['release_code']) && !empty($_REQUEST['release_code'])) {
			$release = new \Engineering\Release();
			if ($release->get($_REQUEST['release_code'])) {
				$parameters['release_code'] = $release->id;
			}
			else {
				error("Release not found");
			}
		}
		if (isset($_REQUEST['status']) && !empty($_REQUEST['status'])) {
			$parameters['status'] = $_REQUEST['status'];
		}
		$taskList = new \Engineering\TaskList();
		$tasks = $taskList->find($parameters);
		
		if ($taskList->error()) error("Error finding tasks: ".$taskList->error());

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->task = $tasks;

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Add an Event								###
	###################################################
	function addEvent() {
		$response = new \HTTP\Response();
		$task = new \Engineering\Task();
		$task->get($_REQUEST['task_code']);
		if ($task->error()) error("Error finding task: ".$task->error());
		if (! $task->id) error("No task found matching '".$_REQUEST['task_code']."'");

		if (isset($_REQUEST['person_code']) && $_REQUEST['person_code'] && $_REQUEST['person_code'] != $GLOBALS['_SESSION_']->customer->code) {
			if ($GLOBALS['_SESSION_']->customer->has_role('engineering admin')) {
				$reporter = new \Register\Customer();
				$reporter->get($_REQUEST['person_code']);
				if ($reporter->error) error("Error finding reporter: ".$reporter->error);
				if (! $reporter->id) error("No user found matching '".$_REQUEST['person_code']."'");
			}
			else {
				error("Permission denied");
			}
		}
		else {
			$reporter = new \Register\Customer($GLOBALS['_SESSION_']->customer->id);
		}

		$event = new \Engineering\Event();
		if ($event->error()) error("Error adding event: ".$event->error());
		$event->add(
			array(
				'task_id'			=> $task->id,
				'person_id'			=> $reporter->id,
				'date_event'		=> get_mysql_date($_REQUEST['date_event']),
				'description'		=> $_REQUEST['description']
			)
		);
		if ($event->error()) error("Error adding event: ".$event->error());
		$response->success = 1;
		$response->event = $event;

		api_log($response);
		print formatOutput($response);
	}
	###################################################
	### Update Event								###
	###################################################
	function updateEvent() {
		$response = new \HTTP\Response();
		if (! $GLOBALS[_SESSION_]->customer->has_role("engineering user")) {
			error("Permission denied");
		}
		$response->success = 0;
		$response->error = "Call not ready yet";
		print formatOutput($response);
	}
	###################################################
	### Find Events									###
	###################################################
	function findEvents() {
		$response = new \HTTP\Response();
		if (! $GLOBALS[_SESSION_]->customer->has_role("engineering user")) {
			error("Permission denied");
		}
		$parameters = array();

		if (isset($_REQUEST['task_code'])) {
			$task = new \Engineering\Task();
			if ($task->get($_REQUEST['task_code'])) {
				$parameters['task_id'] = $task->id;
			}
			else {
				error("Task not found");
			}
		}
		$eventList = new \Engineering\EventList();
		$events = $eventList->find($parameters);

		$response->success = 1;
		$response->event = $events;
		print formatOutput($response);
	}
	###################################################
	### Run Upgrades and Return DB Schema Version	###
	###################################################
	function schemaVersion() {
		$schema = new \Engineering\Schema();
		if ($schema->error) {
			app_error("Error getting version: ".$schema->error,__FILE__,__LINE__);
		}
		$version = $schema->version();
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->version = $version;
		print formatOutput($response);
	}
	function schemaUpgrade() {
		$schema = new \Engineering\Schema();
		if ($schema->error) {
			app_error("Error getting version: ".$schema->error,__FILE__,__LINE__);
		}
		$version = $schema->upgrade();
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->version = $version;
		print formatOutput($response);
	}
	###################################################
	### System Time									###
	###################################################
	function system_time() {
		return date("Y-m-d H:i:s");
	}
	###################################################
	### Application Error							###
	###################################################
	function app_error($message,$file = __FILE__,$line = __LINE__) {
		app_log($message,'error',$file,$line);
		error('Application Error');
	}
	###################################################
	### Return Properly Formatted Error Message		###
	###################################################
	function error($message) {
		$_REQUEST["stylesheet"] = '';
		$response = new \HTTP\Response();
		$response->error = $message;
		$response->success = 0;
		api_log($response);
		print formatOutput($response);
		exit;
	}
	###################################################
	### Return Properly Formatted Message			###
	###################################################
	function formatOutput($object) {
		if (isset($_REQUEST['_format']) && $_REQUEST['_format'] == 'json') {
			$format = 'json';
			header('Content-Type: application/json');
		}
		else {
			$format = 'xml';
			header('Content-Type: application/xml');
		}
		$document = new \Document($format);
		$document->prepare($object);
		return $document->content();
	}
