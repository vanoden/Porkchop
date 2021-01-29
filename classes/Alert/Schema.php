<?php
	namespace Alert;

	class Schema Extends \Database\BaseSchema {
	
		public $module = "Alert";

		public function upgrade() {
		
			$this->error = null;

			if ($this->version() < 1) {
			
				app_log("Upgrading schema to version 1",'notice',__FILE__,__LINE__);

				// start transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `alert_threshold` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`sensor_id` int(11) NOT NULL,
						`operator` int(11) NOT NULL,
                        `value` int(11) NOT NULL,
						PRIMARY KEY (`id`),
						FOREIGN KEY `fk_monitor_sensor` (`sensor_id`) REFERENCES `monitor_sensors` (`sensor_id`)
					)
				";

				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating `alert_threshold` table in " . $this->module . "::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}
			
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `alert_trigger_threshold` (
						`trigger_id` int(11) NOT NULL AUTO_INCREMENT,
						`threshold_id` int(11) NOT NULL,
						`group_id` int(11) NOT NULL,
						PRIMARY KEY (`trigger_id`),
						FOREIGN KEY `fk_alert_threshold` (`threshold_id`) REFERENCES `alert_threshold` (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating `alert_trigger_threshold` table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}
				
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `alert_trigger` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`name`  varchar(250),
                        `enabled` boolean NOT NULL,
						PRIMARY KEY (`id`),
						FOREIGN KEY `fk_alert_trigger_threshold` (`id`) REFERENCES `alert_trigger_threshold` (`trigger_id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating `alert_trigger` table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}
				
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `alert_trigger_escalation` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`trigger_id` int(11) NOT NULL,
						`type`  varchar(250),
						`parameters`  text,
						PRIMARY KEY (`id`),
						FOREIGN KEY `fk_alert_trigger` (`trigger_id`) REFERENCES `alert_trigger` (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating `alert_trigger_escalation` table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}
				
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `alert_actions` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`escalation_id` int(11) NOT NULL,
						`status` enum('OK', 'ERROR', 'CRITICAL', 'EMERGENCY') NOT NULL default 'OK',
						PRIMARY KEY (`id`),
						FOREIGN KEY `fk_alert_trigger_escalation` (`escalation_id`) REFERENCES `alert_trigger_escalation` (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating `alert_actions` table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}
				
				$this->setVersion(1);
				$GLOBALS['_database']->CommitTrans();
			}

			if ($this->version() < 2) {

				app_log("Upgrading schema to version 2",'notice',__FILE__,__LINE__);

				// Start Transaction
				if (! $GLOBALS['_database']->BeginTrans()) app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$table_query = "
					ALTER TABLE `alert_threshold` MODIFY `operator` enum('<','>','=') NOT NULL DEFAULT '<';
				";
				if (! $this->executeSQL($table_query)) {
					$this->error = "SQL Error altering `alert_threshold` table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$table_query = "
					ALTER TABLE `alert_threshold` MODIFY `value` decimal(10,2) NULL;
				";
				if (! $this->executeSQL($table_query)) {
					$this->error = "SQL Error altering alert_threshold table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(2);
				$GLOBALS['_database']->CommitTrans();
			}

			$this->addRoles(array(
				'alert manager'	    => 'Can view/edit assets, sensors and collections',
				'alert reporter'	=> 'Can view assets, sensors and collections',
				'alert admin'		=> 'Full access to alert data',
				'alert asset'		=> 'Holding role for actual devices that can post data.'
			));

			return true;
		}
	}
