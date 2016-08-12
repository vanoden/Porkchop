<?
	class MediaFile
	{
		public function find($parameters = array())
		{
			# Get Code From Table
			$get_code_query = "
				SELECT	id
				FROM	media_files
				WHERE	id = id
			";
			if (preg_match('/^\d+$/',$parameters['item_id']))
			{
				$get_code_query .= "
				AND		item_id = ".$parameters['item_id'];
			}
			if (array_key_exists('index',$parameters) and preg_match('/^\d+$/',$parameters['index']))
			{
				$get_code_query .= "
				AND		`index` = ".$GLOBALS['_database']->qstr($parameters['index'],get_magic_quotes_gpc);
			}
			$rs = $GLOBALS['_database']->Execute(
				$get_code_query
			);
			if (! $rs)
			{
				$this->error = "SQL Error in MediaFile::load: ".$GLOBALS['_database']->ErrorMsg();
			}
			$objects = array();
			while (list($id) = $rs->FetchRow())
			{
				$object = $this->details($id);
				array_push($objects,$object);
			}
			return $objects;
		}
		public function get($code)
		{
			# Get Code From Table
			$get_code_query = "
				SELECT	id
				FROM	media_files
				WHERE	code = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_code_query,
				array($code)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in MediaFile::get: ".$GLOBALS['_database']->ErrorMsg();
			}
			list($id) = $rs->FetchRow();
			return $this->details($id);
		}
		public function details($id)
		{
			# Get Code From Table
			$get_code_query = "
				SELECT	id,
						code,
						size,
						timestamp,
						mime_type,
						original_file,
						date_uploaded,
						disposition,
						unix_timestamp(date_uploaded) `timestamp`
				FROM	media_files
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_code_query,
				array($id)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in MediaFile::details: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $rs->FetchNextObject(false);
		}
		public function save($id,$index,$tmp_file,$original_file = '',$mime_type = 'text/plain',$size = 0)
		{
			$code = uniqid();
			if (! $index) $index = '';

			$add_object_query = "
				INSERT
				INTO	media_files
				(		`item_id`,
						`index`,
						`mime_type`,
						`code`,
						`size`,
						`original_file`,
						`owner_id`,
						`date_uploaded`
				)
				VALUES
				(		?,?,?,?,?,?,?,sysdate())
				ON DUPLICATE KEY UPDATE
					mime_type = ?,
					size = ?,
					original_file = ?
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$id,
					$index,
					$mime_type,
					$code,
					$size,
					$original_file,
					$GLOBALS['_SESSION_']->customer->id,
					$mime_type,
					$size,
					$original_file
				)
			);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in MediaFile::save[$id]: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$id = $GLOBALS['_database']->Insert_ID();
			$details = $this->details($id);

			# Save File
			$storage_path = RESOURCES."/_media/".$details->code;
			app_log("Storing '$tmp_file' as '$storage_path'",'debug',__FILE__,__LINE__);
			if (move_uploaded_file($tmp_file,$storage_path))
				return 1;
			else
			{
				$this->error = "Failed to add file to repository";
				return 0;
			}
		}
		public function load($code)
		{
			# Get File Info
			$object = $this->get($code);

			# Save File
			$path = RESOURCES."/_media/".$object->code;
			if (! file_exists($path))
			{
				$this->error = "File not found";
				return null;
			}
			$content = file_get_contents($path);
			$object->content = $content;
			return $object;
		}
	}
	class MediaItem
	{
		public $id;
		public $error;

		public function __construct()
		{
			# Database Initialization
			$init = new MediaInit();
			if ($init->error)
			{
				$this->error = $init->error;
			}
		}
		public function add($parameters = array())
		{
			# Some Things Required
			if (! $parameters['type'])
			{
				$this->error = "type required for new MediaItem";
				return null;
			}
			# Generate 'unique' code if none provided
			if (! $parameters["code"])
			{
				$parameters["code"] = uniqid($parameters["type"].'-');
			}
			$add_object_query = "
				INSERT
				INTO	media_items
				(		id,
						type,
						date_created,
						owner_id,
						code
				)
				VALUES
				(		null,?,sysdate(),?,?)
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$parameters["type"],
					$GLOBALS['_SESSION_']->customer->id,
					$parameters["code"]
				)
			);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in MediaItem::add: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($this->id,$parameters);
		}
		public function update($id,$parameters = array())
		{
			foreach($parameters as $label => $value)
			{
				app_log("Setting meta '$label' = '$value'",'debug',__FILE__,__LINE__);
				$this->setMeta($id,$label,$value);
			}
			$update_object_query = "
				UPDATE	media_items
				SET		date_updated = sysdate()
				WHERE	id = ?
			";
			$GLOBALS['_database']->Execute(
				$update_object_query,
				array($id)
			);
			return $this->details($id);
		}
		public function find($parameters = array())
		{
			$find_object_query = "
				SELECT	distinct(m.item_id)
				FROM	media_metadata m,
						media_items i
				WHERE	m.item_id = i.id
				AND		i.deleted = 0
			";
			foreach ($parameters as $label => $value)
			{
				if (! preg_match('/^[\w\-\.\_]+$/',$label))
				{
					$this->error = "Invalid parameter name in MediaItem::find: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
				if ($label == "type")
					$find_object_query .= "
					AND	i.type = ".$GLOBALS['_database']->qstr($value,get_magic_quotes_gpc);
				else
					$find_object_query .= "
					AND (	m.label = '".$label."'
						AND m.value = ".$GLOBALS['_database']->qstr($value,get_magic_quotes_gpc)."
					)";
			}
			app_log("Query: $find_object_query",'debug',__FILE__,__LINE__);
			$rs = $GLOBALS['_database']->Execute($find_object_query);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in MediaItem::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$objects = array();
			while (list($id) = $rs->FetchRow())
			{
				$object = $this->details($id);
				$privileges = $this->privileges($id);
				if ($privileges['read'])
				{
					app_log("Adding ".$object->id." to array",'debug',__FILE__,__LINE__);
					array_push($objects,$object);
				}
				else
				{
					app_log("Hiding ".$object->id." lacking privileges",'debug',__FILE__,__LINE__);
				}
			}
			return $objects;
		}
		public function get($code)
		{
			$get_object_query = "
				SELECT	id
				FROM	media_items
				WHERE	code = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($code)
			);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in MediaItem::get: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($id) = $rs->FetchRow();
			return $this->details($id);
		}
		public function details($id)
		{
			$get_object_query = "
				SELECT	id,
						type,
						date_created,
						date_updated,
						owner_id,
						code
				FROM	media_items
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($id)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in MediaItem::details: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$array = $rs->FetchRow();
			if (! $array['id']) return (object) $array;
			$metadata = $this->getMeta($id);
			$array = array_merge($array,$metadata);

			$_file = new MediaFile();
			$images = $_file->find(array("item_id" => $id));
			$array['files']= $images;
			return (object) $array;
		}
		public function getMeta($id)
		{
			# Get Metadata
			$get_metadata_query = "
				SELECT	label,
						value
				FROM	media_metadata
				WHERE	item_id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_metadata_query,
				array($id)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in MediaItem::getMeta: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$array = array();
			while (list($label,$value) = $rs->FetchRow())
			{
				$array[$label] = $value;
			}
			return $array;
		}
		public function setMeta($id,$parameter,$value)
		{
			$add_metadata_query = "
				INSERT
				INTO	media_metadata
				(		item_id,
						label,
						value
				)
				VALUES
				(		?,?,?)
				ON DUPLICATE KEY UPDATE
						value = ?
			";
			$GLOBALS['_database']->Execute(
				$add_metadata_query,
				array(
					$id,
					$parameter,
					$value,
					$value
				)
			);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in MediaItem::setMeta: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return array($parameter,$value);
		}
		public function privileges($media_id,$customer_id = null, $organization_id = null)
		{
			if (! role('media manager'))
			{
				$customer_id = $GLOBALS['_SESSION_']->customer->id;
				$organization_id = $GLOBALS['_SESSION_']->customer->organization->id;
			}
			if (! preg_match('/^\d+$/',$customer_id)) $customer_id = $GLOBALS['_SESSION_']->customer->id;
			if (! preg_match('/^\d+$/',$organization_id)) $organization_id = $GLOBALS['_SESSION_']->customer->organization->id;
			if (! preg_match('/^\d+$/',$customer_id)) $customer_id = 0;
			if (! preg_match('/^\d+$/',$organization_id)) $organization_id = 0;

			app_log("Checking privileges for item ".$media_id.", customer ".$customer_id.", organization ".$organization_id,'debug',__FILE__,__LINE__);

			$get_privileges_query = "
				SELECT	`read`,`write`
				FROM	media_privileges
				WHERE	customer_id = ?
				AND		item_id = ?
				UNION
				SELECT	`read`,`write`
				FROM	media_privileges
				WHERE	organization_id = ?
				AND		item_id = ?
				UNION
				SELECT	`read`,`write`
				FROM	media_privileges
				WHERE	customer_id = ?
				AND		item_id = 0
				UNION
				SELECT	`read`,`write`
				FROM	media_privileges
				WHERE	organization_id = ?
				AND		item_id = 0
				UNION
				SELECT	`read`,`write`
				FROM	media_privileges
				WHERE	customer_id = 0
				AND		organization_id = 0
				AND		item_id = ?
				UNION
				SELECT	`read`,`write`
				FROM	media_privileges
				WHERE	organization_id = 0
				AND		customer_id = 0
				AND		item_id = 0
				LIMIT 1
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_privileges_query,
				array(
					$customer_id,
					$media_id,
					$organization_id,
					$media_id,
					$media_id,
					$customer_id,
					$organization_id
				)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in MediaItem::privileges: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($read,$write) = $rs->FetchRow();

			app_log("Privileges for item ".$media_id.": read => ".$read.", write => ".$write,'debug',__FILE__,__LINE__);
			return array("read" => $read, "write" => $write);
		}
	}
	class MediaDocument extends MediaItem
	{
		public function find($parameters = array())
		{
			$parameters['type'] = 'document';
			return parent::find($parameters);
		}
		public function add($parameters = array())
		{
			$document = parent::add(array("type" => 'document'));
			parent::setMeta($document->id,"name",$parameters['name']);
			return parent::details($document->id);
		}
		public function update($id,$parameters = array())
		{
			if ($parameters['name'])
			{
				parent::setMeta($document->id,"name",$parameters['name']);
			}
			return parent::details($document->id);
		}
	}
	class MediaImage extends MediaItem
	{
		public function find($parameters = array())
		{
			$parameters['type'] = 'image';
			return parent::find($parameters);
		}

		public function resize($image_id,$height,$width)
		{
			$image = $this->details($image_id);
			if (! $image->id)
			{
				$this->error = "Image not found";
				return null;
			}
			$_file = new MediaFile();
			$files = $_file->find(array("item_id" => $image->id));
			list($file) = $files;

			$data = $_file->load($file->id);
			list($owidth,$oheight) = getimagesize($data);
			$gd_image = imagecreatefromstring($width,$height);
			$new_image = imagecreatetruecolor();
			$image_copy_resampled($new_image,$gd_image,0,0,0,0,$width,$height,$owidth,$oheight);

			print_r($file);
		}
	}
	class MediaVideo extends MediaItem
	{
		
	}
	class MediaAudio extends MediaItem
	{
		
	}
	class MediaInit
	{
		public $error = '';
		public function __construct()
		{
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();

			if (! in_array("media__info",$schema_list))
			{
				# Create __info table
				$create_table_query = "
					CREATE TABLE media__info (
						label	varchar(100) not null primary key,
						value	varchar(255)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating info table in MediaInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
			}

			# Check Current Schema Version
			$get_version_query = "
				SELECT	value
				FROM	media__info
				WHERE	label = 'schema_version'
			";

			$rs = $GLOBALS['_database']->Execute($get_version_query);
			if (! $rs)
			{
				$this->error = "SQL Error in MediaInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
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
					CREATE TABLE IF NOT EXISTS `media_items` (
						id				int(11) NOT NULL AUTO_INCREMENT,
						type			enum('raw','audio','video','document','image') NOT NULL default 'raw',
						date_created	datetime,
						date_updated	datetime,
						owner_id		int(11),
						code			varchar(255),
						deleted			int(1) DEFAULT 0,
						PRIMARY KEY (`id`),
						UNIQUE KEY `uk_item_code` (`code`),
						FOREIGN KEY `FK_OWNER_ID` (`owner_id`) REFERENCES register_users (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating media_items table in MediaInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `media_metadata` (
						`item_id`		int(11) NOT NULL,
						`label`			varchar(100) NOT NULL,
						`value`			text,
						UNIQUE KEY `UK_ID_LABEL` (`item_id`,`label`),
						INDEX `IDX_LABEL_VALUE` (`label`,`value`(32)),
						FOREIGN KEY `FK_ITEM_ID` (`item_id`) REFERENCES `media_items` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating media_metadata table in MediaInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `media_files` (
						id				int(11) NOT NULL AUTO_INCREMENT,
						`item_id`		int(11) NOT NULL,
						`code`			varchar(100) NOT NULL,
						`index`			varchar(100) NOT NULL DEFAULT '',
						`size`			int(11) NOT NULL DEFAULT 0,
						`timestamp`		timestamp,
						`mime_type`		varchar(100) NOT NULL DEFAULT 'text/plain',
						`original_file`	varchar(100) DEFAULT '',
						`date_uploaded`	datetime,
						`owner_id`		int(11) NOT NULL,
						PRIMARY KEY `PK_ID`(`id`),
						UNIQUE KEY `UK_ITEM_INDEX` (`item_id`,`index`),
						UNIQUE KEY `UK_CODE` (`code`),
						FOREIGN KEY `FK_ITEM_ID` (`item_id`) REFERENCES `media_items` (`id`),
						FOREIGN KEY `FK_OWNER_ID` (`owner_id`) REFERENCES `register_users` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating media_files table in MediaInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$add_roles_query = "
					INSERT
					INTO	register_roles
					VALUES	(null,'media manager','Can view/edit media'),
							(null,'media reporter','Can view media'),
							(null,'media developer','Can access api')
				";
				$GLOBALS['_database']->Execute($add_roles_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error adding product roles in ProductInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 1;
				$update_schema_version = "
					INSERT
					INTO	media__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error in MediaInit::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
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

				$create_table_query = "
					ALTER TABLE `media_files` ADD disposition enum('inline','attachment','form-data','signal','alert','icon','render','notification') default 'inline'
				";

				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error altering media_files table in MediaInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 2;
				$update_schema_version = "
					INSERT
					INTO	media__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error in MediaInit::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			if ($current_schema_version < 3)
			{
				app_log("Upgrading schema to version 3",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE media_privileges (
						item_id			int(11),
						customer_id		int(11),
						organization_id	int(11),
						`read`			int(1) default 0,
						`write`			int(1) default 0
					)
				";

				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating media_privileges table in MediaInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 3;
				$update_schema_version = "
					INSERT
					INTO	media__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error in MediaInit::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
		}
	}
?>
