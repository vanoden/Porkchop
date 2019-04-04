<?php
	namespace Engineering;

	class Schema {
		public $error;
		public $errno;

		public function __construct() {
			$this->upgrade();
		}

		public function version() {
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();
			$info_table = "engineering__info";

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
					$this->error = "SQL Error creating info table in Engineering::Schema::version: ".$GLOBALS['_database']->ErrorMsg();
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
				$this->error = "SQL Error in Engineering::Schema::version: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			list($version) = $rs->FetchRow();
			if (! $version) $version = 0;
			return $version;
		}
		public function upgrade() {
			$current_schema_version = $this->version();

            // VERSION 1
			if ($current_schema_version < 1) {
				app_log("Upgrading schema to version 1",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `engineering_products` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`code` varchar(45) NOT NULL,
						`title` varchar(255) NOT NULL,
						`description` text,
						PRIMARY KEY (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating products table in Engineering::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `engineering_releases` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`code` varchar(45) NOT NULL,
						`title` varchar(255) DEFAULT NULL,
						`description` text,
						`status` enum('NEW','RELEASED') NOT NULL DEFAULT 'NEW',
						`date_released` datetime DEFAULT NULL,
						`date_scheduled` datetime DEFAULT NULL,
						PRIMARY KEY (`id`),
						UNIQUE KEY `idx_code` (`code`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating releases table in Engineering::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `engineering_tasks` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`code` varchar(100) NOT NULL,
						`title` varchar(255) NOT NULL,
						`date_added` datetime DEFAULT NULL,
						`description` text,
						`status` enum('NEW','HOLD','ACTIVE','CANCELLED','COMPLETE') NOT NULL DEFAULT 'NEW',
						`type` enum('BUG','FEATURE','TEST') NOT NULL DEFAULT 'BUG',
						`estimate` decimal(6,2) not null default 0,
						`location` varchar(255),
						`release_id` int(11),
						`product_id` int(11) NOT NULL,
						`requested_id` int(11) NOT NULL,
						`assigned_id` int(11) NOT NULL DEFAULT 0,
						`date_due` datetime,
						`priority` enum('NORMAL','IMPORTANT','URGENT','CRITICAL') NOT NULL DEFAULT 'NORMAL',
						PRIMARY KEY (`id`),
						UNIQUE KEY `UK_CODE` (`code`),
						FOREIGN KEY `fk_product_id` (`product_id`) REFERENCES `engineering_products` (`id`),
						FOREIGN KEY `fk_person_id` (`requested_id`) REFERENCES `register_users` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating tasks table in Engineering::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$create_table_query = "
					CREATE TABLE `engineering_events` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`task_id` int(11) NOT NULL,
						`person_id` int(11) NOT NULL,
						`description` text,
						`date_event` datetime NOT NULL,
						PRIMARY KEY (`id`),
						FOREIGN KEY `fk_task_id` (`task_id`) REFERENCES engineering_tasks (`id`),
						FOREIGN KEY `fk_person_id` (`person_id`) REFERENCES register_users (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating events table in Engineering::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$add_roles_query = "
					INSERT
					INTO	register_roles
					VALUES	(null,'engineering manager','Full control over products, releases, tasks'),
							(null,'engineering user','Can view products')
				";
				$GLOBALS['_database']->Execute($add_roles_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error adding monitor roles in Engineering::Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$current_schema_version = 1;
				$update_schema_version = "
					INSERT
					INTO	engineering__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Engineering::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			
            // VERSION 2
			if ($current_schema_version < 2) {
				app_log("Upgrading schema to version 2",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `engineering_projects` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`code` varchar(45) NOT NULL,
						`title` varchar(255) NOT NULL,
						`description` text,
						`manager_id` int(11),
						PRIMARY KEY (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating engineering_projects table in Engineering::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$alter_table_query = "
					ALTER TABLE `engineering_tasks` ADD COLUMN `project_id` int(11)
				";
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering engineering_tasks table in Engineering::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$current_schema_version = 2;
				$update_schema_version = "
					INSERT
					INTO	engineering__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Engineering::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			
            // VERSION 3
			if ($current_schema_version < 3) {
				app_log("Upgrading schema to version 3",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$alter_table_query = "
					ALTER TABLE engineering_releases MODIFY COLUMN status enum('NEW','TESTING','RELEASED') NOT NULL DEFAULT 'NEW';
				";
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering engineering_releases table in Engineering::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$current_schema_version = 3;
				$update_schema_version = "
					INSERT
					INTO	engineering__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Engineering::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			
            // VERSION 4
			if ($current_schema_version < 4) {
				app_log("Upgrading schema to version 4",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$alter_table_query = "
					ALTER TABLE `engineering_tasks` ADD COLUMN `prerequisite_id` VARCHAR(11);
				";
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering engineering_releases table in Engineering::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$current_schema_version = 4;
				$update_schema_version = "
					INSERT
					INTO	engineering__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Engineering::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			
            // VERSION 5
			if ($current_schema_version < 5) {
				app_log("Upgrading schema to version 5",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$alter_table_query = "
					ALTER TABLE `engineering_projects` ADD `status` enum('NEW', 'OPEN', 'HOLD', 'CANCELLED', 'COMPLETE') NOT NULL DEFAULT 'NEW';
				";
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering engineering_releases table in Engineering::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$current_schema_version = 5;
				$update_schema_version = "
					INSERT
					INTO	engineering__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Engineering::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}
				$GLOBALS['_database']->CommitTrans();
			}		
			
            // VERSION 6
			if ($current_schema_version < 6) {
				app_log("Upgrading schema to version 6",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$alter_table_query = "
					ALTER TABLE `engineering_tasks` MODIFY `status` enum('NEW','HOLD','ACTIVE','CANCELLED','TESTING','COMPLETE') DEFAULT 'NEW'
				";

				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering engineering_tasks table in Engineering::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$current_schema_version = 6;
				$update_schema_version = "
					INSERT
					INTO	engineering__info
					VALUES	('schema_version', $current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Engineering::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}
				$GLOBALS['_database']->CommitTrans();
			}
                // add new pending_customers page HERE @TODO
                
                // page_pages
                // 222	support	search
                
                // page_metadata
                // 126	222	template	admin.html

                // INSERT INTO `page_pages` (module, view) VALUES ("support", "pending_customers")

                // INSERT INTO `page_metadata` (`page_id`, `key`, `value`) VALUES ('224', 'template', 'admin.html');

		}
	}
