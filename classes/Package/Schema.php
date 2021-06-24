<?php
	namespace Package;

	class Schema Extends \Database\BaseSchema {
		public $module = "Package";

		public function upgrade() {
			$this->error = null;
			$current_schema_version = $this->version();

			if ($this->version() < 2) {
				app_log("Upgrading schema to version 2",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `package_packages` (
						`id` 			int(11) NOT NULL AUTO_INCREMENT,
						`code` 			varchar(45) NOT NULL,
						`name`			varchar(255),
						`description`	text,
						`license`		text,
						`platform`		varchar(255),
						`owner_id`		int(11) NOT NULL,
						`status`		enum('TEST','ACTIVE','HIDDEN') NOT NULL DEFAULT 'TEST',
						`repository_id`	int(11) NOT NULL,
						`date_created`	datetime,
						PRIMARY KEY `pk_package_id` (`id`),
						UNIQUE KEY `uk_package_code` (`code`),
						FOREIGN KEY `fk_package_owner_id` (`owner_id`) REFERENCES `register_users` (`id`),
						FOREIGN KEY `fk_package_repo_id` (`repository_id`) REFERENCES `storage_repositories` (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating package_packages table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `package_versions` (
						`id`			int(11) NOT NULL PRIMARY KEY,
						`package_id`	int(11) NOT NULL,
						`major`			int(3) NOT NULL,
						`minor`			int(3) NOT NULL,
						`build`			varchar(10) NOT NULL,
						`status`		enum('NEW','PUBLISHED','HIDDEN'),
						`date_created`	datetime,
						`date_published` datetime,
						`user_id`		int(11) NOT NULL,
						FOREIGN KEY `fk_package_version` (`package_id`) REFERENCES `package_packages` (`id`),
						FOREIGN KEY `fk_package_version_file` (`id`) REFERENCES `storage_files` (`id`),
						FOREIGN KEY `fk_package_version_user` (`user_id`) REFERENCES `register_users` (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating package_versions table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(2);
				$GLOBALS['_database']->CommitTrans();
			}
			return true;
		}
	}
