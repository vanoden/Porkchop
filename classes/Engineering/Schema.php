<?php
	namespace Engineering;

	class Schema Extends \Database\BaseSchema {
		public $module = 'engineering';
	
		public function upgrade($max_version = 999) {
			$this->error = null;

            // VERSION 1
			if ($this->version() < 1) {
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
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating engineering_products table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
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
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating engineering_releases table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
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
						FOREIGN KEY `fk_task_product_id` (`product_id`) REFERENCES `engineering_products` (`id`),
						FOREIGN KEY `fk_task_person_id` (`requested_id`) REFERENCES `register_users` (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating engineering_tasks table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$create_table_query = "
					CREATE TABLE `engineering_events` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`task_id` int(11) NOT NULL,
						`person_id` int(11) NOT NULL,
						`description` text,
						`date_event` datetime NOT NULL,
						PRIMARY KEY (`id`),
						FOREIGN KEY `fk_eng_task_id` (`task_id`) REFERENCES engineering_tasks (`id`),
						FOREIGN KEY `fk_eng_person_id` (`person_id`) REFERENCES register_users (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating engineering_events table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(1);
				$GLOBALS['_database']->CommitTrans();
			}
			
            // VERSION 2
			if ($this->version() < 2) {
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
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating engineering_projects table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$alter_table_query = "
					ALTER TABLE `engineering_tasks` ADD COLUMN `project_id` int(11)
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->error = "SQL Error altering engineering_tasks table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(2);
				$GLOBALS['_database']->CommitTrans();
			}
			
            // VERSION 3
			if ($this->version() < 3) {
				app_log("Upgrading schema to version 3",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$alter_table_query = "
					ALTER TABLE engineering_releases MODIFY COLUMN status enum('NEW','TESTING','RELEASED') NOT NULL DEFAULT 'NEW';
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->error = "SQL Error altering engineering_releases table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(3);
				$GLOBALS['_database']->CommitTrans();
			}
			
            // VERSION 4
			if ($this->version() < 4) {
				app_log("Upgrading schema to version 4",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$alter_table_query = "
					ALTER TABLE `engineering_tasks` ADD COLUMN `prerequisite_id` VARCHAR(11);
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->error = "SQL Error altering engineering_tasks table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(4);
				$GLOBALS['_database']->CommitTrans();
			}
			
            // VERSION 5
			if ($this->version() < 5) {
				app_log("Upgrading schema to version 5",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$alter_table_query = "
					ALTER TABLE `engineering_projects` ADD `status` enum('NEW', 'OPEN', 'HOLD', 'CANCELLED', 'COMPLETE') NOT NULL DEFAULT 'NEW';
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->error = "SQL Error altering engineering_projects table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(5);
				$GLOBALS['_database']->CommitTrans();
			}		
			
            // VERSION 6
			if ($this->version() < 6) {
				app_log("Upgrading schema to version 6",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$alter_table_query = "
					ALTER TABLE `engineering_tasks` MODIFY `status` enum('NEW','HOLD','ACTIVE','CANCELLED','TESTING','COMPLETE') DEFAULT 'NEW'
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->error = "SQL Error altering engineering_tasks table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(6);
				$GLOBALS['_database']->CommitTrans();
			}
			
            // VERSION 7
			if ($this->version() < 7) {
				app_log("Upgrading schema to version 7",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$alter_table_query = "
					ALTER TABLE `engineering_tasks` MODIFY `status` enum('NEW','HOLD','ACTIVE','CANCELLED','BROKEN','TESTING','COMPLETE') DEFAULT 'NEW'
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->error = "SQL Error altering engineering_tasks table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(7);
				$GLOBALS['_database']->CommitTrans();
			}
			
            // VERSION 8
			if ($this->version() < 8) {
				app_log("Upgrading schema to version 8",'notice',__FILE__,__LINE__);

				// Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
                    CREATE TABLE `engineering_task_comments` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `date_comment` datetime DEFAULT NULL,
                      `content` text DEFAULT NULL,
                      `code` varchar(100) NOT NULL,  
                      `user_id` int(11) DEFAULT NULL,
                      PRIMARY KEY (`id`),
                      FOREIGN KEY `fk_eng_task_comments` (`code`) REFERENCES `engineering_tasks` (`code`),
                      FOREIGN KEY `fk_eng_task_comment_users` (`user_id`) REFERENCES `register_users` (`id`)
                    )
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating engineering_task_comments table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(8);
				$GLOBALS['_database']->CommitTrans();
			}

            // VERSION 9
			if ($this->version() == -1) {
				app_log("Upgrading schema to version 9",'notice',__FILE__,__LINE__);

				// Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
                    CREATE TABLE `engineering_task_hours` (
                      `id` int NOT NULL AUTO_INCREMENT,
                      `date_worked` datetime DEFAULT NULL,
                      `number_of_hours` decimal(5,2) DEFAULT 0,
                      `code` varchar(100) NOT NULL,
                      `user_id` int DEFAULT NULL,
                      PRIMARY KEY (`id`),
                      KEY `engineering_task_hours_ibfk_1` (`code`),
                      KEY `engineering_task_hours_ibfk_2` (`user_id`),
                      CONSTRAINT `engineering_task_hours_ibfk_1` FOREIGN KEY (`code`) REFERENCES `engineering_tasks` (`code`),
                      CONSTRAINT `engineering_task_hours_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `register_users` (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=latin1
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating engineering_task_hours table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(9);
				$GLOBALS['_database']->CommitTrans();
			}			

			if ($this->version() < 10) {
				app_log("Upgrading to version 10",'notice');

				// Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$drop_table_query = "
					DROP TABLE IF EXISTS `engineering_task_hours`
				";
				if (! $this->executeSQL($drop_table_query)) {
					$this->error = "SQL Error dropping engineering_task_hours table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$alter_table_query = "
                    ALTER TABLE `engineering_events` ADD `hours_worked` decimal(5,2) not null default 0
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->error = "SQL Error altering engineering_events table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(10);
				$GLOBALS['_database']->CommitTrans();
			}
			
			if ($this->version() < 11) {
				app_log("Upgrading to version 11",'notice');

				// Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$alter_table_query = "
                    ALTER TABLE `engineering_tasks` ADD `testing_details` TEXT DEFAULT NULL
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->error = "SQL Error altering engineering_tasks table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(11);
				$GLOBALS['_database']->CommitTrans();
			}
			
			if ($this->version() < 12) {
				app_log("Upgrading to version 12",'notice');

				// Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$alter_table_query = "
                    ALTER TABLE `engineering_releases` ADD `package_version_id` int(11) DEFAULT NULL
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->error = "SQL Error altering engineering_releases table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(12);
				$GLOBALS['_database']->CommitTrans();
			}
			
			if ($this->version() < 13) {
				app_log("Upgrading to version 13",'notice');

				// Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$alter_table_query = "
                    ALTER TABLE `engineering_releases` ADD `duplicate_task_id` int(11) DEFAULT NULL
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->error = "SQL Error altering engineering_releases table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(13);
				$GLOBALS['_database']->CommitTrans();
			}
			
			return true;	
		}

		public $roles = array(
			'engineering manager'	=> 'Full control over products, releases, tasks',
			'engineering user'		=> 'Can view products'
		);
	}
