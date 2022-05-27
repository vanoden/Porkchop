<?php
    ###############################################
    ### Handle API Request for the Engineering	###
    ### Module									###
    ### A. Caravello 8/22/2018               	###
    ###############################################
    namespace Engineering;

	// Base Class for APIs
	class API extends \API {

		public function __construct() {
			$this->_name = 'engineering';
			$this->_version = '0.2.3';
			$this->_release = '2021-12-18';
			$this->_schema = new \Engineering\Schema();
			parent::__construct();
		}

		###################################################
		### Add a Product								###
		###################################################
		public function addProduct() {
			$this->requireRole("engineering user");

			$product = new \Engineering\Product();
			if ($product->error()) $this->error("Error adding product: ".$product->error());
			$product->add(
				array(
					'title'			=> $_REQUEST['title'],
					'description'	=> $_REQUEST['description'],
				)
			);
			if ($product->error()) $this->error("Error adding product: ".$product->error());
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->product = $product;

			api_log($response);
			print $this->formatOutput($response);
		}

		###################################################
		### Update a Product							###
		###################################################
		public function updateProduct() {
			$this->requireRole("engineering user");

			$response = new \HTTP\Response();
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
			print $this->formatOutput($response);
		}

		###################################################
		### Get Product									###
		###################################################
		public function getProduct() {
			$this->requireRole("engineering user");

			$response = new \HTTP\Response();
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
			print $this->formatOutput($response);
		}

		###################################################
		### Find Products								###
		###################################################
		public function findProducts() {
			$this->requireRole("engineering user");

			$response = new \HTTP\Response();
			$productList = new \Engineering\ProductList();
			$products = $productList->find();
			
			if ($productList->error()) $this->error("Error finding products: ".$productList->error());

			$response->success = 1;
			$response->product = $products;

			api_log($response);
			print $this->formatOutput($response);
		}

		###################################################
		### Add a Release								###
		###################################################
		public function addRelease() {
			$this->requireRole("engineering user");

			$release = new \Engineering\Release();
			if ($release->error()) $this->error("Error adding Release: ".$release->error());
			$release->add(
				array(
					'title'			=> $_REQUEST['title'],
					'description'	=> $_REQUEST['description'],
				)
			);
			if ($release->error()) $this->error("Error adding Release: ".$release->error());
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->release = $release;

			api_log($response);
			print $this->formatOutput($response);
		}

		###################################################
		### Update a Release							###
		###################################################
		public function updateRelease() {
			$this->requireRole("engineering user");

			$response = new \HTTP\Response();
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
						$response->release = $release;
					}
					else {
						$response->success = 0;
						$response->error = $release->error();
					}
				}
				else {
					$response->success = 0;
					$response->error = $release->error();
				}
			}
			else {
				$response->success = 0;
				$response->error = "Release code required";
			}

			api_log($response);
			print $this->formatOutput($response);
		}

		###################################################
		### Get Release									###
		###################################################
		public function getRelease() {
			$this->requireRole("engineering user");

			$response = new \HTTP\Response();
			$release = new \Engineering\Release();
			if ($release->get($_REQUEST['code'])) {
				$response->success = 1;
				$response->release = $release;
			}
			elseif($release->error()) {
				$response->success = 0;
				$response->error = $release->error();
			}
			else {
				$response->success = 1;
			}

			api_log($response);
			print $this->formatOutput($response);
		}
		###################################################
		### Find Releases								###
		###################################################
		public function findReleases() {
			$this->requireRole("engineering user");

			$response = new \HTTP\Response();
			$releaseList = new \Engineering\ReleaseList();
			$releases = $releaseList->find();
			
			if ($releaseList->error()) $this->error("Error finding Releases: ".$releaseList->error());

			$response->success = 1;
			$response->release = $releases;

			api_log($response);
			print $this->formatOutput($response);
		}

		###################################################
		### Add a Project								###
		###################################################
		public function addProject() {
			$this->requireRole("engineering user");

			if (isset($_REQUEST['owner_code'])) {
				$owner = new \Register\Admin();
				if (! $owner->get($_REQUEST['owner_code'])) {
					app_error("Owner not found");
				}
			}
			elseif (isset($_REQUEST['manager_id'])) {
				$owner = new \Register\Admin($_REQUEST['manager_id']);
			}
			$project = new \Engineering\Project();
			if ($project->error()) $this->error("Error adding project: ".$project->error());
			$project->add(
				array(
					'manager_id'	=> $owner->id,
					'title'			=> $_REQUEST['title'],
					'description'	=> $_REQUEST['description'],
				)
			);
			if ($project->error()) $this->error("Error adding project: ".$project->error());
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->project = $project;

			api_log($response);
			print $this->formatOutput($response);
		}

		###################################################
		### Update a Project							###
		###################################################
		public function updateProject() {
			$this->requireRole("engineering user");

			$response = new \HTTP\Response();
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
			print $this->formatOutput($response);
		}

		###################################################
		### Get Project									###
		###################################################
		public function getProject() {
			$this->requireRole("engineering user");

			$response = new \HTTP\Response();
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
			print $this->formatOutput($response);
		}

		###################################################
		### Find Projects								###
		###################################################
		public function findProjects() {
			$this->requireRole("engineering user");

			$response = new \HTTP\Response();
			$projectList = new \Engineering\ProjectList();
			$projects = $projectList->find();
			
			if ($projectList->error()) $this->error("Error finding projects: ".$projectList->error());

			$response->success = 1;
			$response->project = $projects;

			api_log($response);
			print $this->formatOutput($response);
		}

		###################################################
		### Add a Task									###
		###################################################
		public function addTask() {
			$this->requireRole("engineering user");

			$response = new \HTTP\Response();
			$product = new \Engineering\Product();
			$product->get($_REQUEST['product_code']);
			if ($product->error()) $this->error("Error finding product: ".$product->error());
			if (! $product->id) $this->error("No product found matching '".$_REQUEST['product_code']."'");

			if (isset($_REQUEST['requested_by']) && $_REQUEST['requested_by'] && $_REQUEST['requested_by'] != $GLOBALS['_SESSION_']->customer->code) {
				if ($GLOBALS['_SESSION_']->customer->can('manage engineering tasks')) {
					$requester = new \Register\Customer();
					$requester->get($_REQUEST['requested_by']);
					if ($requester->error) $this->error("Error finding requester: ".$requester->error);
					if (! $requester->id) $this->error("No user found matching '".$_REQUEST['requested_by']."'");
				}
				else {
					$this->error("Permission denied");
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
			if ($task->error()) $this->error("Error adding task: ".$task->error());
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
			if ($task->error()) $this->error("Error adding task: ".$task->error());
			$response->success = 1;
			$response->task = $task;

			api_log($response);
			print $this->formatOutput($response);
		}

		###################################################
		### Get a Task by Code							###
		###################################################
		public function getTask() {
			$this->requireRole("engineering user");

			$response = new \HTTP\Response();
			$task = new \Engineering\Task();
			if ($task->error()) $this->error("Error initializing task: ".$task->error());
			if ($task->get($_REQUEST['code'])) {
				$response->success = 1;
				$response->task = $task;
			}
			else {
				$response->success = 0;
				$response->error = "Task not found";
			}

			api_log($response);
			print $this->formatOutput($response);
		}

		###################################################
		### Find Tasks									###
		###################################################
		public function findTasks() {
			$this->requireRole("engineering user");

			$parameters = array();
			if (isset($_REQUEST['assigned_to']) && !empty($_REQUEST['assigned_to'])) {
				$assigned = new \Register\Customer();
				if ($assigned->get($_REQUEST['assigned_to'])) {
					$parameters['assigned_id'] = $assigned->id;
				}
				else {
					$this->error("Assigned user not found");
				}
			}
			if (isset($_REQUEST['project_code']) && !empty($_REQUEST['project_code'])) {
				$project = new \Engineering\Project();
				if ($project->get($_REQUEST['project_code'])) {
					$parameters['project_id'] = $project->id;
				}
				else {
					$this->error("Project not found");
				}
			}
			if (isset($_REQUEST['release_code']) && !empty($_REQUEST['release_code'])) {
				$release = new \Engineering\Release();
				if ($release->get($_REQUEST['release_code'])) {
					$parameters['release_code'] = $release->id;
				}
				else {
					$this->error("Release not found");
				}
			}
			if (isset($_REQUEST['status']) && !empty($_REQUEST['status'])) {
				$parameters['status'] = $_REQUEST['status'];
			}
			$taskList = new \Engineering\TaskList();
			$tasks = $taskList->find($parameters);
			
			if ($taskList->error()) $this->error("Error finding tasks: ".$taskList->error());

			$response = new \HTTP\Response();
			$response->success = 1;
			$response->task = $tasks;

			api_log($response);
			print $this->formatOutput($response);
		}

		###################################################
		### Add an Event								###
		###################################################
		public function addEvent() {
			$response = new \HTTP\Response();
			$task = new \Engineering\Task();
			$task->get($_REQUEST['task_code']);
			if ($task->error()) $this->error("Error finding task: ".$task->error());
			if (! $task->id) $this->error("No task found matching '".$_REQUEST['task_code']."'");

			if (isset($_REQUEST['person_code']) && $_REQUEST['person_code'] && $_REQUEST['person_code'] != $GLOBALS['_SESSION_']->customer->code) {
				if ($GLOBALS['_SESSION_']->customer->can('manage engineering events')) {
					$reporter = new \Register\Customer();
					$reporter->get($_REQUEST['person_code']);
					if ($reporter->error) $this->error("Error finding reporter: ".$reporter->error);
					if (! $reporter->id) $this->error("No user found matching '".$_REQUEST['person_code']."'");
				}
				else {
					$this->error("Permission denied");
				}
			}
			else {
				$reporter = new \Register\Customer($GLOBALS['_SESSION_']->customer->id);
			}

			$event = new \Engineering\Event();
			if ($event->error()) $this->error("Error adding event: ".$event->error());
			$event->add(
				array(
					'task_id'			=> $task->id,
					'person_id'			=> $reporter->id,
					'date_event'		=> get_mysql_date($_REQUEST['date_event']),
					'description'		=> $_REQUEST['description']
				)
			);
			if ($event->error()) $this->error("Error adding event: ".$event->error());
			$response->success = 1;
			$response->event = $event;

			api_log($response);
			print $this->formatOutput($response);
		}

		###################################################
		### Update Event								###
		###################################################
		public function updateEvent() {
			$this->requirePrivilege("manage engineering events");

			$response = new \HTTP\Response();
			$response->success = 0;
			$response->error = "Call not ready yet";
			print $this->formatOutput($response);
		}

		###################################################
		### Find Events									###
		###################################################
		public function findEvents() {
			$this->has_role("engineering user");

			$response = new \HTTP\Response();
			$parameters = array();

			if (isset($_REQUEST['task_code'])) {
				$task = new \Engineering\Task();
				if ($task->get($_REQUEST['task_code'])) {
					$parameters['task_id'] = $task->id;
				}
				else {
					$this->error("Task not found");
				}
			}
			$eventList = new \Engineering\EventList();
			$events = $eventList->find($parameters);

			$response->success = 1;
			$response->event = $events;
			print $this->formatOutput($response);
		}

		public function _methods() {
			return array(
				'ping'			=> array(),
				'addTask'	=> array(
					'code'		=> array(),
					'title'		=> array(),
					'description'	=> array(),
					'date_added'	=> array(),
					'status'		=> array(),
					'type'			=> array(),
					'requested_by'	=> array(),
					'priority'		=> array(),
					'product_code'	=> array()
				),
				'getTask'	=> array(
					'code'		=> array('required' => true)
				),
				'findTasks'	=> array(
					'assigned_to'	=> array(),
					'project_code'	=> array(),
					'release_code'	=> array(),
					'status'		=> array()
				),
				'addEvent'	=> array(
					'task_code'		=> array(),
					'person_code'	=> array(),
					'date_event'	=> array(),
					'description'	=> array(),
				),
				'updateEvent'	=> array(
					'code'		=> array('required' => true),
					'title'		=> array(),
					'description'		=> array()
				),
				'findEvents'	=> array(
					'title'	=> array()
				),
				'addRelease'	=> array(
					'code'		=> array(),
					'title'		=> array(),
					'description'	=> array(),
				),
				'getRelease'	=> array(
					'code'	=> array('required' => true)
				),
				'updateRelease'	=> array(
					'code'		=> array('required' => true),
					'title'	=> array(),
					'description'		=> array()
				),
				'findReleases'	=> array(
					'title'	=> array()
				),
				'addProduct'	=> array(
					'code'		=> array(),
					'title'		=> array(),
					'description'	=> array(),
				),
				'getProduct'	=> array(
					'code'	=> array('required' => true)
				),
				'updateProduct'	=> array(
					'code'		=> array('required' => true),
					'title'	=> array(),
					'description'		=> array()
				),
				'findProducts'	=> array(
					'title'	=> array()
				),
				'addProject'	=> array(
					'code'		=> array(),
					'owner_code'	=> array('required' => true),
					'title'		=> array(),
					'description'	=> array()
				),
				'getProject'	=> array(
					'code'	=> array('required' => true)
				),
				'updateProject'	=> array(
					'code'		=> array('required' => true),
					'owner_code'	=> array(),
					'title'		=> array(),
					'description'	=> array()
				),
				'findProjects'	=> array(
					'title'	=> array(),
					'owner_code' => array('required' => true)
				)
			);
		}
	}
