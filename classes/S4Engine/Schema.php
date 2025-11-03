<?php
	namespace S4Engine;
	
	class Schema Extends \Database\BaseSchema {
		public $module = "s4engine";

		public function upgrade() {
			$this->clearError();

			if ($this->version() < 1) {
				app_log("Upgrading schema to version 1", 'notice', __FILE__, __LINE__);
				
				# Start Transaction
				if (!$GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported", 'warning', __FILE__, __LINE__);

					$create_table_query = "
					CREATE TABLE IF NOT EXISTS `s4engine_clients` (
						`id`			int(11) NOT NULL AUTO_INCREMENT,
						`number`		int(11) NOT NULL,
						`serial_number`	varchar(64) NOT NULL,
						`model_number`	varchar(64) NOT NULL,
						PRIMARY KEY (`id`),
						UNIQUE KEY `UK_NUMBER` (`number`)
					)
				";
					
				if (! $this->executeSQL($create_table_query)) {
					$this->SQLError("Creating s4engine_clients table: ".$this->error());
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `s4engine_sessions` (
						`id`			int(11) NOT NULL AUTO_INCREMENT,
						`number`		int unsigned NOT NULL,
						`client_id`		int(11) NOT NULL,
						`time_start`	datetime NOT NULL,
						`time_end`		datetime NOT NULL,
						`portal_id`		int(10) NOT NULL,
						PRIMARY KEY (`id`),
						UNIQUE KEY `UK_CLIENT_NUMBER` (`client_id`,`number`),
						FOREIGN KEY `FK_PORTAL_SESSION` (`portal_id`) REFERENCES session_sessions (`id`),
						FOREIGN KEY `FK_CLIENT` (`client_id`) REFERENCES s4engine_clients (`id`)
					)
				";
					
				if (! $this->executeSQL($create_table_query)) {
					$this->error("Creating S4Engine::Sessions table: ".$this->error());
					return false;
				}

				$this->setVersion(1);
				$GLOBALS['_database']->CommitTrans();
			}

			if ($this->version() < 2) {
				app_log("Upgrading schema to version 2", 'notice', __FILE__, __LINE__);
				
				# Start Transaction
				if (!$GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported", 'warning', __FILE__, __LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `s4engine_clients` (
						`id`			int(11) NOT NULL AUTO_INCREMENT,
						`number`		int(11) NOT NULL,
						`serial_number`	varchar(64) NOT NULL,
						`model_number`	varchar(64) NOT NULL,
						PRIMARY KEY (`id`),
						UNIQUE KEY `UK_NUMBER` (`number`)
					)
				";
					
				if (! $this->executeSQL($create_table_query)) {
					$this->SQLError("Creating s4engine_clients table: ".$this->error());
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `s4engine_log` (
						`id`				int(11) NOT NULL AUTO_INCREMENT,
						`function_id`		varbinary(2),
						`client_id`			varbinary(2),
						`server_id`			varbinary(2),
						`session_code`		varbinary(16),
						`content_length`	varbinary(2),
						`body`				varbinary(64),
						`checksum`			varbinary(2),
						`time_created`		datetime NOT NULL,
						`success`			tinyint(1) NOT NULL DEFAULT '0',
						`error`				varchar(255) DEFAULT NULL,
						PRIMARY KEY (`id`),
						INDEX `IDX_TIME_CREATED` (`time_created`),
						INDEX `IDX_FUNCTION_ID` (`time_created`,`function_id`),
						INDEX `IDX_CLIENT_ID` (`time_created`,`client_id`)
					)
				";
					
				if (! $this->executeSQL($create_table_query)) {
					$this->error("Creating S4Engine::Log table: ".$this->error());
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

				$alter_table_query = "
					ALTER TABLE `s4engine_log` ADD COLUMN `remote_address` varchar(255) AFTER `server_id`
				";

				if (! $this->executeSQL($alter_table_query)) {
					$this->error("Altering s4engine_log table: ".$this->error());
					return false;
				}

				$this->setVersion(3);
				$GLOBALS['_database']->CommitTrans();
			}

			return true;
		}
	}
