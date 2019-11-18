<?php
	namespace Build;

	class Schema {
		public $errno;
		public $error;
		public $module = "Build";

		public function __construct() {
			$this->upgrade();
		}
	
		public function version() {
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();
			$info_table = strtolower($this->module)."__info";

			if (! in_array($info_table,$schema_list)) {
				# Create __info table
				$create_table_query = "
					CREATE TABLE `$info_table` (
						label   varchar(100) not null primary key,
						value   varchar(255)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating info table in ".$this->module."Schema::version: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
			}

			# Check Current Schema Version
			$get_version_query = "
				SELECT  value
				FROM    `$info_table`
				WHERE   label = 'schema_version'
			";

			$rs = $GLOBALS['_database']->Execute($get_version_query);
			if (! $rs) {
				$this->error = "SQL Error in ".$this->module."::version: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			list($version) = $rs->FetchRow();
			if (! $version) $version = 0;
			return $version;
		}
		
		public function upgrade() {
			$this->error = '';
			$info_table = strtolower($this->module)."__info";

			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();

			if (! in_array($info_table,$schema_list)) {
				# Create company__info table
				$create_table_query = "
					CREATE TABLE `$info_table` (
						label	varchar(100) not null primary key,
						value	varchar(255)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating info table in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
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
				$this->error = "SQL Error in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			list($current_schema_version) = $rs->FetchRow();
			if ($current_schema_version < 1) {
				app_log("Upgrading schema to version 1",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				# Build Products
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `build_products` (
						`id` int(4) NOT NULL AUTO_INCREMENT,
						`name` varchar(100) NOT NULL,
						`workspace` varchar(255) NOT NULL,
						`major_version` int(5) DEFAULT NULL,
						`minor_version` int(5) DEFAULT NULL,
						PRIMARY KEY (`id`),
						UNIQUE KEY `uk_name` (`name`),
						UNIQUE KEY `uk_workspace` (`workspace`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating build_products table in Build::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return false;
				}

				# Build Versions
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `build_versions` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`product_id` int(4) NOT NULL,
						`number` int(11) DEFAULT NULL,
						`timestamp` datetime DEFAULT NULL,
						`status` enum('NEW','FAILED','ACTIVE') NOT NULL DEFAULT 'NEW',
						`tarball` varchar(255) DEFAULT NULL,
						`message` text,
						PRIMARY KEY (`id`),
						UNIQUE KEY `uk_number` (`product_id`,`number`),
						FOREIGN KEY `fk_product` (`product_id`) REFERENCES `build_products` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating build_versions table in Build::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return false;
				}

				$current_schema_version = 1;
				$update_schema_version = "
					INSERT
					INTO    `$info_table`
					VALUES  ('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Build::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
		}
	}
