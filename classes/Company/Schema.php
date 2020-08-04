<?php
	namespace Company;

	class Schema Extends \Database\BaseSchema {
		public $module = "Company";

		public function upgrade() {
			$this->error = null;

			if ($this->version() < 2) {
				$create_companies_query = "
					CREATE TABLE IF NOT EXISTS `company_companies` (
						`id` int(5) NOT NULL auto_increment,
						`name` varchar(255) NOT NULL default '',
						`login` varchar(50) NOT NULL default '',
						`primary_domain` int(5) NOT NULL default '0',
						`status` int(1) default '1',
						`deleted` int(1) NOT NULL default '0',
						PRIMARY KEY  (`id`),
						UNIQUE KEY `name` (`name`)
					)
				";
				if (! $this->executeSQL($create_companies_query)) {
					$this->error = "SQL Error creating company_companies table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$create_locations_query = "
					CREATE TABLE IF NOT EXISTS `company_locations` (
						`id` int(8) NOT NULL auto_increment,
						`company_id` int(6) NOT NULL default '0',
						`code` varchar(100) NOT NULL default '',
						`address_1` varchar(255) NOT NULL default '',
						`address_2` varchar(255) NOT NULL default '',
						`city` varchar(255) NOT NULL default '',
						`state_id` int(3) NOT NULL default '0',
						`zip_code` int(5) NOT NULL default '0',
						`zip_ext` int(4) NOT NULL default '0',
						`content` text NOT NULL,
						`order_number_sequence` int(8) NOT NULL default '0',
						`area_code` int(3) NOT NULL default '0',
						`phone_pre` int(3) NOT NULL default '0',
						`phone_post` int(11) NOT NULL default '0',
						`phone_ext` int(5) NOT NULL default '0',
						`fax_code` int(11) NOT NULL default '0',
						`fax_pre` int(3) NOT NULL default '0',
						`fax_post` int(4) NOT NULL default '0',
						`active` int(1) NOT NULL default '0',
						`name` varchar(255) NOT NULL default '',
						`service_contact` int(11) NOT NULL default '0',
						`sales_contact` int(11) NOT NULL default '0',
						`domain_id` int(11) unsigned NOT NULL default '0',
						`host` varchar(100) NOT NULL default '',
						PRIMARY KEY  (`id`),
						UNIQUE KEY `location_key` (`company_id`,`code`),
						FOREIGN KEY `fk_company_id` (`company_id`) REFERENCES company_companies (`id`) 
					)
				";
				if (! $this->executeSQL($create_locations_query)) {
					$this->error = "SQL Error creating company_locations table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$create_domains_query = "
					CREATE TABLE IF NOT EXISTS `company_domains` (
						`id` int(11) NOT NULL auto_increment,
						`status` int(11) NOT NULL default '0',
						`comments` varchar(100) NOT NULL default '',
						`location_id` int(11) NOT NULL default '0',
						`domain_name` varchar(100) NOT NULL default '',
						`date_registered` date NOT NULL default '0000-00-00',
						`date_created` datetime NOT NULL default '0000-00-00 00:00:00',
						`date_expires` date NOT NULL default '0000-00-00',
						`registration_period` int(11) NOT NULL default '0',
						`register` varchar(100) NOT NULL default '',
						`company_id` int(5) NOT NULL default '0',
						PRIMARY KEY  (`id`),
						UNIQUE KEY `uk_domain` (`domain_name`),
						FOREIGN KEY `fk_company_id` (`company_id`) REFERENCES `company_companies` (`id`)
					)
				";
				if (! $this->executeSQL($create_domains_query)) {
					$this->error = "SQL Error creating company_domains table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}
				$this->setVersion(2);
				$GLOBALS['_database']->CommitTrans();
			}
			
			if ($this->version() < 3) {
				# Make Sure Register Users is present
				$user_schema = new \Register\Schema();
				$user_schema->upgrade();
				if ($user_schema->version() < 1) {
					$this->error = "Cannot continue, Register Schema ver < 1";
					return false;
				}
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `company_departments` (
						`id` int(5) NOT NULL auto_increment,
						`code` varchar(32) NOT NULL default '',
						`name` varchar(255) NOT NULL default '',
						`description` text,
						`manager_id` int(1) NOT NULL default '0',
						`status` enum('ACTIVE','DELETED') NOT NULL default 'ACTIVE',
						PRIMARY KEY  (`id`),
						UNIQUE KEY `uk_code` (`code`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating company_departments table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `company_department_users` (
						`department_id` int(5) NOT NULL auto_increment,
						`user_id` int(11) NOT NULL,
						PRIMARY KEY  `pk_department_user` (`department_id`,`user_id`),
						FOREIGN KEY `fk_department_id` (`department_id`) REFERENCES company_departments (`id`),
						FOREIGN KEY `fk_user_id` (`user_id`) REFERENCES register_users (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating company_department_users table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(3);
				$GLOBALS['_database']->CommitTrans();
			}
			return true;
		}
	}
