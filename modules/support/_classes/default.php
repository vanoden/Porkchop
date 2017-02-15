<?
	class SupportInit
	{
		public $error;
		public $errno;
		public function __construct()
		{
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();

			if (! in_array("support__info",$schema_list))
			{
				# Create __info table
				$create_table_query = "
					CREATE TABLE support__info (
						label	varchar(100) not null primary key,
						value	varchar(255)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating info table in SupportInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
			}

			# Check Current Schema Version
			$get_version_query = "
				SELECT	value
				FROM	support__info
				WHERE	label = 'schema_version'
			";

			$rs = $GLOBALS['_database']->Execute($get_version_query);
			if (! $rs)
			{
				$this->error = "SQL Error in SupportInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}

			list($current_schema_version) = $rs->FetchRow();

			if ($current_schema_version < 1)
			{
				app_log("Upgrading Support schema to version 1",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `support_requests` (
						`id` 			int(11) NOT NULL AUTO_INCREMENT,
						`code` 			varchar(45) NOT NULL,
						`date_request`	datetime,
						`status`		enum('NEW','CANCELLED','ASSIGNED','OPEN','PENDING CUSTOMER','PENDING VENDOR','COMPLETE','CLOSED') NOT NULL DEFAULT 'NEW',
						`customer_id`	int(11) NOT NULL,
						`organization_id` int(11),
						`tech_id`		int(11),
						PRIMARY KEY `pk_support_request_id` (`id`),
						UNIQUE KEY `uk_request_code` (`code`),
						KEY `IDX_CUSTOMER_ID` (`customer_id`),
						KEY `IDX_TECH_ID` (`tech_id`),
						FOREIGN KEY `FK_CUSTOMER_ID` (`customer_id`) REFERENCES `register_users` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating support_requests table in SupportInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `support_events` (
						`id`			int(11) NOT NULL AUTO_INCREMENT,
						`request_id`	int(11) NOT NULL,
						`tech_id`		int(11) NOT NULL,
						`date_event`	datetime,
						`comment`		text,
						PRIMARY KEY `PK_EVENT_ID` (`id`),
						KEY `IDX_REQUEST_ID` (`request_id`,`tech_id`),
						KEY `IDX_TECH_ID` (`tech_id`),
						FOREIGN KEY `FK_REQUEST_ID` (`request_id`) REFERENCES `support_requests` (`id`),
						FOREIGN KEY `FK_TECH_ID` (`tech_id`) REFERENCES `register_users` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating support_events table in SupportInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `support_attachments` (
						`id`			int(11) NOT NULL AUTO_INCREMENT,
						`code`			varchar(32)	NOT NULL,
						`request_id`	int(11) NOT NULL,
						`user_id`		int(11) NOT NULL,
						`file_name`		varchar(32) NOT NULL,
						`date_posted`	datetime,
						PRIMARY KEY `PK_ATTACHMENT_ID` (`id`),
						UNIQUE KEY `UK_CODE` (`code`),
						KEY `IDX_REQUEST_ID` (`request_id`),
						KEY `IDX_USER_ID` (`user_id`),
						FOREIGN KEY `FK_REQUEST_ID` (`request_id`) REFERENCES `support_requests` (`id`),
						FOREIGN KEY `FK_USER_ID` (`user_id`) REFERENCES `register_users` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating support_events table in SupportInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `support_rmas` (
						`id`			int(11) NOT NULL AUTO_INCREMENT,
						`request_id`	int(11) NOT NULL,
						`code`			varchar(32) NOT NULL,
						`issuer_id`		int(11) NOT NULL DEFAULT 0,
						`date_issued`	datetime,
						`reason`		text,
						PRIMARY KEY `PK_RMA_ID` (`id`),
						UNIQUE KEY `UK_RMA_CODE` (`code`),
						KEY `IDX_REQUEST_ID` (`request_id`),
						KEY `IDX_ISSUER_ID` (`issuer_id`),
						FOREIGN KEY `FK_REQUEST_ID` (`request_id`) REFERENCES `support_requests` (`id`),
						FOREIGN KEY `FK_ISSUER_ID` (`issuer_id`) REFERENCES `register_users` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating support_rmas table in SupportInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `support_rma_events` (
						`id`			int(11)	NOT NULL AUTO_INCREMENT,
						`rma_id`		int(11) NOT NULL,
						`type`			enum('CUSTOMER_SHIPPED','SUPPORT_RECEIVED','SUPPORT_SHIPPED','CUSTOMER_RECEIVED','OTHER') NOT NULL DEFAULT 'OTHER',
						`comment`		text,
						PRIMARY KEY `PK_ID` (`id`),
						KEY `IDX_RMA_ID` (`rma_id`),
						FOREIGN KEY `FK_RMA_ID` (`rma_id`) REFERENCES `support_rmas` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating support_rma_events table in SupportInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$add_roles_query = "
					INSERT
					INTO	register_roles
					VALUES	(null,'support manager','Can view/edit support events'),
							(null,'support reporter','Can view support events')
				";
				$GLOBALS['_database']->Execute($add_roles_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error adding support roles in SupportInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$current_schema_version = 1;
				$update_schema_version = "
					INSERT
					INTO	support__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error in SupportInit::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}
				$GLOBALS['_database']->CommitTrans();
			}
		}
	}

	class SupportRequest
	{
		public $error;
		public $customer_id;
		public $tech_id;
		public $status;
		public $date_created;

		public function __construct()
		{
			$_init = new SupportInit();
			if ($_init->error)
			{
				$this->error = "Error initializing Support module: ".$_init->error;
				return null;
			}
		}

		public function add($parameters = array())
		{
			if (! $parameters['code']) $parameters['code'] = uniqid();
			if (! $parameters['status']) $parameters['status'] = 'NEW';
			if (! role('support manager'))
			{
				$parameters['customer_id'] = $GLOBALS['_SESSION_']->customer->id;
				$parameters['organization_id'] = $GLOBALS['_SESSION_']->customer->organization->id;
			}

			$add_object_query = "
				INSERT
				INTO	support_requests
				(		code,
						customer_id,
						organization_id,
						tech_id,
						date_request,
						status
				)
				VALUES
				(		?,?,?,?,sysdate(),?)
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$parameters['code'],
					$parameters['customer_id'],
					$parameters['organization_id'],
					$parameters['tech_id'],
					$parameters['status']
				)
			);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in SupportRequest::add: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($this->id,$parameters);
		}
		public function update($id,$parameters = array())
		{
			$update_object_query = "
				UPDATE	support_requests
				SET		id = id";
			
			if ($parameters['status'])
				$update_object_query .= ",
				status	= ".$GLOBALS['_database']->qstr($parameters['status'],get_magic_quotes_gpc);
			if ($parameters['tech_id'] and role('support manager'))
				$update_object_query .= ",
				tech_id = ".$GLOBALS['_database']->qstr($parameters['tech_id'],get_magic_quotes_gpc);

			$update_object_query .= "
				WHERE	id = ?";
			$GLOBALS['_database']->Execute(
				$update_object_query,
				array($id)
			);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in SupportRequest::update: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $this->details($id);
		}
		public function find($parameters = array())
		{
			# Get Requests for Admin
			$find_requests_query = "
				SELECT	sr.id
				FROM	support_requests sr
				WHERE	id = id
			";
			if (role("support manager"))
			{
				# No Special Limits
			}
			# Get Requests for Organization Member
			elseif ($GLOBALS['_SESSION_']->customer->organization->id > 0)
			{
				$find_requests_query .= "
				AND		sr.organization_id = ".$GLOBALS['_SESSION_']->customer->organization->id;
			}
			# Get Requests for Individual
			elseif ($GLOBALS['_SESSION_']->customer->id)
				$find_requests_query .= "
				AND		sr.customer_id = ".$GLOBALS['_SESSION_']->customer->id;
			else
			{
				$this->error = "Authentication required";
				return null;
			}
			
			if (preg_match('/^[\w\s]+$/',$parameters['status']))
			{
				$find_requests_query .= "\tAND	status = ".$parameters['status']."\n";
			}

			$rs = $GLOBALS['_database']->Execute($find_requests_query);
			if (! $rs)
			{
				$this->error = "SQL Error in SupportRequest::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$requests = array();
			while ($request = $rs->FetchNextObject(false))
			{
				array_push($requests,$this->details($request->id));
			}
			return $requests;
		}

		private function details($id)
		{
			# Get Request Details
			$get_request_query = "
				SELECT	id,
						code,
						status,
						tech_id,
						customer_id,
						date_request
				FROM	support_requests
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_request_query,
				array($id)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in SupportRequest::details: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $rs->FetchNextObject(false);
		}

		public function statuses()
		{
			$statuses = array(
					"NEW",
					"CANCELLED",
					"ASSIGNED",
					"OPEN",
					"PENDING CUSTOMER",
					"PENDING VENDOR",
					"COMPLETE",
					"CLOSED"
			);

			return $statuses;
		}
	}

	class SupportEvent
	{
		public $error;
		public $id;

		public function __construct()
		{
			$_init = new SupportInit();
			if ($_init->error)
			{
				$this->error = "Error initializing Support module: ".$_init->error;
				return null;
			}
		}

		public function add($parameters = array())
		{
			if (! $parameters['status']) $parameters['status'] = 'NEW';
			if (! role('support admin'))
				$parameters['customer_id'] = $GLOBALS['_SESSION_']->customer->id;

			$add_object_query = "
				INSERT
				INTO	support_events
				(		request_id,
						tech_id,
						date_event,
						comment
				)
				VALUES
				(		?,?,sysdate(),?)
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$parameters['request_id'],
					$parameters['tech_id'],
					$parameters['comment']
				)
			);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in SupportEvent::add: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($this->id);
		}
		public function update($id,$parameters = array())
		{
			return $this->details($id);
		}
		public function find($parameters = array())
		{
			# Get Requests for Admin
			$find_requests_query = "
				SELECT	se.id,
				FROM	support_events se
				WHERE	se.id = se.id
			";
			if (role("support manager"))
			{
				# No Special Limits
			}
			# Get Requests for Organization Member
			elseif ($GLOBALS['_SESSION_']->customer->organization->id)
			{
				# Get Organization Members
				$_customer = new RegisterCustomer();
				$customers = $_customer->find(array("organization_id" => $GLOBALS['_SESSION_']->customer->organization->id));
				$array = array();
				
				$find_requests_query .= "
				AND		se.customer_id in (".join(",",$customers).")";
			}
			# Get Requests for Individual
			else
				$find_requests_query = "";
	
			if (preg_match('/^\d+$/',$parameters['id']))
			{
				$find_requests_query .= "\tAND	id = ".$parameters['id']."\n";
			}
			if (preg_match('/^[\w\s]+$/',$parameters['status']))
			{
				$find_requests_query .= "\tAND	status = ".$parameters['status']."\n";
			}
			if (role("support manager"))
			{
				if (preg_match('/^\d+$/',$parameters['organization_id']))
				{
					$find_requests_query .= "\tAND	organization_id = ".$parameters['organization_id']."\n";
				}
			}
			else
			{
				$find_requests .= '';
			}

			$rs = $GLOBALS['_database']->Execute($find_requests_query);
			if (! $rs)
			{
				$this->error = "SQL Error in SupportRequest::find: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			else
			{
				$requests = array();
			}
		}

		private function details($id)
		{
			# Get Request Details
			$get_request_query = "
				SELECT	id,
						code,
						status,
						tech_id,
						customer_id,
						date_request
				FROM	support_requests
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_request_query,
				array($id)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in SupportRequest::details: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $rs->FetchObject();
		}
	}
?>
