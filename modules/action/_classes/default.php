<?
	require_module("event");
	
	class ActionInit {
		public $error;
		public $errno;
		public $database;
		public $classPrefix;
		public $info_table;

		public function __construct() {
			$this->database = $GLOBALS['_database'];

			$this->classPrefix = "Action";
			$this->info_table = strtolower($this->classPrefix."__info");

			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();

			if (! in_array($this->info_table,$schema_list)) {
				# Create __info table
				$create_table_query = "
					CREATE TABLE ".$this->info_table." (
						label	varchar(100) not null primary key,
						value	varchar(255)
					)
				";
				$this->database->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->errno = "1";
					$this->error = "SQL Error creating info table in ".$this->name."::__construct: ".$this->database->ErrorMsg();
					return null;
				}
			}

			# Check Current Schema Version
			$get_version_query = "
				SELECT	value
				FROM	".$this->info_table."
				WHERE	label = 'schema_version'
			";

			$rs = $this->database->Execute($get_version_query);
			if (! $rs) {
				$this->errno = "2";
				$this->error = "SQL Error in ".$this->name."::__construct: ".$this->database->ErrorMsg();
				return null;
			}

			list($current_schema_version) = $rs->FetchRow();

			if ($current_schema_version < 1) {
				error_log("Upgrading ".$this->classPrefix." to version 1");

				# Start Transaction
				if (! $this->database->BeginTrans())
					error_log("Transactions not supported");

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `action_requests` (
						`id` 			int(11) NOT NULL AUTO_INCREMENT,
						`code` 			varchar(45) NOT NULL,
						`date_request`	datetime,
						`status`		enum('NEW','CANCELLED','ASSIGNED','OPEN','PENDING CUSTOMER','PENDING VENDOR','COMPLETE','CLOSED') NOT NULL DEFAULT 'NEW',
						`user_requested`	int(11) NOT NULL,
						`user_assigned`		int(11),
						`description`	text,
						PRIMARY KEY `PK_ACTION_REQUEST_ID` (`id`),
						UNIQUE KEY `UK_ACTION_REQUEST_CODE` (`code`),
						KEY `IDX_ACTION_USER_ASSIGNED` (`user_assigned`),
						FOREIGN KEY `FK_USER_REQUESTED` (`user_requested`) REFERENCES `register_users` (`id`)
					)
				";
				$this->database->Execute($create_table_query);
				if ($this->database->ErrorMsg()) {
					$this->errno = "3";
					$this->error = "SQL Error creating action_requests table in ".$this->name."::__construct: ".$this->database->ErrorMsg();
					$this->database->RollbackTrans();
					return null;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `action_task_types` (
						`id`			int(3) NOT NULL AUTO_INCREMENT,
						`code`			varchar(100) NOT NULL,
						`parameters`	text NOT NULL,
						PRIMARY KEY `PK_TASK_TYPE_ID` (`id`),
						UNIQUE KEY `IDX_TASK_TYPE_CODE` (`code`)
					)
				";
				$this->database->Execute($create_table_query);
				if ($this->database->ErrorMsg()) {
					$this->errno = "4";
					$this->error = "SQL Error creating action_task_types table in ".$this->name."::__construct: ".$this->database->ErrorMsg();
					$this->database->RollbackTrans();
					return null;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `action_tasks` (
						`id`			int(11) NOT NULL AUTO_INCREMENT,
						`type_id`		int(3)	NOT NULL,
						`request_id`	int(11) NOT NULL,
						`date_request`	datetime NOT NULL,
						`user_requested`	int(11) NOT NULL,
						`user_assigned`	int(11) NOT NULL,
						`asset_id`		int(11) NOT NULL,
						`status`		enum('NEW','CANCELLED','ASSIGNED','OPEN','PENDING CUSTOMER','PENDING VENDOR','COMPLETE') NOT NULL DEFAULT 'NEW',
						`description`	text,
						PRIMARY KEY `PK_TASK_ID` (`id`),
						KEY `IDX_TASK_USER_ASSIGNED` (`user_assigned`),
						FOREIGN KEY `FK_TASK_TYPE` (`type_id`) REFERENCES `action_task_types` (`id`),
						FOREIGN KEY `FK_TASK_REQUEST_ID` (`request_id`) REFERENCES `action_requests` (`id`),
						FOREIGN KEY `FK_TASK_REQUEST_USER` (`user_requested`) REFERENCES `register_users` (`id`)
					)
				";
				$this->database->Execute($create_table_query);
				if ($this->database->ErrorMsg()) {
					$this->errno = "5";
					$this->error = "SQL Error creating action_tasks table in ".$this->name."::__construct: ".$this->database->ErrorMsg();
					$this->database->RollbackTrans();
					return null;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `action_task_sets` (
						`id`			int(11) NOT NULL AUTO_INCREMENT,
						`code`			varchar(100) NOT NULL,
						PRIMARY KEY `PK_TASK_SET_ID` (`id`),
						UNIQUE KEY `UK_TASK_SET_CODE` (`code`)
					)
				";
				$this->database->Execute($create_table_query);
				if ($this->database->ErrorMsg()) {
					$this->errno = "6";
					$this->error = "SQL Error creating action_task_sets table in ".$this->name."::__construct: ".$this->database->ErrorMsg();
					$this->database->RollbackTrans();
					return null;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `action_task_set_items` (
						`id`			int(11)	NOT NULL AUTO_INCREMENT,
						`set_id`		int(11) NOT NULL,
						`sort_position`	int(2) NOT NULL DEFAULT '50',
						`type_id`		int(3) NOT NULL,
						PRIMARY KEY `PK_TASK_SET_ITEM_ID` (`id`),
						KEY `IDX_SORTED_ITEMS` (`set_id`,`sort_position`),
						FOREIGN KEY `FK_TASK_TYPE_ID` (`type_id`) REFERENCES `action_task_types` (`id`),
						FOREIGN KEY `FK_TASK_SET_ID` (`set_id`) REFERENCES `action_task_sets` (`id`)
					)
				";
				$this->database->Execute($create_table_query);
				if ($this->database->ErrorMsg()) {
					$this->errno = "7";
					$this->error = "SQL Error creating action_task_set_items table in ".$this->name."::__construct: ".$this->database->ErrorMsg();
					$this->database->RollbackTrans();
					return null;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `action_events` (
						`id`			int(11)	NOT NULL AUTO_INCREMENT,
						`request_id`	int(11) NOT NULL,
						`date_event`	datetime NOT NULL,
						`user_id`		int(3) NOT NULL,
						`task_id`		int(11),
						PRIMARY KEY `PK_TASK_EVENT_ID` (`id`),
						KEY `IDX_TASK_EVENT_DATE` (`date_event`),
						KEY `IDX_TASK_EVENT_TASK` (`task_id`),
						FOREIGN KEY `FK_TASK_EVENT_REQUEST_ID` (`request_id`) REFERENCES `action_requests` (`id`),
						FOREIGN KEY `FK_TASK_EVENT_USER_ID` (`user_id`) REFERENCES `register_users` (`id`)
					)
				";
				$this->database->Execute($create_table_query);
				if ($this->database->ErrorMsg()) {
					$this->errno = "7";
					$this->error = "SQL Error creating action_events table in ".$this->name."::__construct: ".$this->database->ErrorMsg();
					$this->database->RollbackTrans();
					return null;
				}

				$add_roles_query = "
					INSERT
					INTO	register_roles
					VALUES	(null,'action manager','Can enter/edit/view action items'),
							(null,'action user','Can enter/view action items')
				";
				$this->database->Execute($add_roles_query);
				if ($this->database->ErrorMsg()) {
					$this->errno = "8";
					$this->error = "SQL Error adding action roles in ".$this->name."::__construct: ".$this->database->ErrorMsg();
					$this->database->RollbackTrans();
					return null;
				}

				$current_schema_version = 1;
				$update_schema_version = "
					INSERT
					INTO	".$this->info_table."
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$this->database->Execute($update_schema_version);
				if ($this->database->ErrorMsg()) {
					$this->error = "SQL Error in ".$this->name."::schema_manager: ".$this->database->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$this->database->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
		}
	}

	class ActionRequest Extends ActionInit {
		public $id;
		public $code;
		public $status;
		public $date_request;
		public $user_requested;
		public $user_assigned;
		public $description;

		public function __construct($id = 0){
			parent::__construct();
			if ($this->error) return null;
			
			if ($id > 0) $this->details($id);
		}

		# Add Request to database
		public function add($parameters = array()) {
			if (! isset($parameters['code'])) $parameters['code'] = uniqid();
			if (! isset($parameters['status'])) $parameters['status'] = 'NEW';

			$add_object_query = "
				INSERT INTO action_requests
				(
					`code`,`date_request`,`status`,`user_requested`
				)
				VALUES	(?,sysdate(),?,?)
			";
			$this->database->Execute(
				$add_object_query,
				array(
					$parameters['code'],
					$parameters['status'],
					$parameters['user_requested']
				)
			);
			if ($this->database->ErrorMsg()) {
				$this->errno = "9";
				$this->error = "SQL Error in ActionRequest::add: ".$this->database->ErrorMsg();
				return null;
			}
			$this->id = $this->database->Insert_ID();

			$event = new ActionEvent();
			$event->add(
				"ActionRequest",
				[	"code"	=> $parameters["code"],
					"timestamp"	=> date("Y-m-d H:i:s"),
					"user"	=> $GLOBALS['_SESSION_']->customer->code,
					"description"	=> "ActionRequest Created",
				]
			);
			return $this->update($parameters);
		}

		# Overload Update Function
		public function update() {
			$num_args = func_num_args();
			if ($num_args > 1) return $this->updateWithId(func_get_args());
			$id = $this->id;
			return $this->updateWithId($id,func_get_arg(0));
		}

		# Update request in database
		private function updateWithId($id,$parameters) {
			$changes  = array();
			$update_object_query = "
				UPDATE	action_requests
				SET		id = id";
			if (isset($parameters['description']) && $parameters['description'] != $this->description) {
				$update_object_query .= ",
				description = ".$this->database->qstr($parameters['description'],get_magic_quotes_gpc());
				array_push($changes,"Description changed.");
			}
			if (isset($parameters['status']) && $parameters['status'] != $this->status) {
				$update_object_query .= ",
				status = ".$this->database->qstr($parameters['status'],get_magic_quotes_gpc());
				array_push($changes,"Status changed to ".$parameters['status']);
			}
			if (isset($parameters['user_assigned']) && preg_match('/^\d+$/',$parameters['user_assigned']) && $parameters['user_assigned'] != $this->user_assigned) {
				$update_object_query .= ",
				user_assigned = ".$this->database->qstr($parameters['user_assigned'],get_magic_quotes_gpc());
				$tech = new RegisterCustomer($parameters['user_assigned']);
				array_push($changes,"Event assigned to ".$tech->code);
			}
			
			if (count($changes) < 1)
				return $this->details($id);

			$update_object_query .= "
				WHERE	id = ?
			";

			$this->database->Execute(
				$update_object_query,
				array($id)
			);
			if ($this->database->ErrorMsg()) {
				$this->error = "SQL Error in ActionRequest::update: ".$this->database->ErrorMsg();
				return null;
			}

			$this->add_event(
				array(
					"timestamp"	=> date("Y-m-d H:i:s"),
					"user"	=> $GLOBALS['_SESSION_']->customer->code,
					"description"	=> "ActionRequest Updated: ".join("\n",$changes),
				)
			);
			return $this->details($id);
		}

		public function get($code = '') {
			$get_object_query = "
				SELECT	id
				FROM	action_requests
				WHERE	code = ?
			";
			$rs = $this->database->Execute($get_object_query,array($code));
			if (! $rs) {
				$this->error = "SQL Error in ".$this->name."::get: ".$this->database->ErrorMsg();
				return null;
			}
			list($id) = $rs->FetchRow();
			return $this->details($id);
		}


		public function details($id = 0) {
			$get_object_query = "
				SELECT	*
				FROM	action_requests
				WHERE	id = ?
			";
			$details = $GLOBALS['_database']->Execute($get_object_query,array($id));
			if (! $details)
			{
				$this->error = "SQL Error in ".$this->name."::details: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$object = $details->FetchNextObject(false);
			$this->id = $object->id;
			$this->code = $object->code;
			$this->status = $object->status;
			$this->date_request = $object->date_request;
			$this->user_requested = $object->user_requested;
			$this->user_assigned = $object->user_assigned;
			$this->description = $object->description;
			return $object;
		}
		
		public function add_task($parameters) {
			$task = new ActionTask();
			$task->add(
				array(
					"request_id"		=> $this->id,
					"date_request"		=> $parameters['date_request'],
					"user_requested"	=> $parameters['user_requested'],
					"user_assigned"		=> $parameters['user_assigned'],
					"description"		=> $parameters['description'],
					"status"			=> $parameters['status'],
					"type_id"			=> $parameters['type_id'],
					"name"				=> $parameters['name'],
					"asset_id"			=> $parameters['asset_id']
				)
			);
			$event = new ActionEvent();
			$event->add(
				"ActionRequest",
				[	"code"	=> $this->code,
					"timestamp"	=> date("Y-m-d H:i:s"),
					"user"	=> $GLOBALS['_SESSION_']->customer->code,
					"task"	=> $task->id,
					"description"	=> "ActionTask ".$task->id." added to Request",
				]
			);

			if ($task->error) $this->error = $task->error;
		}
		public function tasks(){
			$_tasks = new ActionTask();
			$tasks = $_tasks->find(
				array(
					'request_id'	=> $this->id
				)
			);
			if ($_tasks->error) $this->error = $_tasks->error;
			return $tasks;
		}
		public function events() {
			$_event = new ActionEvent();
			$response = $_event->search("ActionRequest",array("code" => $this->code));
			$events = array();
			foreach ($response["hits"]["hits"] as $hit) {
				$record = $hit["_source"];
				array_push($events,$record);
			}

			return $events;
		}
		public function add_event($parameters) {
			$parameters["code"] = $this->code;
			$event = new ActionEvent();
			$event->add(
				"ActionRequest",
				$parameters
			);
		}
		public function user_assigned_name(){
			if (! $this->user_assigned) return "Unassigned";
			$admin = new RegisterCustomer($this->user_assigned);
			return $admin->first_name." ".$admin->last_name;
		}
		public function user_requested_name(){
			$admin = new RegisterCustomer($this->user_requested);
			return $admin->first_name." ".$admin->last_name;
		}
	}

	class ActionRequests Extends ActionInit {
		public function __construct() {
			parent::__construct();
			if ($this->error) return null;
		}

        public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	action_requests
				WHERE	id = id
			";
			foreach ($parameters as $parameter) {
				if (in_array($parameter,array('id','code','user_assigned','user_requested'))) {
					$find_objects_query .= "
					AND	`".$parameter."` = ".$this->database->qstr($parameters[$parameter],get_magic_quotes_gpc);
				}
			}
			if (is_array($parameters["status"]))
				$find_objects_query .= "AND `status` IN (\"".join("\",\"",preg_replace('/[^\w\-\_\s\.]/','',$parameters["status"]))."\")";
			elseif (isset($parameters["status"]))
				$find_objects_query .= "
				AND	`status` = ".$this->database->qstr($parameters["status"],get_magic_quotes_gpc);
	
			$rs = $this->database->Execute($find_objects_query);
			if (! $rs) {
				$this->error = "SQL Error in ActionRequest::find: ".$this->database->ErrorMsg();
				return null;
			}
			$array = array();
			while(list($id) = $rs->FetchRow()) {
				$request = new ActionRequest($id);
				$object = $request;
				array_push($array,$object);
			}
			return $array;
		}
		
    }
    
	class ActionTask Extends ActionInit {
		public $id;
		public $error;
		public $request_id;
		public $type_id;
		public $status;
		public $date_request;
		public $user_requested;
		public $user_assigned;
		public $description;
		public $asset_id;

		public function __construct($id = 0) {
			parent::__construct();
			if ($this->error) return null;
			
			if ($id > 0) $this->details($id);
		}

		public function add($parameters = array()) {
			$add_object_query = "
				INSERT INTO action_tasks
				(		request_id,
						date_request,
						user_requested,
						type_id,
						status
				)
				VALUES
				(		?,?,?,?,?)
			";
			$this->database->Execute(
				$add_object_query,
				array(
					$parameters['request_id'],
					$parameters['date_request'],
					$parameters['user_requested'],
					$parameters['type_id'],
					$parameters['status']
				)
			);
			if ($this->database->ErrorMsg()) {
				$this->error = "SQL Error in ActionTask::add: ".$this->database->ErrorMsg();
				return null;
			}
			$this->id = $this->database->Insert_ID();
			$object = $this->update($parameters);
			return $object;
		}
		public function update() {
			$num_args = func_num_args();
			if ($num_args > 1) return $this->updateWithId(func_get_args());
			$id = $this->id;

			return $this->updateWithId($id,func_get_arg(0));
		}
		public function updateWithId($id,$parameters = array()) {
			$old_task = new ActionTask();
			$old_task->details($id);
			
			$update_object_query = "
				UPDATE	action_tasks
				SET		id = id
			";
			if (isset($parameters['status'])) {
				$update_object_query .= ",
				status = ".$GLOBALS['_database']->qstr($parameters['status'],get_magic_quotes_gpc());
				if ($old_task->status != $parameters['status'])
					$changed .= "Status now ".$parameters['status']." ";
			}
			if (isset($parameters['description'])) {
				$update_object_query .= ",
				description = ".$GLOBALS['_database']->qstr($parameters['description'],get_magic_quotes_gpc());
				$changed .= "Description now ".$parameters['description']." ";
			}
			if (isset($parameters['user_assigned']) && preg_match('/^\d+$/',$parameters['user_assigned'])) {
				$update_object_query .= ",
				user_assigned = ".$GLOBALS['_database']->qstr($parameters['user_assigned'],get_magic_quotes_gpc());
				if ($old_task->user_assigned != $parameters['user_assigned']) {
					$user = new RegisterCustomer();
					$user->details($parameters['user_assigned']);
					$changed .= "Now assigned to ".$user->first_name." ".$user->last_name." ";
				}
			}
			if (isset($parameters['asset_id']) && preg_match('/^\d+$/',$parameters['asset_id'])) {
				$update_object_query .= ",
				asset_id = ".$GLOBALS['_database']->qstr($parameters['asset_id'],get_magic_quotes_gpc());
				if ($old_task->asset_id != $parameters['asset_id']) {
					$asset = new MonitorAsset();
					$asset->details($parameters['asset_id']);
					$changed .= "Asset now ".$asset->code." ";
				}
			}
			$update_object_query .= "
				WHERE	id = ?
			";

			$this->database->Execute(
				$update_object_query,
				array($id)
			);
			if ($this->database->ErrorMsg())
			{
				$this->error = "SQL Error in ActionTask::update: ".$this->database->ErrorMsg();
				return null;
			}
			$object = $this->details($id);
			
			# Get Relevant Search Details
			$asset = new MonitorAsset();
			$asset->details($_REQUEST['asset_id']);
			$organization = new RegisterOrganization();
			$organization->details($asset->organization_id);
			error_log("Adding event for asset ".$asset->code."[".$_REQUEST['asset_id']."] owned by organization ".$organization->code."[".$asset->organization_id."]","debug",__FILE__,__LINE__);
			$this->add_event(
				array(
					"timestamp"	=> date("Y-m-d H:i:s"),
					"user"	=> $GLOBALS['_SESSION_']->customer->code,
					"description"	=> "ActionTask Updated: ".$changed,
					"asset"		=> $asset->code,
					"organization"	=> $organization->code
				)
			);

			return $object;
		}

		public function get($code = '') {
			$get_object_query = "
				SELECT	id
				FROM	action_tasks
				WHERE	code = ?
			";
			return $this->details($id);
		}

		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	action_tasks
				WHERE	id = id
			";
			foreach ($parameters as $parameter) {
				if (in_array($parameter,array('id','request_id','user_assigned','user_requested','status'))) {
					$find_objects_query .= "
					AND	`".$parameter."` = ".$this->database->qstr($parameters[$parameter],get_magic_quotes_gpc);
				}
			}
			$rs = $this->database->Execute($find_objects_query);
			if (! $rs) {
				$this->error = "SQL Error in ".$this->name."::find: ".$this->database->ErrorMsg();
				return null;
			}
			$array = array();
			while(list($id) = $rs->FetchRow()) {
				$this->details($id);
				array_push($array,$this);
			}
			return $array;
		}
		public function user_assigned_name() {
			if (! $this->user_assigned) return "Unassigned";
			$admin = new RegisterCustomer($this->user_assigned);
			return $admin->first_name." ".$admin->last_name;
		}
		public function user_requested_name() {
			$admin = new RegisterCustomer($this->user_requested);
			return $admin->first_name." ".$admin->last_name;
		}
		public function type() {
			$type = new ActionTaskType();
			$type->details($this->type_id);
			return $type->code;
		}
		public function request_code() {
			if (! $this->request_id) {
				app_log("No request_id",'error',__FILE__,__LINE__);
				return "Unknown";
			}
			$request = new ActionRequest($this->request_id);
			if (! $request->code) {
				app_log("No request code",'error',__FILE__,__LINE__);
				return "Don't know";
			}
			return $request->code;
		}
		public function details($id = 0) {
			$get_object_query = "
				SELECT	*
				FROM	action_tasks
				WHERE	id = ?
			";
			$details = $GLOBALS['_database']->Execute($get_object_query,array($id));
			if (! $details)
			{
				$this->error = "SQL Error in ".$this->name."::details: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$properties = $details->FetchNextObject(false);
			$this->id = $properties->id;
			$this->type_id = $properties->type_id;
			$this->date_request = $properties->date_request;
			$this->request_id = $properties->request_id;
			$this->status = $properties->status;
			$this->user_requested = $properties->user_requested;
			$this->user_assigned = $properties->user_assigned;
			$this->description = $properties->description;
			$this->asset_id = $properties->asset_id;
			return $properties;
		}
		public function events() {
			$_event = new ActionEvent();
			$response = $_event->search(
				"ActionTask",
				array(
					"request_code"	=> $this->request_code(),
					"task_id"		=> $this->id,
				)
			);
			$events = array();
			foreach ($response["hits"]["hits"] as $hit) {
				$record = $hit["_source"];
				array_push($events,$record);
			}

			return $events;
		}
		public function add_event($parameters) {
			$parameters["request_code"] = $this->request_code();
			$parameters["task_id"] = $this->id;
			$parameters["timestamp"] = date("Y-m-d H:i:s");
			if (isset($this->asset_id)) {
				$asset = new MonitorAsset($this->asset_id);
				$parameters["asset_code"] = $asset->code;
			}
			$event = new ActionEvent();
			$event->add(
				"ActionTask",
				$parameters
			);
		}
	}
	class ActionTasks Extends ActionInit {
		public function __construct() {
			parent::__construct();
			if ($this->error) return null;
		}
		public function find($parameters = array())	{
			$find_objects_query = "
				SELECT	id
				FROM	action_tasks
				WHERE	id = id
			";
			while (list($parameter,$value) = each ($parameters)) {
				if (in_array($parameter,array('id','request_id','user_assigned','user_requested','status','asset_id'))) {
					$find_objects_query .= "
					AND	`".$parameter."` = ".$this->database->qstr($value,get_magic_quotes_gpc);
				}
				else {
					app_log("Filter parameter $parameter skipped",'debug',__FILE__,__LINE__);
				}
			}
			app_log("ActionTasks Query: ".format_query($find_objects_query),'debug',__FILE__,__LINE__);
			$rs = $this->database->Execute($find_objects_query);
			if (! $rs) {
				$this->error = "SQL Error in ".$this->name."::find: ".$this->database->ErrorMsg();
				return null;
			}
			$array = array();
			while(list($id) = $rs->FetchRow()) {
				$task = new ActionTask();
				$task->details($id);
				array_push($array,$task);
			}
			return $array;
		}
    }
    
	class ActionTaskSet Extends ActionInit {
		public function __construct()
		{
			parent::__construct();
			if ($this->error) return null;
		}

		public function add($parameters = array())
		{
			$add_object_query = "
				INSERT INTO action_task_sets
				VALUES	()
			";

			return $this->update($id,$parameters);
		}

		public function update($id,$parameters = array())
		{
			$update_object_query = "
				UPDATE	Action
				SET		
				WHERE	id = ?
			";
			return $this->details($id);
		}

		public function get($code = '')
		{
			$get_object_query = "
				SELECT	id
				FROM	Action
				WHERE	code = ?
			";
			return $this->details($id);
		}

		public function find($parameters = array())
		{
			$find_objects_query = "
				SELECT	id
				FROM	action_task_sets
				WHERE	id = id
			";
			foreach ($parameters as $parameter)
			{
				if (in_array($parameter,array('id','code')))
				{
					$find_objects_query .= "
					AND	`".$parameter."` = ".$this->database->qstr($parameters[$parameter],get_magic_quotes_gpc);
				}
			}
			$rs = $this->database->Execute($find_object_query);
			if (! $rs)
			{
				$this->error = "SQL Error in ".$this->name."::find: ".$this->database->ErrorMsg();
				return null;
			}
			$array = array();
			while(list($id) = $rs->FetchRow())
			{
				$object = $this->details($id);
				array_push($array,$object);
			}
			return $array;
		}

		public function details($id = 0)
		{
			$get_object_query = "
				SELECT	*
				FROM	Action
				WHERE	id = ?
			";
			$details = $GLOBALS['_database']->Execute($get_object_query,array($id));
			if (! $details)
			{
				$this->error = "SQL Error in ".$this->name."::details: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $details->FetchNextObject(false);
		}
	}

	class ActionEvent Extends ActionInit {
		public $id;
		public $request_id;
		public $date_event;
		public $user_id;
		public $task_id;
		public $description;
		
		public function add($type,$parameters)
		{
			app_log("Loggin, type $type",'debug',__FILE__,__LINE__);
			$event_item = new EventItem();
			$event_item->add($type,$parameters);
			return $true;
		}

		public function search($type,$params) {
			$event_item = new EventItem();
			$results = $event_item->search($type,$params);
			return $results;
		}
	}
	class ActionTaskType {
        public $id;
		public $code;
		public $error;
		
		public function get($code)
		{
			$get_object_query = "
				SELECT	id
				FROM	action_task_types
				WHERE	code = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($code)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in ActionTaskType::get: ".$rs->ErrorMsg();
				return null;
			}
			list($id) = $rs->FetchRow();
			return $this->details($id);
		}
		public function add($parameters) {
			$add_object_query = "
				INSERT
				INTO	action_task_types
				VALUES	(null,?,'')
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array($parameters['code'])
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in ActionTaskType::add: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->details();
		}
		public function details($id=0)
		{
			if (! preg_match('/^\d+$/',$id))
				$id = $this->id;

			$get_object_query = "
				SELECT	*
				FROM	action_task_types
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($id)
			);
			if (! $rs){
				$this->error = "SQL Error in ActionTaskType::details: ".$rs->ErrorMsg();
			}
			$object = $rs->FetchNextObject(false);
			$this->id = $object->id;
			$this->code = $object->code;
			return $object;
		}
    }
    
	class ActionTaskTypes {
        public $error;
		
		public function __construct(){
		}
		
		public function find() {
			$get_objects_query = "
				SELECT	id
				FROM	action_task_types
				WHERE	id = id
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_objects_query
			);
			if (! $rs)
			{
				$this->error = "SQL Error in ActionTaskTypes::find: ".$rs->ErrorMsg();
				return null;
			}
			$objects = array();
			while (list($id) = $rs->FetchRow())
			{
				$type = new ActionTaskType();
				array_push($objects,$type->details($id));
			}
			return $objects;
		}
    }
    
?>
