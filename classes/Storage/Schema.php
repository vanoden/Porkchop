<?php
	namespace Storage;

	class Schema {
	
		public $error;
		public $errno;
		private $info_table = "storage__info";
		private $roles = array(
			'storage manager'   => 'Can manage repositories',
			'storage upload'	=> 'Can upload and manage files'
		);

		public function __construct() {
			$this->upgrade();
		}

		public function version() {
		
			// See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();

			if (! in_array($this->info_table,$schema_list)) {
			
				// Create __info table
				$create_table_query = "
					CREATE TABLE `".$this->info_table."` (
						label	varchar(100) not null primary key,
						value	varchar(255)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating info table in Storage::Schema::version(): ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
			}

			// Check Current Schema Version
			$get_version_query = "
				SELECT	value
				FROM	`".$this->info_table."`
				WHERE	label = 'schema_version'
			";

			$rs = $GLOBALS['_database']->Execute($get_version_query);
			if (! $rs) {
				$this->error = "SQL Error in Storage::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			list($version) = $rs->FetchRow();
			if (!$version) $version = 0;
			return $version;
		}
	
		public function upgrade() {
		
			$current_schema_version = $this->version();
			if ($current_schema_version < 1) {
				app_log("Upgrading schema to version 1",'notice',__FILE__,__LINE__);

				// Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `storage_repositories` (
						`id` 			int(11) NOT NULL AUTO_INCREMENT,
						`code` 			varchar(45) NOT NULL,
						`name`			varchar(255) NOT NULL,
						`type`			varchar(100) NOT NULL,
						`status`		enum('NEW','ACTIVE','DISABLED') NOT NULL DEFAULT 'NEW',
						PRIMARY KEY (`id`),
						UNIQUE KEY `uk_storage_code` (`code`)
					)
				";
				
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating repositories table in Storage::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `storage_repository_metadata` (
						`repository_id` int(11) NOT NULL,
						`key` 			varchar(45) NOT NULL,
						`value`			varchar(255),
						PRIMARY KEY (`repository_id`,`key`),
						FOREIGN KEY `fk_repository_id` (`repository_id`) REFERENCES `storage_repositories` (`id`)
					)
				";
				
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating repository metadata table in Storage::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `storage_files` (
						`id`			int(11) NOT NULL AUTO_INCREMENT,
						`code`			varchar(100) NOT NULL,
						`repository_id`	int(11) NOT NULL,
						`path` 			varchar(255) NOT NULL,
						`name`			varchar(255) NOT NULL,
						`mime_type`		varchar(255) NOT NULL,
						`size`			int(11) NOT NULL,
						`date_created`	datetime,
						`user_id`		int(11) NOT NULL,
						`endpoint`		varchar(255),
						`read_protect`	enum('NONE','AUTH','ROLE','ORGANIZATION','USER') NOT NULL DEFAULT 'NONE',
						`write_protect`	enum('NONE','AUTH','ROLE','ORGANIZATION','USER') NOT NULL DEFAULT 'NONE',
						PRIMARY KEY (`id`),
						UNIQUE KEY `uk_code` (`code`),
						UNIQUE KEY `uk_file_name` (`repository_id`,`path`,`name`),
						FOREIGN KEY `fk_repository_id` (`repository_id`) REFERENCES `storage_repositories` (`id`),
						FOREIGN KEY `fk_user_id` (`user_id`) REFERENCES `register_users` (`id`)
					)
				";
				
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating files table in Storage::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `storage_file_metadata` (
						`file_id` int(11) NOT NULL,
						`key` 			varchar(45) NOT NULL,
						`value`			varchar(255),
						PRIMARY KEY (`file_id`,`key`),
						FOREIGN KEY `fk_file_id` (`file_id`) REFERENCES `storage_files` (`id`)
					)
				";
				
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating file metadata table in Storage::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS storage_file_roles (
						`file_id`	int(11) NOT NULL,
						`role_id`	int(11) NOT NULL,
						`read`		int(1) NOT NULL DEFAULT 0,
						`write`		int(1) NOT NULL DEFAULT 0,
						PRIMARY KEY `pk_role` (`file_id`,`role_id`),
						FOREIGN KEY `fk_file` (`file_id`) REFERENCES `storage_files` (`id`),
						FOREIGN KEY `fk_role` (`role_id`) REFERENCES `register_roles` (`id`)
					)
				";
				
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating file metadata table in Storage::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 1;
				$update_schema_version = "
					INSERT
					INTO	`".$this->info_table."`
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Storage::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}
				
				$GLOBALS['_database']->CommitTrans();
			}
			
			if ($current_schema_version < 2) {
			
				app_log("Upgrading schema to version 2",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$update_table_query = "
					ALTER TABLE storage_files
					ADD display_name varchar(255),
					ADD description text
				";
				$GLOBALS['_database']->Execute($update_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering file table in Storage::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 2;
				$update_schema_version = "
					INSERT
					INTO	`".$this->info_table."`
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Storage::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}
				$GLOBALS['_database']->CommitTrans();
			}

			// Add Roles
			foreach ($this->roles as $name => $description) {
				$role = new \Register\Role();
				if (! $role->get($name)) {
					app_log("Adding role '$name'");
					$role->add(array('name' => $name,'description' => $description));
				}
				if ($role->error) {
					$this->_error = "Error adding role '$name': ".$role->error;
					return false;
				}
				return true;
			}
			
			if ($current_schema_version < 3) {
			
				app_log("Upgrading schema to version 3",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

		        $add_table_query = "
                    CREATE TABLE `storage_files_types` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `code` varchar(100) NOT NULL,
                      `type` ENUM('support request','support ticket','support action','support rma','support warranty','engineering task','engineering release','engineering project','engineering product'),
                      `ref_id` int(11) NOT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
		        ";
		        
				$GLOBALS['_database']->Execute($add_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering file table in Storage::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

			    $current_schema_version = 3;
			    $update_schema_version = "
				    INSERT
				    INTO	`".$this->info_table."`
				    VALUES	('schema_version',$current_schema_version)
				    ON DUPLICATE KEY UPDATE
					    value = $current_schema_version
			    ";
			    $GLOBALS['_database']->Execute($update_schema_version);
			    if ($GLOBALS['_database']->ErrorMsg()) {
				    $this->error = "SQL Error in Storage::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
				    app_log($this->error,'error',__FILE__,__LINE__);
				    $GLOBALS['_database']->RollbackTrans();
				    return 0;
			    }
			    $GLOBALS['_database']->CommitTrans();
			}
		}
	}
