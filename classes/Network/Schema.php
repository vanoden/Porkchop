<?php
	namespace Network;

	class Schema Extends \Database\BaseSchema {
		public $module = "network";
		
		public function upgrade() {
			// Initialize Database Service
			$database = new \Database\Service();

			if ($this->version() < 1) {
				app_log("Upgrading ".get_class($this)." schema to version 1",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $database->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `network_domains` (
						`id`			int(11) NOT NULL AUTO_INCREMENT,
						`name`			varchar(255) NOT NULL,
						PRIMARY KEY `pk_network_domains` (`id`),
						UNIQUE KEY		`uk_name` (`name`)
					)
				";
				if (! $database->execute($create_table_query)) {
					$this->SQLError("SQL Error creating network_domains table in ".$this->module."::Schema::upgrade(): ".$database->ErrorMsg());
					app_log($this->error(), 'error');
					return false;
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
				if (! $database->execute($create_table_query)) {
					$this->SQLError("SQL Error creating network_hosts table in ".$this->module."::Schema::upgrade(): ".$database->ErrorMsg());
					app_log($this->error(), 'error');
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `network_host_metadata` (
						`host_id`	int(11) NOT NULL AUTO_INCREMENT,
						`key`		varchar(255) NOT NULL,
						`value`		varchar(11) NOT NULL,
						PRIMARY KEY (`host_id`,`key`),
						FOREIGN KEY `fk_meta_host_id` (`host_id`) REFERENCES `network_hosts` (`id`)
					)
				";
				if (! $database->execute($create_table_query)) {
					$this->SQLError("SQL Error creating network_host_metadata table in ".$this->module."::Schema::upgrade(): ".$database->ErrorMsg());
					app_log($this->error(), 'error');
					return false;
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
				if (! $database->execute($create_table_query)) {
					$this->SQLError("SQL Error creating network_adapters table in ".$this->module."::Schema::upgrade(): ".$database->ErrorMsg());
					app_log($this->error(), 'error');
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `network_subnets` (
						`id`			int(11) NOT NULL AUTO_INCREMENT,
						`address`		bigint(8) NOT NULL,
						`size`			bigint(8) NOT NULL,
						`type`			enum('ipv4','ipv6') NOT NULL DEFAULT 'ipv4',
						PRIMARY KEY `pk_network_subnet_id` (`id`)
					)
				";
				if (! $database->execute($create_table_query)) {
					$this->SQLError("SQL Error creating network_subnets table in ".$this->module."::Schema::upgrade(): ".$database->ErrorMsg());
					app_log($this->error(), 'error');
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `network_addresses` (
						`id`			int(11) NOT NULL AUTO_INCREMENT,
						`address`		bigint(8) NOT NULL,
						`subnet_id`	int(6) NOT NULL,
						`adapter_id`	int(11) NOT NULL,
						PRIMARY KEY (`id`),
						UNIQUE KEY `uk_address` (`address`),
						FOREIGN KEY `fk_adapter` (`adapter_id`) REFERENCES `network_adapters` (`id`)
					)
				";
				if (! $database->execute($create_table_query)) {
					$this->SQLError("SQL Error creating network_addresses table in ".$this->module."::Schema::upgrade(): ".$database->ErrorMsg());
					app_log($this->error(), 'error');
					return false;
				}

				$this->setVersion(1);
				$database->CommitTrans();
			}
			if ($this->version() < 2) {
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `network_acls` (
						`id`		int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
						`subnet_id`	int(11) NOT NULL,
						`priority`	int(11) NOT NULL DEFAULT 0,
						`content`	TEXT,
						`status`	enum('INACTVE','LOG','ACTIVE') NOT NULL DEFAULT 'LOG',
						INDEX `idx_priority` (`priority`)
					)
				";
				if (! $database->execute($create_table_query)) {
					$this->SQLError("SQL Error creating network_acls table in ".$this->module."::Schema::upgrade(): ".$database->ErrorMsg());
					app_log($this->error(), 'error');
					return false;
				}

				$this->setVersion(2);
				$database->CommitTrans();
			}
			if ($this->version() < 3) {
				$alter_table_query = "
					ALTER TABLE `network_subnets`
					ADD COLUMN `managed` enum('AUTO','MANUAL') NOT NULL DEFAULT 'AUTO',
					ADD COLUMN `risk_level` int(3) NOT NULL DEFAULT 0,
					ADD COLUMN `description` varchar(255) NULL,
					ADD COLUMN `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
					ADD COLUMN `date_last_seen` datetime NULL,
					ADD INDEX `idx_subnet_address` (`address`,`size`,`type`),
					ADD INDEX `idx_address_last_seen` (`date_last_seen`,`managed`)
				";
				if (! $database->execute($alter_table_query)) {
					$this->SQLError("SQL Error altering network_subnets table in ".$this->module."::Schema::upgrade(): ".$database->ErrorMsg());
					app_log($this->error(), 'error');
					return false;
				}

				$this->setVersion(3);
				$database->CommitTrans();
			}

			if ($this->version() < 4) {
				$alter_table_query = "
					ALTER TABLE `network_subnets`
					ADD UNIQUE KEY `uk_subnet` (`address`,`size`,`type`)
				";
				if (! $database->execute($alter_table_query)) {
					$this->SQLError("SQL Error altering network_subnets table in ".$this->module."::Schema::upgrade(): ".$database->ErrorMsg());
					app_log($this->error(), 'error');
					return false;
				}

				$this->setVersion(4);
				$database->CommitTrans();
			}

			if ($this->version() < 5) {
				$alter_table_query = "
					ALTER TABLE `network_subnets`
					ADD COLUMN `uri_last_seen` varchar(255) NULL
				";
				if (! $database->execute($alter_table_query)) {
					$this->SQLError("SQL Error altering network_subnets table in ".$this->module."::Schema::upgrade(): ".$database->ErrorMsg());
					app_log($this->error(), 'error');
					return false;
				}

				$this->setVersion(5);
				$database->CommitTrans();
			}

			if ($this->version() > 6) {
				$alter_table_query = "
					ALTER TABLE `network_subnets`
					ADD COLUMN `five_minute_hit_rate` float(5,2) NULL
				";
				if (! $database->execute($alter_table_query)) {
					$this->SQLError("SQL Error altering network_subnets table in ".$this->module."::Schema::upgrade(): ".$database->ErrorMsg());
					app_log($this->error(), 'error');
					return false;
				}
				$this->setVersion(6);
				$database->CommitTrans();
			}
			return true;
		}
	}
