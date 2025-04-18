<?php
	namespace Register;
	
	class Schema Extends \Database\BaseSchema {

		public $module = "register";
		
		public $error;

		public function upgrade() {

			$this->error = null;

			if ($this->version() < 1) {
				app_log("Upgrading schema to version 1", 'notice', __FILE__, __LINE__);
				
				# Start Transaction
				if (!$GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported", 'warning', __FILE__, __LINE__);
				
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
					
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating organizations table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}
				
				$create_table_query = "
						CREATE TABLE IF NOT EXISTS `register_departments` (
							`id`			int(11) NOT NULL AUTO_INCREMENT,
							`name`			varchar(150) NOT NULL,
							`description`	text,
							`manager_id`	int(11),
							`parent_id`		int(11),
							PRIMARY KEY (`id`),
							UNIQUE KEY `UK_CODE` (`name`),
							INDEX `IDX_PARENT` (`parent_id`)
						)
					";
					
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating register_departments table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
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
					
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating register_users table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
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
					
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating register_contacts table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
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
					
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating register_roles table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
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
					
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating register_users_roles table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}
				
				$this->setVersion(1);
				$GLOBALS['_database']->CommitTrans();
			}
			
			if ($this->version() < 2) {
				app_log("Upgrading schema to version 2", 'notice', __FILE__, __LINE__);

				# Start Transaction 
				if (!$GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported", 'warning', __FILE__, __LINE__);

				# Requires Product Schema
				$product_schema = new \Product\Schema;
				if (! $product_schema->upgrade(1)) {
					$this->error = "Error upgrading product schema: ".$product_schema->error();
					return false;
				}
				$create_table_query = "
						CREATE TABLE IF NOT EXISTS `register_organization_products` (
							`organization_id`	int(11) NOT NULL,
							`product_id`		int(11) NOT NULL,
							`quantity`			decimal(9,2) NOT NULL,
							`date_expires`		datetime DEFAULT '9999-12-31 23:59:59',
							PRIMARY KEY `pk_organization_product` (`organization_id`,`product_id`),
							FOREIGN KEY `fk_orgproduct_organization` (`organization_id`) REFERENCES `register_organizations` (`id`),
							FOREIGN KEY `fk_orgproduct_product` (`product_id`) REFERENCES `product_products` (`id`)
						)
					";
					
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating register_organization_products table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(2);
				$GLOBALS['_database']->CommitTrans();
			}
			
			if ($this->version() < 3) {
				app_log("Upgrading schema to version 3", 'notice', __FILE__, __LINE__);
				
				# Start Transaction
				if (!$GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported", 'warning', __FILE__, __LINE__);
				
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
					$this->error = "SQL Error creating organizations table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					app_log($this->error, 'error', __FILE__, __LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$this->setVersion(3);
				$GLOBALS['_database']->CommitTrans();
			}
			
			if ($this->version() < 4) {
				app_log("Upgrading schema to version 4", 'notice', __FILE__, __LINE__);
				
				# Start Transaction
				if (!$GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported", 'warning', __FILE__, __LINE__);
				
				$create_table_query = "
						ALTER TABLE register_users ADD timezone varchar(32) NOT NULL DEFAULT 'America/New_York'
					";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating organizations table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					app_log($this->error, 'error', __FILE__, __LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$this->setVersion(4);
				$GLOBALS['_database']->CommitTrans();
			}
			
			if ($this->version() < 5) {
				app_log("Upgrading schema to version 5", 'notice', __FILE__, __LINE__);
				
				# Start Transaction
				if (!$GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported", 'warning', __FILE__, __LINE__);
				
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
					$this->error = "SQL Error creating register relations table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					app_log($this->error, 'error', __FILE__, __LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$this->setVersion(5);
				$GLOBALS['_database']->CommitTrans();
			}
			
			if ($this->version() < 6) {
				app_log("Upgrading schema to version 6", 'notice', __FILE__, __LINE__);
				
				# Start Transaction
				if (!$GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported", 'warning', __FILE__, __LINE__);
				
				$create_table_query = "
						CREATE TABLE IF NOT EXISTS `register_password_tokens` (
							`person_id` int(11) NOT NULL,
							`code`		varchar(255) NOT NULL,
							`date_expires`	datetime DEFAULT '1990-01-01 00:00:00',
							`client_ip`		varchar(32),
							PRIMARY KEY (`person_id`),
							FOREIGN KEY `fk_token_person_id` (`person_id`) REFERENCES `register_users` (`id`)
						)
					";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating register relations table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					app_log($this->error, 'error', __FILE__, __LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$this->setVersion(6);
				$GLOBALS['_database']->CommitTrans();
			}
			
			if ($this->version() < 7) {
				app_log("Upgrading schema to version 7", 'notice', __FILE__, __LINE__);
				
				# Start Transaction
				if (!$GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported", 'warning', __FILE__, __LINE__);
				
				$alter_table_query = "
						ALTER TABLE `register_users` MODIFY COLUMN `status` enum('NEW','ACTIVE','EXPIRED','HIDDEN','DELETED') NOT NULL DEFAULT 'ACTIVE'
					";
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_users table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					app_log($this->error, 'error', __FILE__, __LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$this->setVersion(7);
				$GLOBALS['_database']->CommitTrans();
			}
			
			if ($this->version() < 8) {
				app_log("Upgrading schema to version 8", 'notice', __FILE__, __LINE__);
				
				# Start Transaction
				if (!$GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported", 'warning', __FILE__, __LINE__);
				
				$alter_table_query = "
						ALTER TABLE `register_organizations` ADD COLUMN `status` enum('NEW','ACTIVE','EXPIRED','HIDDEN','DELETED') NOT NULL DEFAULT 'ACTIVE'
					";
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_organizations table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					app_log($this->error, 'error', __FILE__, __LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$this->setVersion(8);
				$GLOBALS['_database']->CommitTrans();
			}
			
			if ($this->version() < 9) {
				app_log("Upgrading schema to version 9", 'notice', __FILE__, __LINE__);
				
				# Start Transaction
				if (!$GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported", 'warning', __FILE__, __LINE__);
				
				$alter_table_query = "
						ALTER TABLE `register_organizations` ADD COLUMN `notes` text
					";
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_organizations table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					app_log($this->error, 'error', __FILE__, __LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				
				$alter_table_query = "
						ALTER TABLE `register_users`
						ADD COLUMN `notes` text
					";
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_users table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					app_log($this->error, 'error', __FILE__, __LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$this->setVersion(9);
				$GLOBALS['_database']->CommitTrans();
			}
			
			if ($this->version() < 10) {
				app_log("Upgrading schema to version 10", 'notice', __FILE__, __LINE__);
				
				# Start Transaction
				if (!$GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported", 'warning', __FILE__, __LINE__);
				
				$alter_table_query = "
						ALTER TABLE `register_organizations` ADD COLUMN `is_reseller` int(1) DEFAULT 0
					";
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_organizations table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					app_log($this->error, 'error', __FILE__, __LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				
				$alter_table_query = "
						ALTER TABLE `register_organizations` ADD COLUMN `assigned_reseller_id` int(11) DEFAULT 0
					";
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_users table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					app_log($this->error, 'error', __FILE__, __LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$this->setVersion(10);
				$GLOBALS['_database']->CommitTrans();
			}
			
			if ($this->version() < 11) {
				app_log("Upgrading schema to version 11", 'notice', __FILE__, __LINE__);
				
				# Start Transaction
				if (!$GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported", 'warning', __FILE__, __LINE__);
				
				$create_table_query = "
						CREATE TABLE IF NOT EXISTS `register_role_privileges` (
							`id`			int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
							`role_id` 		int(11) NOT NULL,
							`privilege`		varchar(150) NOT NULL,
							INDEX `idx_role_id` (`role_id`),
							UNIQUE KEY `uk_privilege` (`privilege`),
							FOREIGN KEY `fk_role_id` (`role_id`) REFERENCES `register_roles` (`id`)
						)
					";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating role privileges table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$this->setVersion(11);
				$GLOBALS['_database']->CommitTrans();
			}
			
			if ($this->version() < 12) {
				app_log("Upgrading schema to version 12", 'notice', __FILE__, __LINE__);
				
				# Start Transaction
				if (!$GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported", 'warning', __FILE__, __LINE__);
				
				$create_table_query = "
						CREATE TABLE `register_queue` (
							`id` int(11) NOT NULL AUTO_INCREMENT,
							`name` varchar(255) NOT NULL,
							`address` varchar(200) NOT NULL DEFAULT '',
							`city` varchar(200) NOT NULL DEFAULT '',
							`state` varchar(200) NOT NULL DEFAULT '',
							`zip` varchar(200) NOT NULL DEFAULT '',
							`phone` varchar(200) NOT NULL DEFAULT '',
							`cell` varchar(200) NOT NULL DEFAULT '',
							`code` varchar(100) NOT NULL,
							`status` enum('NEW','ACTIVE','EXPIRED','HIDDEN','DELETED') NOT NULL DEFAULT 'NEW',
							`date_created` datetime NULL,
							`is_reseller` int(1) DEFAULT '0',
							`assigned_reseller_id` int(11) DEFAULT '0',
							`notes` text,
							`product_id` int(11) DEFAULT NULL,
							`serial_number` varchar(255) DEFAULT NULL,
							PRIMARY KEY (`id`),
							UNIQUE KEY `UK_CODE` (`code`)
						)
					";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating register queue table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				
				$alter_table_query = "
						ALTER TABLE `register_contacts` MODIFY `description` varchar(100) DEFAULT NULL
					";
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_contacts table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					app_log($this->error, 'error', __FILE__, __LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$this->setVersion(12);
				$GLOBALS['_database']->CommitTrans();
			}
			
			if ($this->version() < 13) {
	
				app_log("Upgrading schema to version 13",'notice',__FILE__,__LINE__);
	
				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);
			
				$alter_table_query = "
					ALTER TABLE register_queue ADD COLUMN register_user_id int(11) AFTER serial_number;
				";
	
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_contacts table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
						app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
					
				$alter_table_query = "
					ALTER TABLE `register_queue` CHANGE `status` `status` ENUM(\"VERIFYING\",\"PENDING\",\"APPROVED\",\"DENIED\") DEFAULT \"VERIFYING\";
				";
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_contacts table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$this->setVersion(13);
				$GLOBALS['_database']->CommitTrans();
			}
				
			if ($this->version() < 14) {
				app_log("Upgrading schema to version 14",'notice',__FILE__,__LINE__);
				   # Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);
			
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS register_privileges (
						   id int(11) NOT NULL AUTO_INCREMENT,
						   name    varchar(100) NOT NULL,
						   description text,
						   PRIMARY KEY `pk_register_privileges` (`id`),
						   UNIQUE KEY `uk_privilege_name` (`name`)
					   )
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating register_privileges table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
					
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS register_roles_privileges (
						role_id int(11) NOT NULL,
						privilege_id int(11) NOT NULL,
						PRIMARY KEY `pk_role_privilege` (`role_id`,`privilege_id`),
						FOREIGN KEY `fk_roles_id` (`role_id`) REFERENCES `register_roles` (`id`),
						FOREIGN KEY `fk_privilege_id` (`privilege_id`) REFERENCES `register_privileges` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_contacts table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
					
				$fill_table_query = "
					INSERT INTO register_privileges SELECT null,privilege,'' FROM register_role_privileges
				";
				$GLOBALS['_database']->Execute($fill_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error copying register_privileges from register_role_privileges in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
					
				$move_table_query = "
					INSERT INTO register_roles_privileges SELECT rrp.role_id,(SELECT rp.id from register_privileges rp WHERE rp.name = rrp.privilege) FROM register_role_privileges rrp;
				";
				$GLOBALS['_database']->Execute($move_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error building register_roles_privileges in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
	
				$drop_table_query = "
					DROP TABLE register_role_privileges
				";
				$GLOBALS['_database']->Execute($drop_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error dropping register_role_privileges in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$this->setVersion(14);
				$GLOBALS['_database']->CommitTrans();
			}
				
			if ($this->version() < 15) {
				app_log("Upgrading schema to version 15",'notice',__FILE__,__LINE__);
					
				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$drop_table_query = "
					DROP TABLE IF EXISTS register_organization_locations
				";
				$GLOBALS['_database']->Execute($drop_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error dropping register_organization_locations table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$drop_table_query = "
					DROP TABLE IF EXISTS builtin_locations
				";
				$GLOBALS['_database']->Execute($drop_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error dropping builtin_locations table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$drop_table_query = "
					DROP TABLE IF EXISTS builtin__info
				";
				$GLOBALS['_database']->Execute($drop_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error dropping builtin__info table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$geography_schema = new \Geography\Schema;
				if (! $geography_schema->upgrade()) {
					$this->error = "Error upgrading Geography Schema: ".$geography_schema->error();
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS register_locations (
						id INT(11) NOT NULL AUTO_INCREMENT,
						name varchar(255) NOT NULL,
						address_1 varchar(255),
						address_2 varchar(255),
						city varchar(255) NOT NULL,
						region_id INT(11) NOT NULL,
						country_id INT(4) NOT NULL,
						zip_code INT(10) NOT NULL,
						notes TEXT,
						PRIMARY KEY `pk_id` (`id`),
						FOREIGN KEY `fk_country_id` (`country_id`) REFERENCES `geography_countries` (`id`),
						FOREIGN KEY `fk_region_id` (`region_id`) REFERENCES `geography_provinces` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating register_locations table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS register_organization_locations (
						organization_id INT(11) NOT NULL,
						location_id INT(11) NOT NULL,
						PRIMARY KEY `pk_org_loc` (`organization_id`,`location_id`),
						FOREIGN KEY `fk_org_id` (`organization_id`) REFERENCES `register_locations` (`id`),
						FOREIGN KEY `fk_loc_id` (`location_id`) REFERENCES `register_locations` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating register_organization_locations table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
	
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS register_user_locations (
						user_id INT(11) NOT NULL,
						location_id INT(11) NOT NULL,
						PRIMARY KEY `pk_user_loc` (`user_id`,`location_id`),
						FOREIGN KEY `fk_loc_user_id` (`user_id`) REFERENCES `register_users` (`id`),
						FOREIGN KEY `fk_reg_loc_id` (`location_id`) REFERENCES `register_locations` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating register_user_locations table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$this->setVersion(15);
				$GLOBALS['_database']->CommitTrans();
			}
				
			if ($this->version() < 16) {
				app_log("Upgrading schema to version 16",'notice',__FILE__,__LINE__);
					
				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$table = new \Database\Schema\Table('register_locations');
				if (! $table->disable_keys()) {
					$this->error = $table->error();
					return false;
				}

				$constraints = $table->constraints();
				if ($table->error()) {
					$this->error = $table->error();
					app_log($table->error(),'error');
					return false;
				}
				foreach ($constraints as $constraint) {
					if ($constraint->type != 'PRIMARY KEY') {
						app_log("Dropping ".$constraint->type." ".$constraint->name." from ".$constraint->table);
						if (!$constraint->drop()) {
							$this->error = "Error dropping constraint '".$constraint->name."': ".$constraint->error();
							return false;
						}
					}
				}
				if ($table->has_column('region_id')) {
					if (! $this->executeSQL("ALTER TABLE register_locations CHANGE COLUMN region_id province_id INT(11) NOT NULL")) {
						$this->error = "SQL Error altering from register_locations table in ".$this->module."::Schema::upgrade(): ".$this->error;
						app_log($this->error, 'error');
						return false;
					}
				}
				if (! $table->has_constraint('fk_province_id')) {
					if (! $this->executeSQL("ALTER TABLE register_locations ADD FOREIGN KEY `fk_province_id` (`province_id`) REFERENCES `geography_provinces` (`id`)")) {
						$this->error = "SQL Error adding key to register_locations table in ".$this->module."::Schema::upgrade(): ".$this->error;
						app_log($this->error, 'error');
						return false;
					}
				}
				if (! $table->enable_keys()) {
					$this->error = $table->error();
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(16);
				$GLOBALS['_database']->CommitTrans();
			}

			if ($this->version() < 17) {
	
				app_log("Upgrading schema to version 17",'notice',__FILE__,__LINE__);
	
				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);
			
				$alter_table_query = "
					ALTER TABLE register_locations MODIFY COLUMN zip_code varchar(12) NOT NULL
				";
	
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_locations table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
						app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$this->setVersion(17);
				$GLOBALS['_database']->CommitTrans();
			}
				
			if ($this->version() < 18) {
				app_log("Upgrading schema to version 18",'notice',__FILE__,__LINE__);
					
				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$table = new \Database\Schema\Table('register_organization_locations');
				if (! $table->disable_keys()) {
					$this->error = $table->error();
					return false;
				}

				$constraints = $table->constraints();
				if ($table->error()) {
					$this->error = $table->error();
					app_log($table->error(),'error');
					return false;
				}
				foreach ($constraints as $constraint) {
					if ($constraint->type == 'FOREIGN KEY') {
						app_log("Dropping ".$constraint->type." ".$constraint->name." from ".$constraint->table);
						if (!$constraint->drop()) {
							$this->error = "Error dropping constraint '".$constraint->name."': ".$constraint->error();
							return false;
						}
					}
				}
				if (! $this->executeSQL("ALTER TABLE register_organization_locations ADD FOREIGN KEY `register_organization_locations_organization_id` (`organization_id`) REFERENCES `register_organizations` (`id`)")) {
					$this->error = "SQL Error adding key to register_organization_locations table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}
				if (! $this->executeSQL("ALTER TABLE register_organization_locations ADD FOREIGN KEY `register_organization_locations_location_id` (`location_id`) REFERENCES `register_locations` (`id`)")) {
					$this->error = "SQL Error adding key to register_organization_locations table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				if (! $table->enable_keys()) {
					$this->error = $table->error();
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(18);
				$GLOBALS['_database']->CommitTrans();
			}

			if ($this->version() < 19) {
	
				app_log("Upgrading schema to version 19",'notice',__FILE__,__LINE__);
	
				# Start Transaction 
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);
			
				$alter_table_query = "
					ALTER TABLE register_users ADD COLUMN `automation` int(1) NOT NULL DEFAULT 0
				";
	
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_users table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
						app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$this->setVersion(19);
				$GLOBALS['_database']->CommitTrans();
			}
			if ($this->version() < 20) {
	
				app_log("Upgrading schema to version 20",'notice',__FILE__,__LINE__);
	
				# Start Transaction 
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);
			
				$alter_table_query = "
					ALTER TABLE register_privileges ADD COLUMN `module` varchar(255) NOT NULL DEFAULT 'Unspecified',
					ADD INDEX `idx_privilege_module` (`module`)
				";
	
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_privileges table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
						app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$this->setVersion(20);
				$GLOBALS['_database']->CommitTrans();
			}
			if ($this->version() < 21) {
				app_log("Upgrading schema to version 21",'notice',__FILE__,__LINE__);

				# Start Transaction 
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);
			
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS register_user_metadata (
						`user_id`	INT(11) NOT NULL,
						`key`		varchar(255) NOT NULL,
						`value`		varchar(255) NOT NULL,
						PRIMARY KEY `pk_user_meta` (`user_id`,`key`),
						FOREIGN KEY `fk_usermeta_userid` (`user_id`) REFERENCES `register_users` (`id`)
					)
				";
	
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating register_user_metadata table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
						app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$this->setVersion(21);
				$GLOBALS['_database']->CommitTrans();
			}
            if ($this->version() < 22) {
				app_log("Upgrading schema to version 22",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$table = new \Database\Schema\Table('register_organizations');
				if (! $table->disable_keys()) {
					$this->error = $table->error();
					return false;
				}

				if (! $table->has_column('password_expiration_days')) {
					$alter_table_query = "ALTER TABLE `register_organizations` ADD `password_expiration_days` int NULL";
					if (! $this->executeSQL($alter_table_query)) {
						$this->error = "SQL Error altering `register_organizations` table in ".$this->module."::Schema::upgrade(): ".$this->error;
						app_log($this->error, 'error');
						return false;
					}
				}

				$alter_table_query = "ALTER TABLE `register_users` ADD COLUMN `password_age` DATETIME DEFAULT CURRENT_TIMESTAMP;";
				if (! $this->executeSQL($alter_table_query)) {
					$this->error = "SQL Error altering `register_users` table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(22);
				$GLOBALS['_database']->CommitTrans();
			}
            if ($this->version() < 23) {
				app_log("Upgrading schema to version 23",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$alter_table_query = "
					ALTER TABLE `register_users`
						MODIFY COLUMN `status` enum('NEW','ACTIVE','EXPIRED','HIDDEN','DELETED','BLOCKED') NOT NULL DEFAULT 'ACTIVE',
						ADD COLUMN `auth_failures` INT(2) DEFAULT 0
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->error = "SQL Error altering `register_organizations` table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(23);
				$GLOBALS['_database']->CommitTrans();
			}
			
			if ($this->version() < 25) {
				app_log("Upgrading schema to version 25",'notice',__FILE__,__LINE__);

				// Start Transaction 
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);
			
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS register_auth_failures (
						`id`			INT(11) NOT NULL AUTO_INCREMENT,
						`ip_address`	INT(11) NOT NULL,
						`login`			varchar(255) NOT NULL,
						`date_fail`		timestamp,
						`reason`		enum('NOACCOUNT','PASSEXPIRED','WRONGPASS','INACTIVE','INVALIDPASS') NOT NULL,
						`endpoint`		varchar(255),
						PRIMARY KEY `pk_reg_auth_fail` (`id`),
						INDEX `idx_reg_auth_fail_ip_login` (`ip_address`,`login`),
						INDEX `idx_reg_auth_fail_ip_last` (`ip_address`,`date_fail`),
						INDEX `idx_reg_auth_fail_login` (`login`,`date_fail`)
					)
				";
	
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating register_auth_failures table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
						app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$this->setVersion(25);
				$GLOBALS['_database']->CommitTrans();
			}

			if ($this->version() < 26) {
				app_log("Upgrading schema to version 26",'notice',__FILE__,__LINE__);

				// Start Transaction 
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);
			
				$create_table_query = "
						CREATE TABLE if not exists `register_tags` (
						  `id` int NOT NULL AUTO_INCREMENT,
						  `type` enum('ORGANIZATION','USER','CONTACT','LOCATION') NOT NULL DEFAULT 'ORGANIZATION',
						  `register_id` int DEFAULT NULL,
						  `name` varchar(255) NOT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=InnoDB DEFAULT CHARSET=latin1;
				";
	
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating register_tags table in Register::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
						app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$this->setVersion(26);
				$GLOBALS['_database']->CommitTrans();
				
				# add new calbrator role
				$this->addRoles(array('calibrator' => 'can calibrate customer devices'));			
			}

            if ($this->version() < 27) {
				app_log("Upgrading schema to version 27",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$table = new \Database\Schema\Table('register_organizations');
				if (! $table->has_column('default_billing_location_id')) {
					$alter_table_query = "ALTER TABLE `register_organizations` ADD COLUMN `default_billing_location_id` int NULL";
					if (! $this->executeSQL($alter_table_query)) {
						$this->error = "SQL Error altering `register_organizations` table in ".$this->module."::Schema::upgrade(): ".$this->error;
						app_log($this->error, 'error');
						return false;
					}
				}

				if (! $table->has_column('default_shipping_location_id')) {
					$alter_table_query = "ALTER TABLE `register_organizations` ADD COLUMN `default_shipping_location_id` int NULL;";
					if (! $this->executeSQL($alter_table_query)) {
						$this->error = "SQL Error altering `register_organizations` table in ".$this->module."::Schema::upgrade(): ".$this->error;
						app_log($this->error, 'error');
						return false;
					}
				}

				$this->setVersion(27);
				$GLOBALS['_database']->CommitTrans();
			}
			
            if ($this->version() < 28) {
				app_log("Upgrading schema to version 28", 'notice', __FILE__, __LINE__);
				
				# Start Transaction
				if (!$GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported", 'warning', __FILE__, __LINE__);
				
				$alter_table_query = "
						ALTER TABLE `register_contacts` MODIFY `type` enum('phone','email','sms','facebook', 'insite')
					";

				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_contacts table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					app_log($this->error, 'error', __FILE__, __LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$this->setVersion(28);
				$GLOBALS['_database']->CommitTrans();			
			}

			if ($this->version() < 29) {
				app_log("Upgrading schema to version 29", 'notice', __FILE__, __LINE__);
				
				# Start Transaction
				if (!$GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported", 'warning', __FILE__, __LINE__);
				
				$alter_table_query = "
					ALTER TABLE register_auth_failures
					MODIFY `reason` enum('NOACCOUNT','PASSEXPIRED','WRONGPASS','INACTIVE','INVALIDPASS','CSRFTOKEN','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN'
				";

				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_auth_failures table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					app_log($this->error, 'error', __FILE__, __LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$this->setVersion(29);
				$GLOBALS['_database']->CommitTrans();	
			}

			if ($this->version() < 30) {
				app_log("Upgrading schema to version 30", 'notice', __FILE__, __LINE__);
				
				# Start Transaction
				if (!$GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported", 'warning', __FILE__, __LINE__);
				
				$create_table_query = "
					CREATE TABLE register_user_audit (
						id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
						user_id int(11) NOT NULL,
						admin_id int(11) NOT NULL,
						event_date datetime NOT NULL,
						event_class enum('REGISTRATION_SUBMITTED','REGISTRATION_APPROVED','REGISTRATION_DISCARDED','AUTHENTICATION_SUCCESS','AUTHENTICATION_FAILURE','PASSWORD_CHANGED','PASSWORD_RECOVERY_REQUESTED','ORGANIZATION_CHANGED','ROLE_ADDED','ROLE_REMOVED','STATUS_CHANGED') NOT NULL,
						event_notes varchar(255),
						FOREIGN KEY `fk_register_user_audit_user` (`user_id`) REFERENCES `register_users` (`id`),
						FOREIGN KEY `fk_register_user_audit_by` (`admin_id`) REFERENCES `register_users` (`id`),
						INDEX `idx_register_user_audit_user` (`user_id`),
						INDEX `idx_register_user_audit_by` (`admin_id`),
						INDEX `idx_register_user_audit_date` (`event_date`),
						INDEX `idx_register_user_audit_class` (`event_class`)
					)
				";

				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating register_user_audit table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					app_log($this->error, 'error', __FILE__, __LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$this->setVersion(30);
				$GLOBALS['_database']->CommitTrans();	
			}

			if ($this->version() < 31) {
				app_log("Upgrading schema to version 31", 'notice', __FILE__, __LINE__);
				
				# Start Transaction
				if (!$GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported", 'warning', __FILE__, __LINE__);
				
				$create_table_query = "
                CREATE TABLE `register_organization_audit` (
					`id` int NOT NULL AUTO_INCREMENT,
					`organization_id` int NOT NULL,
					`admin_id` int NOT NULL,
					`event_date` datetime NOT NULL,
					`event_class` enum('ORGANIZATION_CREATED', 'ORGANIZATION_UPDATED', 'STATUS_CHANGED','NAME_CHANGED','RESELLER_CHANGED') NOT NULL,
					`event_notes` varchar(255) DEFAULT NULL,
					PRIMARY KEY (`id`),
					KEY `idx_register_organization_audit_organization` (`organization_id`),
					KEY `idx_register_organization_audit_by` (`admin_id`),
					KEY `idx_register_organization_audit_date` (`event_date`),
					KEY `idx_register_organization_audit_class` (`event_class`),
					CONSTRAINT `register_organization_audit_ibfk_1` FOREIGN KEY (`organization_id`) REFERENCES `register_organizations` (`id`),
					CONSTRAINT `register_organization_audit_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `register_users` (`id`)
				  );
				";

				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating register_user_audit table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					app_log($this->error, 'error', __FILE__, __LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$this->setVersion(31);
				$GLOBALS['_database']->CommitTrans();	
			}

			if ($this->version() < 32) {

				# Add website_url column to register_organizations table
				app_log("Upgrading schema to version 32", 'notice', __FILE__, __LINE__);
				if (!$GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported", 'warning', __FILE__, __LINE__);
				$alter_table_query = "
					ALTER TABLE `register_organizations` ADD COLUMN `website_url` varchar(255) DEFAULT NULL
				";
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_organizations table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					app_log($this->error, 'error', __FILE__, __LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$this->setVersion(32);
				$GLOBALS['_database']->CommitTrans();
			}

			if ($this->version() < 33) {

				# Add last_hit_date column to register_users table
				app_log("Upgrading schema to version 33", 'notice', __FILE__, __LINE__);
				if (!$GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported", 'warning', __FILE__, __LINE__);
				$alter_table_query = "
					ALTER TABLE `register_users` ADD COLUMN `last_hit_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
				";
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_users table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					app_log($this->error, 'error', __FILE__, __LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$this->setVersion(33);
				$GLOBALS['_database']->CommitTrans();
			}

			if ($this->version() < 34) {
				
				# Add event_class enum RESET_KEY_GENERATED to register_user_audit table
				app_log("Upgrading schema to version 34", 'notice', __FILE__, __LINE__);
				if (!$GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported", 'warning', __FILE__, __LINE__);

				$alter_table_query = "ALTER TABLE `register_user_audit` 
										MODIFY COLUMN `event_class` ENUM('REGISTRATION_SUBMITTED','REGISTRATION_APPROVED','REGISTRATION_DISCARDED',
											'AUTHENTICATION_SUCCESS','AUTHENTICATION_FAILURE','PASSWORD_CHANGED','PASSWORD_RECOVERY_REQUESTED',
											'ORGANIZATION_CHANGED','ROLE_ADDED','ROLE_REMOVED','STATUS_CHANGED','RESET_KEY_GENERATED')";

				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_users table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					app_log($this->error, 'error', __FILE__, __LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$this->setVersion(34);
				$GLOBALS['_database']->CommitTrans();
			}

			if ($this->version() < 35) {
				
				# Add event_class enum RESET_KEY_GENERATED to register_user_audit table
				app_log("Upgrading schema to version 35", 'notice', __FILE__, __LINE__);
				if (!$GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported", 'warning', __FILE__, __LINE__);

				$alter_table_query = "ALTER TABLE `register_user_audit` 
										MODIFY COLUMN `event_class` ENUM('REGISTRATION_SUBMITTED','REGISTRATION_APPROVED','REGISTRATION_DISCARDED',
											'AUTHENTICATION_SUCCESS','AUTHENTICATION_FAILURE','PASSWORD_CHANGED','PASSWORD_RECOVERY_REQUESTED',
											'ORGANIZATION_CHANGED','ROLE_ADDED','ROLE_REMOVED','STATUS_CHANGED','RESET_KEY_GENERATED', 'USER_UPDATED')";

				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_users table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					app_log($this->error, 'error', __FILE__, __LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$this->setVersion(35);
				$GLOBALS['_database']->CommitTrans();
			}

			if ($this->version() < 36) {
				app_log("Upgrading schema to version 36", 'notice', __FILE__, __LINE__);
				
				# Start Transaction 
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);
				
				$alter_table_query = "
					ALTER TABLE `register_users` ADD COLUMN `time_based_password` int(1) NOT NULL DEFAULT 0
				";

				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_users table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					app_log($this->error, 'error', __FILE__, __LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				
				$this->setVersion(36);
				$GLOBALS['_database']->CommitTrans();
			}

			# add public column to register_contacts table
			if ($this->version() < 37) {
				app_log("Upgrading schema to version 37", 'notice', __FILE__, __LINE__);
				$alter_table_query = "
					ALTER TABLE `register_contacts` ADD COLUMN `public` tinyint(1) NOT NULL default 0
				";

				# Start Transaction 
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_contacts table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					app_log($this->error, 'error', __FILE__, __LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$this->setVersion(37);
				$GLOBALS['_database']->CommitTrans();
			}

			# 	register_users = public|private add schema version 38
			if ($this->version() < 38) {
				app_log("Upgrading schema to version 38", 'notice', __FILE__, __LINE__);
				$alter_table_query = "
					ALTER TABLE `register_users` ADD COLUMN `profile` enum('public','private') NOT NULL default 'private'
				";
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_users table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					app_log($this->error, 'error', __FILE__, __LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$this->setVersion(38);
				$GLOBALS['_database']->CommitTrans();
			}		

			if ($this->version() < 39) {
				app_log("Upgrading schema to version 39", 'notice', __FILE__, __LINE__);
				$alter_table_query = "
					ALTER TABLE `register_auth_failures` MODIFY COLUMN `ip_address` int unsigned NOT NULL
				";
				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_users table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					app_log($this->error, 'error', __FILE__, __LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$this->setVersion(39);
				$GLOBALS['_database']->CommitTrans();
			}

			if ($this->version() < 40) {
				app_log("Upgrading schema to version 40", 'notice', __FILE__, __LINE__);

				# Start Transaction 
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$alter_table_query = "
					ALTER TABLE `register_users` ADD COLUMN `secret_key` varchar(255) NOT NULL
				";

				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_users table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					app_log($this->error, 'error', __FILE__, __LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$this->setVersion(40);
				$GLOBALS['_database']->CommitTrans();
			}
			
			if ($this->version() < 41) {
				app_log("Upgrading schema to version 41", 'notice', __FILE__, __LINE__);

				# Start Transaction 
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$alter_table_query = "
					ALTER TABLE `register_organization_products` DROP PRIMARY KEY
				";

				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_users table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					app_log($this->error, 'error', __FILE__, __LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$alter_table_query = "
					ALTER TABLE `register_organization_products` ADD COLUMN `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY BEFORE organization_id
				";

				$GLOBALS['_database']->Execute($alter_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering register_users table in Register::Schema::upgrade(): " . $GLOBALS['_database']->ErrorMsg();
					app_log($this->error, 'error', __FILE__, __LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$this->setVersion(41);
				$GLOBALS['_database']->CommitTrans();
			}
			return true;
		}
	}
