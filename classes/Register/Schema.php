<?php
	namespace Register;

	class Schema {
		public $error;
		public $errno;
		public $module = "register";

		public function __construct() {
			$this->upgrade();
		}
		
		public function version() {
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();
			$info_table = "register__info";

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
					$this->error = "SQL Error creating info table in RegisterSchema::__construct: ".$GLOBALS['_database']->ErrorMsg();
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
				$this->error = "SQL Error in RegisterInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
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
					CREATE TABLE IF NOT EXISTS `register_organizations` (
						`id`			int(11) NOT NULL AUTO_INCREMENT,
						`name`			varchar(255) NOT NULL,
						`code`			varchar(100) NOT NULL,
						`date_created`	date,
						PRIMARY KEY (`id`),
						UNIQUE KEY `UK_CODE` (`code`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating organizations table in RegisterInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `register_departments` (
						`id`			int(11) NOT NULL AUTO_INCREMENT,
						`name`			varchar(255) NOT NULL,
						`description`	text,
						`manager_id`	int(11),
						`parent_id`		int(11),
						PRIMARY KEY (`id`),
						UNIQUE KEY `UK_CODE` (`name`),
						INDEX `IDX_PARENT` (`parent_id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating departments table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `register_users` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`status`	enum('NEW','ACTIVE','HIDDEN','DELETED') NOT NULL DEFAULT 'ACTIVE',
						`last_name` varchar(100) DEFAULT NULL,
						`middle_name` varchar(100) DEFAULT NULL,
						`first_name` varchar(100) DEFAULT NULL,
						`login` varchar(45) NOT NULL,
						`password` varchar(64) NOT NULL DEFAULT '',
						`title` varchar(100) DEFAULT '',
						`department_id` int(11) NOT NULL DEFAULT '0',
						`organization_id` int(11) DEFAULT '0',
						`opt_in` boolean NOT NULL DEFAULT '0',
						`date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
						`date_updated` timestamp NOT NULL,
						`date_expires` datetime NOT NULL,
						`auth_method` varchar(100) DEFAULT 'local',
						`unsubscribe_key` varchar(50) NOT NULL DEFAULT '',
						`validation_key` varchar(45) DEFAULT NULL,
						`custom_metadata` text,
						PRIMARY KEY (`id`),
						UNIQUE KEY `uk_login` (`login`),
						KEY `idx_organization` (`organization_id`),
						KEY `idx_unsubscribe_key` (`unsubscribe_key`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					app_log("SQL Error creating users table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg(),'error',__FILE__,__LINE__);
					$this->error = "Error creating register_users table";
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `register_contacts` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`person_id` int(11) NOT NULL,
						`type` enum('phone','email','sms','facebook','twitter') NOT NULL,
						`description` varchar(100),
						`notify` tinyint(1) NOT NULL default 0,
						`value` varchar(255) NOT NULL,
						`notes` varchar(255) DEFAULT NULL,
						PRIMARY KEY (`id`),
						KEY `fk_person_id` (`person_id`),
						KEY `fk_type` (`type`),
						CONSTRAINT `register_contact_listing_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `register_users` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating register_contacts table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `register_roles` (
						`id` int(10) NOT NULL AUTO_INCREMENT,
						`name` varchar(45) NOT NULL,
						`description` varchar(255) NOT NULL DEFAULT '',
						PRIMARY KEY (`id`),
						UNIQUE KEY `uk_name` (`name`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating register_roles table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `register_users_roles` (
						`user_id` int(11) NOT NULL AUTO_INCREMENT,
						`role_id` int(10) NOT NULL,
						PRIMARY KEY (`user_id`,`role_id`),
						FOREIGN KEY `register_users_roles_ibfk_1` (`user_id`) REFERENCES `register_users` (`id`),
						FOREIGN KEY `register_users_roles_ibfk_2` (`role_id`) REFERENCES `register_roles` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating register_users_roles table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$add_roles_query = "
					INSERT
					INTO	register_roles
					VALUES	(null,'register manager','Can view/edit customers and organizations'),
							(null,'register reporter','Can view customers and organizations')
				";
				$GLOBALS['_database']->Execute($add_roles_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error adding register roles in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 1;
				$update_schema_version = "
					INSERT
					INTO	register__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					app_log("SQL Error in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg(),'error',__FILE__,__LINE__);
					$this->error = "Error adding roles to database";
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			if ($current_schema_version < 0) {
				app_log("Upgrading schema to version 2",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `register_organization_products` (
						`organization_id`	int(11) NOT NULL,
						`product_id`		int(11) NOT NULL,
						`quantity`			decimal(9,2) NOT NULL,
						`date_expires`		datetime DEFAULT '9999-12-31 23:59:59',
						PRIMARY KEY `pk_organization_product` (`organization_id`,`product_id`),
						FOREIGN KEY `fk_organization` (`organization_id`) REFERENCES `register_organizations` (`id`),
						FOREIGN KEY `fk_product` (`product_id`) REFERENCES `product_products` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating organizations table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 2;
				$update_schema_version = "
					INSERT
					INTO	register__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					app_log("SQL Error in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg(),'error',__FILE__,__LINE__);
					$this->error = "Error adding roles to database";
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			if ($current_schema_version < 3) {
				app_log("Upgrading schema to version 3",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE `register_person_metadata` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`person_id` int(11) NOT NULL,
						`key` varchar(32) NOT NULL,
						`value` text,
						PRIMARY KEY (`id`),
						UNIQUE KEY `person_id` (`person_id`,`key`),
						CONSTRAINT `person_metadata_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `register_users` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating organizations table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 3;
				$update_schema_version = "
					INSERT
					INTO	register__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					app_log("SQL Error in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg(),'error',__FILE__,__LINE__);
					$this->error = "Error adding roles to database";
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			if ($current_schema_version < 4) {
				app_log("Upgrading schema to version 4",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					ALTER TABLE register_users ADD timezone varchar(32) NOT NULL DEFAULT 'America/New_York'
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating organizations table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 4;
				$update_schema_version = "
					INSERT
					INTO	register__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					app_log("SQL Error in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg(),'error',__FILE__,__LINE__);
					$this->error = "Error adding roles to database";
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			if ($current_schema_version < 5) {
				app_log("Upgrading schema to version 5",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `register_relations` (
						`parent_id` int(11) NOT NULL,
						`person_id` int(11) NOT NULL,
						PRIMARY KEY (`parent_id`,`person_id`),
						FOREIGN KEY `fk_parent_id` (`parent_id`) REFERENCES `register_users` (`id`),
						FOREIGN KEY `fk_person_id` (`person_id`) REFERENCES `register_users` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating register relations table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 5;
				$update_schema_version = "
					INSERT
					INTO	register__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					app_log("SQL Error in RegisterInit::schema_manager: ".$GLOBALS['_database']->ErrorMsg(),'error',__FILE__,__LINE__);
					$this->error = "Error adding roles to database";
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			if ($current_schema_version < 6) {
				app_log("Upgrading schema to version 6",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `register_password_tokens` (
						`person_id` int(11) NOT NULL,
						`code`		varchar(255) NOT NULL,
						`date_expires`	datetime DEFAULT '1990-01-01 00:00:00',
						`client_ip`		varchar(32),
						PRIMARY KEY (`person_id`),
						FOREIGN KEY `fk_person_id` (`person_id`) REFERENCES `register_users` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating register relations table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 6;
				$update_schema_version = "
					INSERT
					INTO	register__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					app_log("SQL Error in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg(),'error',__FILE__,__LINE__);
					$this->error = "Error adding roles to database";
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			if ($current_schema_version < 7) {
				app_log("Upgrading schema to version 7",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$alter_table_query = "
					ALTER TABLE `register_users` MODIFY COLUMN `status` enum('NEW','ACTIVE','EXPIRED','HIDDEN','DELETED') NOT NULL DEFAULT 'ACTIVE'
				";
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_users table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 7;
				$update_schema_version = "
					INSERT
					INTO	register__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					app_log("SQL Error in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg(),'error',__FILE__,__LINE__);
					$this->error = "Error adding roles to database";
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			if ($current_schema_version < 8) {
				app_log("Upgrading schema to version 8",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$alter_table_query = "
					ALTER TABLE `register_organizations` ADD COLUMN `status` enum('NEW','ACTIVE','EXPIRED','HIDDEN','DELETED') NOT NULL DEFAULT 'ACTIVE'
				";
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_organizations table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 8;
				$update_schema_version = "
					INSERT
					INTO	register__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					app_log("SQL Error in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg(),'error',__FILE__,__LINE__);
					$this->error = "Error adding roles to database";
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			if ($current_schema_version < 9) {
				app_log("Upgrading schema to version 9",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$alter_table_query = "
					ALTER TABLE `register_organizations` ADD COLUMN `notes` text
				";
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_organizations table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$alter_table_query = "
					ALTER TABLE `register_users`
					ADD COLUMN `notes` text
				";
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_users table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 9;
				$update_schema_version = "
					INSERT
					INTO	register__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					app_log("SQL Error in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg(),'error',__FILE__,__LINE__);
					$this->error = "Error adding roles to database";
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			if ($current_schema_version < 10) {
				app_log("Upgrading schema to version 10",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$alter_table_query = "
					ALTER TABLE `register_organizations` ADD COLUMN `is_reseller` int(1) DEFAULT 0
				";
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_organizations table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$alter_table_query = "
					ALTER TABLE `register_organizations` ADD COLUMN `assigned_reseller_id` int(11) DEFAULT 0
				";
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_users table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 10;
				$update_schema_version = "
					INSERT
					INTO	register__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			if ($current_schema_version < 11) {
				app_log("Upgrading schema to version 11",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `register_role_privileges` (
						`id`			int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
						`role_id` 		int(11) NOT NULL,
						`privilege`		varchar(255) NOT NULL,
						INDEX `idx_role_id` (`role_id`),
						UNIQUE KEY `uk_privilege` (`privilege`),
						FOREIGN KEY `fk_role_id` (`role_id`) REFERENCES `register_roles` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating role privileges table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 11;
				$update_schema_version = "
					INSERT
					INTO	register__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					app_log("SQL Error in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg(),'error',__FILE__,__LINE__);
					$this->error = "Error adding roles to database";
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
		}
	}
?>
