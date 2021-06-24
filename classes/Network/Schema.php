<?php
	namespace Network;

	class Schema {
		public $error;
		public $errno;
		private $module = "network";
		private $class = "Network";
		private $roles = array(
			'network editor'	=> 'Can add/edit network info',
			'network viewer'	=> 'Can see network info'
		);

		public function __construct() {
			$this->upgrade();
		}
		
		public function version() {
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();
			$info_table = $this->module."__info";

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
					$this->error = "SQL Error creating info table in ".$this->class."::Schema::version(): ".$GLOBALS['_database']->ErrorMsg();
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
				$this->error = "SQL Error in ".$this->class."::Schema::version(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			list($version) = $rs->FetchRow();
			if (! $version) $version = 0;
			return $version;
		}
		public function upgrade() {
			$current_schema_version = $this->version();

			if ($current_schema_version < 1) {
				app_log("Upgrading ".$this->class." schema to version 1",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `network_domains` (
						`id`			int(11) NOT NULL AUTO_INCREMENT,
						`name`			varchar(255) NOT NULL,
						PRIMARY KEY (`id`),
						UNIQUE KEY		`uk_name` (`name`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating network_domains table in ".$this->class."::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `network_hosts` (
						`id`			int(11) NOT NULL AUTO_INCREMENT,
						`name`			varchar(255) NOT NULL,
						`domain_id`		int(11) NOT NULL,
						`os_name`		varchar(255),
						`os_version`	varchar(100),
						PRIMARY KEY (`id`),
						UNIQUE KEY `uk_name` (`name`),
						FOREIGN KEY `fk_domain` (`domain_id`) REFERENCES `network_domains` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating network_hosts table in ".$this->class."::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `network_adapters` (
						`id`			int(11) NOT NULL AUTO_INCREMENT,
						`name`			varchar(255) NOT NULL,
						`mac_address`	varchar(255) NOT NULL,
						`type`			enum('eth','wlan','tun','tap','lo') NOT NULL,
						`host_id`		int(11) NOT NULL,
						PRIMARY KEY (`id`),
						UNIQUE KEY `uk_host_name` (`host_id`,`name`),
						UNIQUE KEY `uk_mac` (`mac_address`),
						FOREIGN KEY `fk_host` (`host_id`) REFERENCES `network_hosts` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating network_adapters table in ".$this->class."::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `network_addresses` (
						`id`			int(11) NOT NULL AUTO_INCREMENT,
						`address`		varchar(255) NOT NULL,
						`prefix`		varchar(255) NOT NULL,
						`type`			enum('ipv4','ipv6') NOT NULL DEFAULT 'ipv4',
						`adapter_id`	int(11) NOT NULL,
						PRIMARY KEY (`id`),
						UNIQUE KEY `uk_address` (`address`),
						FOREIGN KEY `fk_adapter` (`adapter_id`) REFERENCES `network_adapters` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating network_addresses table in ".$this->class."::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 1;
				$update_schema_version = "
					INSERT
					INTO	".$this->module."__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					app_log("SQL Error in ".$this->class."::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg(),'error',__FILE__,__LINE__);
					$this->error = "Error adding roles to database";
					$GLOBALS['_database']->RollbackTrans();
					return null;
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
			}
		}
	}
