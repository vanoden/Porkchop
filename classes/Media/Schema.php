<?php
	namespace Media;

	class Schema {
		public $error = '';
		public function __construct() {
			$this->upgrade();
		}
	
		public function version() {
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();
			$info_table = "media__info";

			if (! in_array($info_table,$schema_list)) {
				# Create __info table
				$create_table_query = "
					CREATE TABLE `$info_table` (
						label	varchar(100) not null primary key,
						value	varchar(255)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating info table in Media::Schema::version(): ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
			}

			# Check Current Schema Version
			$get_version_query = "
				SELECT	value
				FROM	`$info_table`
				WHERE	label = 'schema_version'
			";

			$rs = $GLOBALS['_database']->Execute($get_version_query);
			if (! $rs) {
				$this->error = "SQL Error in Media::Schema::version(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			list($version) = $rs->FetchRow();
			if (! $version) $version = 0;
			return $version;
		}

		public function upgrade() {
			$current_schema_version = $this->version();

			if ($current_schema_version < 1) {
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
					$this->error = "SQL Error creating media_items table in Media::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
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
					$this->error = "SQL Error creating media_metadata table in Media::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
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
					$this->error = "SQL Error creating media_files table in Media::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
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
					$this->error = "SQL Error adding product roles in Media::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
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
					$this->error = "SQL Error altering media_files table in Media::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
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
					$this->error = "SQL Error in Media::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
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
					$this->error = "SQL Error creating media_privileges table in Media::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
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
					$this->error = "SQL Error in Media::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
		}
	}
?>
