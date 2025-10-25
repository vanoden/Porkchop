<?php
	namespace Calendar;

	class Schema Extends \Database\BaseSchema {
		public function __construct() {
			$this->module = "calendar";
			parent::__construct();
		}

		public function upgrade() {
			$this->clearError();

			$database = new \Database\Service();

			if ($this->version() < 1) {
				app_log("Upgrading ".$this->module." schema to version 1",'notice',__FILE__,__LINE__);

				
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `calendar_calendars` (
						`id` int(10) NOT NULL AUTO_INCREMENT,
						`code` varchar(32) NOT NULL DEFAULT '',
						`owner_id` int(6) DEFAULT NULL,
						`timestamp_created` int(10) DEFAULT CURRENT_TIMESTAMP,
						`name` varchar(32) NOT NULL DEFAULT '',
						PRIMARY KEY `pk_calendar_id` (`id`),
						UNIQUE KEY `uk_calendar_code` (`code`),
						KEY `idx_calendar_name` (`name`),
						FOREIGN KEY `fk_owner_id` (`owner_id`) REFERENCES `register_users` (`id`)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Creating calendar_calendars table: ".$database->error());
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `calendar_events` (
						`id` int(10) NOT NULL AUTO_INCREMENT,
						`code` varchar(32) NOT NULL,
						`calendar_id` int(10) NOT NULL DEFAULT '0',
						`name` int(11) NOT NULL DEFAULT '',
						`timestamp_created` datetime NOT NULL DEFAULT '1001-01-01 00:00:00',
						`timestamp_start` datetime NOT NULL DEFAULT '1001-01-01 00:00:00',
						`timestamp_end` datetime NOT NULL DEFAULT '1001-01-01 00:00:00',
						`description` text(256) DEFAULT NULL,
						`all_day` tinyint(1) NOT NULL DEFAULT 0,
						PRIMARY KEY `pk_calendar_event_id` (`id`),
						UNIQUE KEY `uk_calendar_code` `code` (`session_id`),
						FOREIGN KEY `fk_event_calendar_id` (`calendar_id`) REFERENCES `calendar_calendars` (`id`)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Creating calendar_events table: ".$database->error());
					return false;
				}

				$this->setVersion(1);
				$database->CommitTrans();
			}

			return true;
		}
	}
