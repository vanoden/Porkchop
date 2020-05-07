<?php
	namespace Issue;

	class Schema {
		public $error;
		public $errno;
		private $_info_table = "issue__info";

		public function __construct() {
			$this->upgrade();
		}

		public function version() {
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();

			if (! in_array($this->_info_table,$schema_list)) {
				# Create __info table
				$create_table_query = "
					CREATE TABLE `".$this->_info_table."` (
						label	varchar(100) not null primary key,
						value	varchar(255)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating info table in Issue::Schema::version: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
			}

			# Check Current Schema Version
			$get_version_query = "
				SELECT	value
				FROM	`".$this->_info_table."`
				WHERE	label = 'schema_version'
			";

			$rs = $GLOBALS['_database']->Execute($get_version_query);
			if (! $rs) {
				$this->error = "SQL Error in Issue::Schema::version: ".$GLOBALS['_database']->ErrorMsg();
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
					CREATE TABLE IF NOT EXISTS `issue_products` (
						`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
						`code` varchar(45) NOT NULL,
						`name` varchar(255) NOT NULL,
						`description` TEXT,
						`status` enum('ACTIVE','DEPRECATED','UNSUPPORTED'),
						`owner_id`		int(11) NOT NULL,
						UNIQUE KEY `uk_product_code` (`code`),
						FOREIGN KEY `fk_owner_id` (`owner_id`) REFERENCES `register_users` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating products table in Issue::Schema::__construct(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `issue_issues` (
						`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
						`code` varchar(45) NOT NULL,
						`product_id` int(11) DEFAULT NULL,
						`title` varchar(255) NOT NULL,
						`status` enum('NEW','HOLD','OPEN','CANCELLED','COMPLETE','REOPENED','APPROVED'),
						`description` TEXT,
						`date_reported` datetime,
						`user_reported_id` int(11) NOT NULL,
						`date_assigned` datetime,
						`user_assigned_id` int(11),
						`date_completed` datetime,
						`date_approved` datetime,
						`user_approved_id` int(11),
						`priority` enum('NORMAL','IMPORTANT','CRITICAL','EMERGENCY') NOT NULL DEFAULT 'NORMAL',
						`internal` int(1) NOT NULL DEFAULT 0,
						UNIQUE KEY `idx_code` (`code`),
						FOREIGN KEY `fk_product_idx` (`product_id`) REFERENCES `issue_products` (`id`),
						FOREIGN KEY `fk_user_reported_idx` (`user_reported_id`) REFERENCES `register_users` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating issues table in Issue::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `issue_events` (
						`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
						`issue_id` int(11) NOT NULL,
						`person_id` int(11) NOT NULL,
						`date_event` datetime NOT NULL,
						`description` TEXT,
						`status_previous` enum('NEW','HOLD','OPEN','CANCELLED','COMPLETE','APPROVED'),
						`status_new` enum('NEW','HOLD','ACTIVE','CANCELLED','COMPLETE','APPROVED'),
						`internal` int(1) NOT NULL DEFAULT 0,
						FOREIGN KEY `fk_issue_idx` (`issue_id`) REFERENCES `issue_issues` (`id`),
						FOREIGN KEY `fk_person_idx` (`person_id`) REFERENCES `register_users` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating events table in Issue::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$create_table_query = "
					CREATE TABLE `issue_product_metadata` (
						`id`			int(12) NOT NULL AUTO_INCREMENT,
						`product_id`	int(11) NOT NULL,
						`label` 		varchar(100) NOT NULL,
						`value`			varchar(255) NOT NULL,
						PRIMARY KEY (`id`),
						UNIQUE KEY `uk_product_label` (`product_id`,`label`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating metadata table in Issue::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				# Add Roles
				$role = new \Register\Role();
				$role->add(
					array("name" => "issue admin")
				);

				$current_schema_version = 1;
				$update_schema_version = "
					INSERT
					INTO	`".$this->_info_table."`
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Issue::Schema::version(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}
				$GLOBALS['_database']->CommitTrans();
			}
		}
	}
