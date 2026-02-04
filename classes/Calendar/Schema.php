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
						`timestamp_created` datetime DEFAULT CURRENT_TIMESTAMP,
						`name` varchar(32) NOT NULL DEFAULT '',
						PRIMARY KEY `pk_calendar_id` (`id`),
						UNIQUE KEY `uk_calendar_code` (`code`),
						KEY `idx_calendar_name` (`name`),
						FOREIGN KEY `fk_calendar_owner_id` (`owner_id`) REFERENCES `register_users` (`id`)
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
						`name` varchar(128) NOT NULL DEFAULT '',
						`timestamp_created` datetime NOT NULL DEFAULT '1001-01-01 00:00:00',
						`timestamp_start` datetime NOT NULL DEFAULT '1001-01-01 00:00:00',
						`timestamp_end` datetime NOT NULL DEFAULT '1001-01-01 00:00:00',
						`description` text DEFAULT NULL,
						`all_day` tinyint(1) NOT NULL DEFAULT 0,
						PRIMARY KEY `pk_calendar_event_id` (`id`),
						UNIQUE KEY `uk_calendar_event_code` (`code`),
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

			if ($this->version() < 2) {
				app_log("Upgrading ".$this->module." schema to version 2",'notice',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `calendar_metadata` (
						`id` int(10) NOT NULL AUTO_INCREMENT,
						`calendar_id` int(10) NOT NULL DEFAULT '0',
						`key` varchar(64) NOT NULL DEFAULT '',
						`value` text,
						PRIMARY KEY `pk_calendar_metadata_id` (`id`),
						KEY `idx_calendar_meta_key` (`key`),
						FOREIGN KEY `fk_metadata_calendar_id` (`calendar_id`) REFERENCES `calendar_calendars` (`id`)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Creating calendar_metadata table: ".$database->error());
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `calendar_event_metadata` (
						`id` int(10) NOT NULL AUTO_INCREMENT,
						`event_id` int(10) NOT NULL DEFAULT '0',
						`key` varchar(64) NOT NULL DEFAULT '',
						`value` text,
						PRIMARY KEY `pk_calendar_event_metadata_id` (`id`),
						KEY `idx_event_meta_key` (`key`),
						FOREIGN KEY `fk_event_metadata_event_id` (`event_id`) REFERENCES `calendar_events` (`id`)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Creating calendar_event_metadata table: ".$database->error());
					return false;
				}

				// Create Table Associating Events to Users
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `calendar_event_users` (
						`id` int(10) NOT NULL AUTO_INCREMENT,
						`event_id` int(10) NOT NULL DEFAULT '0',
						`user_id` int(10) NOT NULL DEFAULT '0',
						`optional` tinyint(1) NOT NULL DEFAULT 0,
						PRIMARY KEY `pk_calendar_event_user_id` (`id`),
						UNIQUE KEY `uk_event_user` (`event_id`,`user_id`),
						FOREIGN KEY `fk_event_user_event_id` (`event_id`) REFERENCES `calendar_events` (`id`),
						FOREIGN KEY `fk_event_user_user_id` (`user_id`) REFERENCES `register_users` (`id`)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Creating calendar_event_users table: ".$database->error());
					return false;
				}

				$this->setVersion(2);
				$database->CommitTrans();
			}
			if ($this->version() < 3) {
				app_log("Upgrading ".$this->module." schema to version 3",'notice',__FILE__,__LINE__);

				$alter_table_query = "
					ALTER TABLE `calendar_calendars`
					ADD COLUMN `public` TINYINT(1) NOT NULL DEFAULT 0 AFTER `timestamp_created`,
					ADD COLUMN `description` TEXT NOT NULL DEFAULT '' AFTER `name`
				";
				if (! $database->Execute($alter_table_query)) {
					$this->SQLError("Altering calendar_calendars table: ".$database->error());
					return false;
				}

				$this->setVersion(3);
				$database->CommitTrans();
			}

			return true;
		}
	}
