<?php
	namespace Storage;

	class Schema Extends \Database\BaseSchema  {
		public $module = "Storage";

		public function upgrade() {
			$this->clearError();

			if ($this->version() < 1) {
				app_log("Upgrading schema to version 1",'notice',__FILE__,__LINE__);
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
				if (! $this->executeSQL($create_table_query)) {
					$this->SQLError("Creating storage_repositories table: ".$this->error());
					return false;
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
				if (! $this->executeSQL($create_table_query)) {
					$this->SQLError("Creating storage_repository_metadata table: ".$this->error());
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `storage_files` (
						`id`			int(11) NOT NULL AUTO_INCREMENT,
						`code`			varchar(100) NOT NULL,
						`repository_id`	int(11) NOT NULL,
						`path` 			varchar(150) NOT NULL,
						`name`			varchar(150) NOT NULL,
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
						FOREIGN KEY `fk_file_repository_id` (`repository_id`) REFERENCES `storage_repositories` (`id`),
						FOREIGN KEY `fk_file_user_id` (`user_id`) REFERENCES `register_users` (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->SQLError("Creating storage_files table: ".$this->error());
					return false;
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
				if (! $this->executeSQL($create_table_query)) {
					$this->SQLError("Creating storage_file_metadata table: ".$this->error());
					return false;
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
				if (! $this->executeSQL($create_table_query)) {
					$this->SQLError("Creating storage_file_roles table: ".$this->error());
					return false;
				}

				$this->setVersion(1);
				$GLOBALS['_database']->CommitTrans();
			}
			
			if ($this->version() < 2) {
				app_log("Upgrading schema to version 2",'notice',__FILE__,__LINE__);

				$table = new \Database\Schema\Table('storage_files');
				if (!$table->has_column('display_name') && ! $table->has_column('description')) {
					$update_table_query = "
						ALTER TABLE storage_files
						ADD display_name varchar(255),
						ADD description text
					";
					if (! $this->executeSQL($update_table_query)) {
						$this->SQLError("Altering storage_files table: ".$this->error());
						return false;
					}
				}

				$this->setVersion(2);
				$GLOBALS['_database']->CommitTrans();
			}
			
			if ($this->version() < 5) {
			
				app_log("Upgrading schema to version 5",'notice',__FILE__,__LINE__);
		        $create_table_query = "
                    CREATE TABLE IF NOT EXISTS `storage_files_types` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `code` varchar(100) NOT NULL,
                      `type` ENUM('support request','support ticket','support action','support rma','support warranty','engineering task','engineering release','engineering project','engineering product'),
                      `ref_id` int(11) NOT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
		        ";
				if (! $this->executeSQL($create_table_query)) {
					$this->SQLError("Creating storage_files_types table: ".$this->error());
					return false;
				}

				$this->setVersion(5);
				$GLOBALS['_database']->CommitTrans();
			}
			
			if ($this->version() < 6) {
				app_log("Upgrading schema to version 6",'notice',__FILE__,__LINE__);

				$table = new \Database\Schema\Table('storage_repositories');
				if (! $table->has_column('default_privileges')) {
					$update_table_query = "
						ALTER TABLE storage_repositories
						ADD COLUMN default_privileges varchar(1024) NOT NULL DEFAULT '{}',
						ADD COLUMN override_privileges varchar(1024) NOT NULL DEFAULT '{\"a\": \"-f\"}'
					";
					if (! $this->executeSQL($update_table_query)) {
						$this->SQLError("Altering storage_files table: ".$this->error());
						return false;
					}
				}
				$table = new \Database\Schema\Table('storage_files');
				if ($table->has_column('read_protect')) {
					$update_table_query = "
						ALTER TABLE storage_files
						DROP column read_protect,
						DROP column write_protect
					";
					if (! $this->executeSQL($update_table_query)) {
						$this->SQLError("Altering storage_files table: ".$this->error());
						return false;
					}
				}
				if (!$table->has_column('date_modified')) {
					$update_table_query = "
						ALTER TABLE storage_files
						ADD COLUMN access_privileges varchar(1024) NOT NULL DEFAULT '{}',
						ADD COLUMN date_modified datetime NOT NULL DEFAULT '1970-01-01' after `date_created`
					";
					if (! $this->executeSQL($update_table_query)) {
						$this->SQLError("Altering storage_files table: ".$this->error());
						return false;
					}
				}
				$drop_table_query = "
					DROP TABLE IF EXISTS storage_file_roles
				";
				if (! $this->executeSQL($drop_table_query)) {
					$this->SQLError("Altering storage_files table: ".$this->error());
					return false;
				}

				$this->setVersion(6);
				$GLOBALS['_database']->CommitTrans();
			}
			return true;
		}
	}
