<?php
	namespace Package;

	class Schema {
		public $error;
		public $errno;
		private $roles = array(
			'package manager'   => 'Full control over packages and versions'
		);

		public function __construct() {
			$this->upgrade();
		}

		public function version() {
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();
			$info_table = "package__info";

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
					$this->error = "SQL Error creating info table in Package::Schema::version(): ".$GLOBALS['_database']->ErrorMsg();
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
				$this->error = "SQL Error in Package::Schema::version(): ".$GLOBALS['_database']->ErrorMsg();
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
						PRIMARY KEY (`id`),
						UNIQUE KEY `uk_package_code` (`code`),
						FOREIGN KEY `fk_owner_id` (`owner_id`) REFERENCES `register_users` (`id`),
						FOREIGN KEY `fk_repo_id` (`repository_id`) REFERENCES `storage_repositories` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating products table in ProductInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
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
						FOREIGN KEY `FK_PACKAGE_ID` (`package_id`) REFERENCES `package_packages` (`id`),
						FOREIGN KEY `FK_FILE_ID` (`id`) REFERENCES `storage_files` (`id`),
						FOREIGN KEY `FK_USER_ID` (`user_id`) REFERENCES `register_users` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating product_images table in ProductInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$current_schema_version = 1;
				$update_schema_version = "
					INSERT
					INTO	package__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Package::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			
			# Add Roles
			foreach ($this->roles as $name => $description) {
				$role = new \Register\Role();
				if (! $role->get($name)) {
					app_log("Adding role '$name'");
					$role->add(array('name' => $name,'description' => $description));
				}
				if ($role->error) {
					$this->_error = "Error adding role '$name': ".$role->error;
					return false;
				}
				return true;
			}
		}
	}
