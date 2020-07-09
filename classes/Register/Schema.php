<?php
	namespace Register;
	
	class Schema Extends \Database\BaseSchema {
	
		public $module = "register";
		
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
							`name`			varchar(255) NOT NULL,
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

				// Start Transaction 
				if (!$GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported", 'warning', __FILE__, __LINE__);
				
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
							`privilege`		varchar(255) NOT NULL,
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
						FOREIGN KEY `fk_role_id` (`role_id`) REFERENCES `register_roles` (`id`),
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
					
				// Start Transaction
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
						FOREIGN KEY `fk_user_id` (`user_id`) REFERENCES `register_users` (`id`),
						FOREIGN KEY `fk_loc_id` (`location_id`) REFERENCES `register_locations` (`id`)
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
					
				// Start Transaction
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
						app_log("Dropping ".$constraint->type." ".$constraint->name." from ".$constraint->table,'notice');
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
					
				// Start Transaction
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
						app_log("Dropping ".$constraint->type." ".$constraint->name." from ".$constraint->table,'notice');
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
	
				// Start Transaction 
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

			$this->addRoles(array(
				'register manager'	=> 'Can view/edit customers and organizations',
				'register reporter'	=> 'Can view customers and organizations',
				'location manager'	=> 'Can view and manage location entries'
			));
			return true;
		}
	}
