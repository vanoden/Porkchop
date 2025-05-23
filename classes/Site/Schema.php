<?php
	namespace Site;

	class Schema Extends \Database\BaseSchema {
		public function __construct() {
			$this->module = "session";
			parent::__construct();
		}

		public function upgrade() {
			$this->clearError();

			$database = new \Database\Service();

			if ($this->version() < 2) {
				app_log("Upgrading ".$this->module." schema to version 2",'notice',__FILE__,__LINE__);
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `session_sessions` (
					  `active` int(1) NOT NULL DEFAULT '1',
					  `id` int(10) NOT NULL AUTO_INCREMENT,
					  `code` varchar(32) NOT NULL DEFAULT '',
					  `user_id` int(6) DEFAULT NULL,
					  `last_hit_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					  `first_hit_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					  `browser` varchar(255) DEFAULT NULL,
					  `company_id` int(5) NOT NULL DEFAULT '0',
					  `c_id` int(8) DEFAULT NULL,
					  `e_id` int(8) DEFAULT NULL,
					  `prev_session` varchar(100) NOT NULL DEFAULT '',
					  `refer_url` text,
					  PRIMARY KEY (`id`),
					  KEY `idx_sess_company_id` (`company_id`,`user_id`),
					  KEY `idx_sess_code` (`code`),
					  KEY `idx_sess_end_time` (`last_hit_date`),
					  KEY `idx_sess_active` (`company_id`,`active`,`id`,`user_id`),
					  FOREIGN KEY `fk_sess_company_id` (`company_id`) REFERENCES `company_companies` (`id`)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Creating session_sessions table: ".$database->error());
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `session_hits` (
					  `id` int(10) NOT NULL AUTO_INCREMENT,
					  `session_id` int(10) NOT NULL DEFAULT '0',
					  `server_id` int(11) NOT NULL DEFAULT '0',
					  `hit_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					  `remote_ip` varchar(20) DEFAULT NULL,
					  `secure` int(1) NOT NULL DEFAULT '0',
					  `script` varchar(100) NOT NULL DEFAULT '',
					  `query_string` text,
					  `order_id` int(8) NOT NULL DEFAULT '0',
					  `module_id` int(3) NOT NULL,
					  PRIMARY KEY (`id`),
					  KEY `session_id` (`session_id`)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Creating session_hits table: ".$database->error());
					return false;
				}

				$this->setVersion(2);
				$database->CommitTrans();
			}
			if ($this->version() < 3) {
				app_log("Upgrading ".$this->module." schema to version 3",'notice',__FILE__,__LINE__);
				$create_table_query = "
					ALTER TABLE `session_sessions` MODIFY `code` char(64) NOT NULL DEFAULT ''
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("altering session_sessions table: ".$database->error());
					return false;
				}

				$this->setVersion(3);
				$database->CommitTrans();
			}
			if ($this->version() < 4) {
				app_log("Upgrading ".$this->module." schema to version 4",'notice',__FILE__,__LINE__);
				$create_table_query = "
					ALTER TABLE `session_sessions` ADD `timezone` varchar(32) NOT NULL DEFAULT 'America/New_York'
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("altering session_sessions table: ".$database->error());
					return false;
				}

				$this->setVersion(4);
				$database->CommitTrans();
			}
			if ($this->version() < 5) {
				app_log("Upgrading ".$this->module." schema to version 5",'notice',__FILE__,__LINE__);
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `page_pages` (
					  `id` int(5) NOT NULL AUTO_INCREMENT,
					  `module` varchar(100) NOT NULL,
					  `view` varchar(100) NOT NULL,
					  `index` varchar(100) NOT NULL DEFAULT '',
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `uk_page_views` (`module`,`view`,`index`)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Creating page_pages table: ".$database->error());
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `page_metadata` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`page_id` int(11) NOT NULL,
						`key` varchar(32) NOT NULL,
						`value` text,
						PRIMARY KEY (`id`),
						UNIQUE KEY `UK_PAGE_METADATA_PAGE_KEY` (`page_id`,`key`),
						CONSTRAINT `FK_PAGE_METADATA_PAGE_ID` FOREIGN KEY (`page_id`) REFERENCES `page_pages` (`id`)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Creating page_metadata table: ".$database->error());
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `page_widget_types` (
					  `id` int(5) NOT NULL AUTO_INCREMENT,
					  `name` varchar(100) NOT NULL,
					  PRIMARY KEY `pk_widget_type` (`id`),
					  UNIQUE KEY `uk_name` (`name`)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Creating page_widget_types table: ".$database->error());
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `page_widgets` (
					  `id` int(10) NOT NULL AUTO_INCREMENT,
					  `page_view_id` int(5) NOT NULL,
					  `type_id` int(10) NOT NULL DEFAULT '0',
					  PRIMARY KEY (`id`),
					  FOREIGN KEY `fk_page_view` (`page_view_id`) REFERENCES `page_metadata` (`id`),
					  FOREIGN KEY `fk_widget_type` (`type_id`) REFERENCES `page_widget_types` (`id`)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Creating page_widgets table: ".$database->error());
					return false;
				}

				$this->setVersion(5);
				$database->CommitTrans();
			}
			if ($this->version() < 6) {
				app_log("Upgrading ".$this->module." schema to version 6",'notice',__FILE__,__LINE__);
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `site_configurations` (
						`key`	varchar(150) NOT NULL PRIMARY KEY,
						`value` varchar(255)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Creating site_configurations table: ".$database->error());
					return false;
				}

				$this->setVersion(6);
				$database->CommitTrans();
			}
			if ($this->version() < 7) {
				app_log("Upgrading ".$this->module." schema to version 7",'notice',__FILE__,__LINE__);
				$create_table_query = "
	                CREATE TABLE IF NOT EXISTS `site_messages` (
	                  `id` int(10) NOT NULL AUTO_INCREMENT,
	                  `user_created` int(11) NOT NULL,
	                  `date_created` timestamp NOT NULL,
	                  `important` boolean NOT NULL,
	                  `content` text,
                      `parent_id` int(11) NULL,
	                  PRIMARY KEY (`id`),
	                  KEY `fk_user_created` (`user_created`),
                      CONSTRAINT `register_users_ibfk_1` FOREIGN KEY (`user_created`) REFERENCES `register_users` (`id`)
                    )
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Creating site_messages table: ".$database->error());
					return false;
				}

				$create_table_query = "
	                CREATE TABLE IF NOT EXISTS `site_message_deliveries` (
	                  `id` int(10) NOT NULL AUTO_INCREMENT,
      	              `message_id` int(10) NOT NULL,
      	              `user_id` int(10) NOT NULL,
      	              `date_viewed` timestamp NOT NULL,
      	              `date_acknowledged` timestamp NOT NULL,
	                  PRIMARY KEY (`id`),
	                  KEY `fk_message_id` (`message_id`),
	                  KEY `fk_user_id` (`user_id`),
                      CONSTRAINT `message_id_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `site_messages` (`id`),
                      CONSTRAINT `user_id_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `register_users` (`id`)
                    )
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Creating site_message_deliveries table: ".$database->error());
					return false;
				}
				
				$create_table_query = "
	                 CREATE TABLE `site_messages_metadata` (
                      `item_id` int NOT NULL,
                      `label` varchar(200) NOT NULL,
                      `value` text,
                      UNIQUE KEY `UK_ID_LABEL` (`item_id`,`label`),
                      KEY `IDX_LABEL_VALUE` (`label`,`value`(32)),
                      CONSTRAINT `site_messages_metadata_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `site_messages` (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Creating site_messages_metadata table: ".$database->error());
					return false;
				}

				$this->setVersion(7);
				$database->CommitTrans();
			}
			if ($this->version() < 8) {
				app_log("Upgrading ".$this->module." schema to version 8",'notice',__FILE__,__LINE__);
				$create_table_query = "
					ALTER TABLE `site_message_deliveries`
					MODIFY `date_viewed` timestamp NULL default NULL,
					MODIFY `date_acknowledged` timestamp NULL default NULL
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Creating site_configurations table: ".$database->error());
					return false;
				}

				$this->setVersion(8);
				$database->CommitTrans();
			}
			
			if ($this->version() < 9) {
				app_log("Upgrading ".$this->module." schema to version 9",'notice',__FILE__,__LINE__);
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `counters_watched` (
  	                    `id`    int(10) NOT NULL AUTO_INCREMENT,
						`key`	varchar(150) NOT NULL,
						`notes` varchar(255),
						PRIMARY KEY (`id`)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Creating counters_watched table: ".$database->error());
					return false;
				}

				$this->setVersion(9);
				$database->CommitTrans();
			}

			if ($this->version() < 10) {
				app_log("Upgrading ".$this->module." schema to version 10",'notice',__FILE__,__LINE__);
				$alter_table_query = "
                    ALTER TABLE `session_sessions` ADD COLUMN `super_elevation_expires` DATETIME DEFAULT NULL;
				";
				if (! $database->Execute($alter_table_query)) {
					$this->SQLError("altering session_sessions table: ".$database->error());
					return false;
				}

				$this->setVersion(10);
				$database->CommitTrans();
			}

			if ($this->version() < 11) {
				app_log("Upgrading ".$this->module." schema to version 11",'notice',__FILE__,__LINE__);
				$alter_table_query = "
                    ALTER TABLE `session_sessions` ADD COLUMN `oauth2_state` varchar(255) DEFAULT NULL;
				";
				if (! $database->Execute($alter_table_query)) {
					$this->SQLError("altering session_sessions table: ".$database->error());
					return false;
				}

				$this->setVersion(11);
				$database->CommitTrans();
			}
			
			if ($this->version() < 12) {
				app_log("Upgrading ".$this->module." schema to version 12",'notice',__FILE__,__LINE__);
				$alter_table_query = "
                    ALTER TABLE `site_messages` ADD COLUMN `subject` text DEFAULT NULL AFTER `important`;
				";
				if (! $database->Execute($alter_table_query)) {
					$this->SQLError("altering session_sessions table: ".$database->error());
					return false;
				}

				$this->setVersion(12);
				$database->CommitTrans();
			}
			
			if ($this->version() < 13) {
				app_log("Upgrading ".$this->module." schema to version 13",'notice',__FILE__,__LINE__);
				$alter_table_query = "
                    ALTER TABLE `site_messages` ADD COLUMN `recipient_id` int NULL AFTER `user_created`;
				";
				if (! $database->Execute($alter_table_query)) {
					$this->SQLError("altering site_messages table: ".$database->error());
					return false;
				}

		        $alter_table_query = "
		            ALTER TABLE `site_messages` ADD FOREIGN KEY (recipient_id) REFERENCES `register_users` (`id`)
		        ";
				if (! $database->Execute($alter_table_query)) {
					$this->SQLError("altering `site_messages` table: ".$database->error());
					return false;
				}

				$this->setVersion(13);
				$database->CommitTrans();
			}
			
			if ($this->version() < 14) {
				app_log("Upgrading ".$this->module." schema to version 14",'notice',__FILE__,__LINE__);
				$create_table_query = "
                    CREATE TABLE IF NOT EXISTS `site_headers`(
                        id  int(11) NOT NULL AUTO_INCREMENT,
                        name    varchar(32) NOT NULL,
                        value   varchar(256) NOT NULL,
                        PRIMARY KEY `pk_id` (`id`),
                        UNIQUE KEY `uk_name` (`name`)
                    )
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("altering site_messages table: ".$database->error());
					return false;
				}

				$this->setVersion(14);
				$database->CommitTrans();
			}
			if ($this->version() < 15) {
				app_log("Upgrading ".$this->module." schema to version 15",'notice',__FILE__,__LINE__);
				$alter_table_query = "
                    ALTER TABLE `site_headers` modify `value` varchar(1024)
				";
				if (! $database->Execute($alter_table_query)) {
					$this->SQLError("altering site_headers table: ".$database->error());
					return false;
				}

				$this->setVersion(15);
				$database->CommitTrans();
			}
			if ($this->version() < 16) {
				app_log("Upgrading ".$this->module." schema to version 16",'notice',__FILE__,__LINE__);
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `site_terms_of_use` (
						id				int(11) NOT NULL AUTO_INCREMENT,
						code			char(16) NOT NULL,
						name			varchar(128) NOT NULL,
						description		varchar(256),
						PRIMARY KEY `pk_tou_id` (`id`),
						UNIQUE KEY `uk_tou_code` (`code`),
						UNIQUE KEY `uk_tou_name` (`name`)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("altering site_terms_of_use table: ".$database->error());
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `site_terms_of_use_versions` (
						id				int(11) NOT NULL AUTO_INCREMENT,
						tou_id			int(11) NOT NULL,
						status			enum('NEW','PUBLISHED','RETRACTED') NOT NULL DEFAULT 'NEW',
						content			text,
						PRIMARY KEY `pk_tou_id` (`id`),
						INDEX `idx_tou_status` (`status`),
						FOREIGN KEY `fk_tou_id` (`tou_id`) REFERENCES `site_terms_of_use` (`id`)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("altering site_terms_of_use_versions table: ".$database->error());
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `site_terms_of_use_events` (
						version_id	int(11) NOT NULL,
						user_id		int(11) NOT NULL,
						date_event	datetime,
						type		enum('CREATION','ACTIVATION','RETRACTION') NOT NULL DEFAULT 'CREATION',
						INDEX `idx_tou_evt_user_date` (`user_id`,`date_event`,`type`),
						INDEX `idx_tou_evt_date_user` (`date_event`,`user_id`,`type`),
						FOREIGN KEY `fk_tou_event_version` (`version_id`) REFERENCES `site_terms_of_use_versions` (`id`),
						FOREIGN KEY `fk_tou_event_user` (`user_id`) REFERENCES `register_users` (`id`)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("altering site_terms_of_use_events table: ".$database->error());
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `site_terms_of_use_actions` (
						version_id	int(11) NOT NULL,
						user_id		int(11) NOT NULL,
						date_action	datetime,
						type		enum('VIEWED','DECLINED','ACCEPTED') NOT NULL DEFAULT 'VIEWED',
						INDEX `idx_tou_act_user_date` (`user_id`,`date_action`,`type`),
						INDEX `idx_tou_act_date_user` (`date_action`,`user_id`,`type`),
						FOREIGN KEY `fk_tou_action_version` (`version_id`) REFERENCES `site_terms_of_use_versions` (`id`),
						FOREIGN KEY `fk_tou_action_user` (`user_id`) REFERENCES `register_users` (`id`)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("altering site_terms_of_use_events table: ".$database->error());
					return false;
				}

				$alter_table_query = "
                    ALTER TABLE `page_pages` add `tou_id` int(11)
				";
				if (! $database->Execute($alter_table_query)) {
					$this->SQLError("altering page_pages table: ".$database->error());
					return false;
				}

				$this->setVersion(16);
				$database->CommitTrans();
			}
			if ($this->version() < 17) {
				app_log("Upgrading ".$this->module." schema to version 17",'notice',__FILE__,__LINE__);
				$alter_table_query = "
                    ALTER TABLE `site_terms_of_use_events` add `id` int(11) PRIMARY KEY AUTO_INCREMENT
				";
				if (! $database->Execute($alter_table_query)) {
					$this->SQLError("altering page_pages table: ".$database->error());
					return false;
				}

				$this->setVersion(17);
				$database->CommitTrans();
			}
			if ($this->version() < 18) {
				app_log("Upgrading ".$this->module." schema to version 18",'notice',__FILE__,__LINE__);
				$alter_table_query = "
                    ALTER TABLE `page_pages` add `sitemap` int(1) DEFAULT 0
				";
				if (! $database->Execute($alter_table_query)) {
					$this->SQLError("altering page_pages table: ".$database->error());
					return false;
				}

				$this->setVersion(18);
				$database->CommitTrans();
			}
			if ($this->version() < 19) {
				app_log("Upgrading ".$this->module." schema to version 19",'notice',__FILE__,__LINE__);
				$alter_table_query = "
                    ALTER TABLE `site_terms_of_use_actions` add `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY
				";
				if (! $database->Execute($alter_table_query)) {
					$this->SQLError("altering site_terms_of_use_actions table: ".$database->error());
					return false;
				}

				$this->setVersion(19);
				$database->CommitTrans();
			}
		
			if ($this->version() < 20) {

				app_log("Upgrading ".$this->module." schema to version 20",'notice',__FILE__,__LINE__);
				$alter_table_query = "
					ALTER TABLE `site_terms_of_use_versions` ADD COLUMN `version_number` int DEFAULT NULL AFTER id;
				";
				if (! $database->Execute($alter_table_query)) {
					$this->SQLError("altering site_terms_of_use_actions table: ".$database->error());
					return false;
				}

				// sql query to update version number (for legacy instance with existing TOS entries)
				$update_version_query = "SET @version_number := 0;";
				if (! $database->Execute($update_version_query)) {
					$this->SQLError("error updating legacy site_terms_of_use_actions version_number(s): ".$database->error());
					return false;
				}
				
				$update_version_query = "SET @prev_tou_id := 0;";
				if (! $database->Execute($update_version_query)) {
					$this->SQLError("error updating legacy site_terms_of_use_actions version_number(s): ".$database->error());
					return false;
				}
				
				// increment version numbers by 1 for each tou_id
				$update_version_query = "
					UPDATE `site_terms_of_use_versions` t1
					JOIN (
					SELECT `id`, `tou_id`,
						(@version_number := IF(@prev_tou_id = tou_id, @version_number + 1,
											IF(@prev_tou_id := tou_id, 1, 1))) as new_version_number
					FROM `site_terms_of_use_versions`
					ORDER BY `tou_id`, `id`
					) t2
					ON t1.id = t2.id
					SET t1.version_number = t2.new_version_number;
				";

				if (! $database->Execute($update_version_query)) {
					$this->SQLError("error updating legacy site_terms_of_use_actions version_number(s): ".$database->error());
					return false;
				}
				
				// add key to table
				$alter_table_query = "
					ALTER TABLE `site_terms_of_use_versions` ADD KEY `idx_tou_version_number` (`version_number`,`tou_id`);
				";
				if (! $database->Execute($alter_table_query)) {
					$this->SQLError("altering site_terms_of_use_actions table: ".$database->error());
					return false;
				}

				// make version number not null for future entries
				$alter_table_query = "
					ALTER TABLE `site_terms_of_use_versions` MODIFY `version_number` int NOT NULL;
				";
				if (! $database->Execute($alter_table_query)) {
					$this->SQLError("altering site_terms_of_use_actions table: ".$database->error());
					return false;
				}

				$this->setVersion(20);
				$database->CommitTrans();
			}

			if ($this->version() < 21) {
				app_log("Upgrading ".$this->module." schema to version 21",'notice',__FILE__,__LINE__);
				$create_table_query = "
					CREATE TABLE `site_audit_events` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`event_date` datetime NOT NULL,
						`user_id` int(11) NOT NULL,
						`instance_id` int(11) NOT NULL,
						`class_name` varchar(64) NOT NULL,
						`class_method` varchar(64) NOT NULL,
						`description` text NOT NULL,
						PRIMARY KEY (`id`),
						KEY `class_name_instance_id_class_method` (`class_name`, `instance_id`, `class_method`),
						KEY `event_date_user_id` (`event_date`, `user_id`),
						KEY `user_id_class_name_class_method` (`user_id`, `class_name`, `class_method`),
						CONSTRAINT `site_audit_events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `register_users` (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("create site_audit_events table: ".$database->error());
					return false;
				}

				$this->setVersion(21);
				$database->CommitTrans();
			}

			if ($this->version() < 23) {
				app_log("Upgrading ".$this->module." schema to version 23",'notice',__FILE__,__LINE__);

				// MOVED TO SEARCH SCHEMA
				$this->setVersion(23);
				$database->CommitTrans();
			}
			if ($this->version() < 24) {
				app_log("Upgrading schema to version 24",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $database->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
                    CREATE TABLE IF NOT EXISTS `navigation_menus` (
                      `id` int(5) NOT NULL AUTO_INCREMENT,
                      `code` varchar(100) NOT NULL,
                      `title` varchar(100) NOT NULL DEFAULT '',
                      PRIMARY KEY (`id`),
                      UNIQUE KEY `uk_code` (`code`)
                    )
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("create navigation_menus table: ".$database->error());
					app_log($this->error(), 'error');
					return false;
				}

				$create_table_query = "
                    CREATE TABLE IF NOT EXISTS `navigation_menu_items` (
                      `id` int(8) NOT NULL AUTO_INCREMENT,
                      `menu_id` int(11) NOT NULL DEFAULT '0',
                      `title` varchar(100) NOT NULL DEFAULT '',
                      `target` varchar(200) NOT NULL DEFAULT '',
                      `view_order` int(3) DEFAULT NULL,
                      `alt` varchar(255),
					  `description` text,
                      `parent_id` int(5) NOT NULL DEFAULT '0',
                      `external` int(1) NOT NULL DEFAULT '0',
                      `ssl` int(11) NOT NULL DEFAULT '0',
					  `required_role_id` int(11) DEFAULT NULL,
                      PRIMARY KEY (`id`),
                      KEY `parent_id` (`parent_id`),
                      KEY `view_order` (`view_order`),
                      FOREIGN KEY `fk_menu_id` (`menu_id`) REFERENCES `navigation_menus` (`id`)
                    )
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("create navigation_menu_items table: ".$database->error());
					app_log($this->error(), 'error');
					return false;
				}

				$this->setVersion(24);
				$database->CommitTrans();
			}

			if ($this->version() < 25) {
				app_log("Upgrading ".$this->module." schema to version 25",'notice',__FILE__,__LINE__);

				// Need Storage Schema to be installed first
				$prerequisite = new \Storage\Schema();
				$prerequisite->upgrade();

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `object_images` (
					  `object_id` int NOT NULL,
					  `object_type` varchar(50) NOT NULL,
					  `image_id` int NOT NULL,
					  `label` varchar(100) NOT NULL,
					  `view_order` int NOT NULL DEFAULT '999',
					  PRIMARY KEY (`object_id`, `object_type`, `image_id`),
					  KEY `FK_IMAGE_ID` (`image_id`),
					  CONSTRAINT `object_images_ibfk_1` FOREIGN KEY (`image_id`) REFERENCES `storage_files` (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1;
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Creating object_images table: ".$database->error());
					return false;
				}

				$this->setVersion(25);
				$database->CommitTrans();
			}

			if ($this->version() < 26) {
				app_log("Upgrading ".$this->module." schema to version 26",'notice',__FILE__,__LINE__);
				
				$table = new \Database\Schema\Table('object_images');
				if ($table->has_constraint("object_images_ibfk_2")) {
					$drop_constraint_query = "
						ALTER TABLE `object_images` 
						DROP FOREIGN KEY `object_images_ibfk_2`
					";
					if (! $database->Execute($drop_constraint_query)) {
						$this->SQLError("Dropping old foreign key constraint from object_images table: ".$database->error());
						return false;
					}
				}

				$this->setVersion(26);
				$database->CommitTrans();
			}

			return true;
		}
	}
