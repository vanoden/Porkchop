<?
	class ContactInit
	{
		public $error;

		public function __construct()
		{
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();

			if (! in_array("contact__info",$schema_list))
			{
				# Create company__info table
				$create_table_query = "
					CREATE TABLE contact__info (
						label	varchar(100) not null primary key,
						value	varchar(255)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating info table in ContactInit::construct: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
			}

			# Check Current Schema Version
			$get_version_query = "
				SELECT	value
				FROM	contact__info
				WHERE	label = 'schema_version'
			";

			$rs = $GLOBALS['_database']->Execute($get_version_query);
			if (! $rs)
			{
				$this->error = "SQL Error in ContactInit::construct ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			list($current_schema_version) = $rs->FetchRow();

			if ($current_schema_version < 1)
			{
				app_log("Upgrading schema to version 1",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `contact_events` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `date_event` datetime NOT NULL,
					  `content` TEXT NOT NULL,
					  `status` enum('NEW','OPEN','CLOSED') DEFAULT 'NEW',
					  PRIMARY KEY (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating contact types table in ContactInit::construct ".$GLOBALS['_database']->ErrorMsg();
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$current_schema_version = 1;

				$update_schema_query = "
					INSERT
					INTO	contact__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
							value = 1
				";
				$GLOBALS['_database']->Execute($update_schema_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error updating schema_version table in ContactInit::construct ".$GLOBALS['_database']->ErrorMsg();
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			if ($current_schema_version < 2)
			{
				app_log("Upgrading schema to version 2",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$add_roles_query = "
					INSERT
					INTO	register_roles
					VALUES	(null,'contact admin','Can view contact request, notified of requests')
				";
				$GLOBALS['_database']->Execute($add_roles_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error adding register roles in ContactInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$current_schema_version = 2;

				$update_schema_query = "
					INSERT
					INTO	contact__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
							value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error updating schema_version table in ContactInit::construct ".$GLOBALS['_database']->ErrorMsg();
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
		}
	}
	class ContactEvent
	{
		public $error;

		public function __construct()
		{
			app_log("Initializing Contact Module",'debug',__FILE__,__LINE__);

			$_init = new ContactInit();
			if ($_init->error)
			{
				$this->error = "Error initializing Contact module: ".$_init->error;
				return null;
			}
		}
		public function add($parameters)
		{
			$add_object_query = "
				INSERT
				INTO	contact_events
				(		id,date_event,content,status)
				VALUES
				(		null,sysdate(),?,'NEW')
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(json_encode($parameters))
			);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "Error storing contact information: ".$GLOBALS['_database']->ErrorMsg();
				return NULL;
			}
		}
		public function update($id,$parameters)
		{
			$update_object_query = "
				UPDATE	contact_events
				SET		id = id";
			if (in_array($parameters["status"],array("NEW","OPEN","CLOSED")))
			{
				$update_object_query .= ",
						status = '".$parameters["status"]."'";
			}
			$update_object_query .= "
				WHERE	id = ?
			";

			$GLOBALS['_database']->Execute(
				$update_object_query,
				array($id)
			);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "Error updating contact information: ".$GLOBALS['_database']->ErrorMsg();
				return NULL;
			}
			return $this->details($id);
		}

		public function find($parameters = array())
		{
			$find_object_query = "
				SELECT	id
				FROM	contact_events
				WHERE	id = id
			";
			if (preg_match('/^\w+$/',$parameters['status']))
				$find_object_query = "
				AND		status = '".$parameters['status']."'";
			$find_object_query .= "
				ORDER BY date_event";
			$rs = $GLOBALS['_database']->Execute($find_object_query);
			if (! $rs)
			{
				$this->error = "SQL Error in ContactEvent::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$objects = array();
			while (list($id) = $rs->FetchRow())
			{
				$object = $this->details($id);
				array_push($objects,$object);
			}
			return $objects;
		}
		public function details($id)
		{
			$get_object_query = "
				SELECT	id,
						date_event,
						status,
						content
				FROM	contact_events
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($id)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in ContactEvent::details: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$object = $rs->FetchNextObject(false);
			$content = json_decode($object->content);
			$object->content = $content;
			return $object;
		}
	}
?>
