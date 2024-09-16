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

			return true;
		}
	}
