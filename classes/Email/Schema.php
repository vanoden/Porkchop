<?php
	namespace Email;

	class Schema Extends \Database\BaseSchema {
		public function __construct() {
			$this->module = "Email";
			parent::__construct();
		}

		public function upgrade() {
			$this->clearError();
			$database = new \Database\Service();

			if ($this->version() < 2) {
				app_log("Upgrading schema to version 2",'notice',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS email_messages (
						`id`	int(11) not null AUTO_INCREMENT,
						`date_created` datetime not null,
						`date_tried` datetime,
						`tries` int(2) not null default 0,
						`status` enum('QUEUED','SENDING','ERROR','CANCELLED','SENT','FAILED') not null default 'QUEUED',
						`to` varchar(255) not null,
						`from` varchar(255) not null,
						`subject` varchar(255) not null,
						`body` text,
						`html` int(1) not null default 1,
						`process_id` int(11) not null default 0,
						PRIMARY KEY `pk_id` (`id`),
						INDEX `idx_status` (`status`,`date_created`),
						INDEX `idx_date` (`date_created`),
						INDEX `idx_to` (`to`)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Error creating email_messages table in ".$this->module."::Schema::upgrade(): ".$database->ErrorMsg());
					app_log($this->error(), 'error');
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS email_history (
						`email_id`	int(11) not null,
						`date_event` datetime not null,
						`new_status` enum('QUEUED','SENDING','ERROR','CANCELLED','SENT','FAILED') not null default 'QUEUED',
						`response_code` int(3),
						`host` varchar(255),
						`result` text,
						INDEX `idx_id` (`email_id`),
						INDEX `idx_date` (`date_event`),
						INDEX `idx_host` (`host`),
						INDEX `idx_code` (`response_code`)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Error creating email_history table in ".$this->module."::Schema::upgrade(): ".$database->ErrorMsg());
					app_log($this->error(), 'error');
					return false;
				}

				$this->setVersion(2);
				$database->CommitTrans();
			}

			if ($this->version() < 3) {
				app_log("Upgrading schema to version 3",'notice',__FILE__,__LINE__);

				// Deliveries reference a message, and attempts reference a delivery.
				// Those foreign keys are required to queue one body to many recipients
				// and to retry each recipient on its own schedule.
				$create_messages = "
					CREATE TABLE IF NOT EXISTS `mail_queue_messages` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`code` varchar(64) NOT NULL,
						`date_queued` datetime NOT NULL,
						`user_id` int(11) NOT NULL DEFAULT 0,
						`from_address` varchar(255) NOT NULL,
						`subject` varchar(255) NOT NULL,
						`mime_type` varchar(255) NOT NULL DEFAULT 'text/plain',
						`content` mediumtext,
						PRIMARY KEY (`id`),
						UNIQUE KEY `uk_mail_queue_message_code` (`code`),
						KEY `idx_mail_queue_message_queued` (`date_queued`),
						KEY `idx_mail_queue_message_from` (`from_address`),
						KEY `idx_mail_queue_message_user` (`user_id`),
						KEY `idx_mail_queue_message_mime` (`mime_type`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
				";
				if (! $this->executeSQL($create_messages)) {
					$this->SQLError("Error creating mail_queue_messages table in ".$this->module."::Schema::upgrade(): ".$this->error());
					return false;
				}

				$create_deliveries = "
					CREATE TABLE IF NOT EXISTS `mail_queue_deliveries` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`code` varchar(64) NOT NULL,
						`message_id` int(11) NOT NULL,
						`type` enum('TO','CC','BCC') NOT NULL DEFAULT 'TO',
						`name` varchar(255) NOT NULL DEFAULT '',
						`address` varchar(255) NOT NULL,
						`status` enum('QUEUED','ERROR','CANCELLED','FAILED','DELIVERED') NOT NULL DEFAULT 'QUEUED',
						`date_delivered` datetime DEFAULT NULL,
						PRIMARY KEY (`id`),
						UNIQUE KEY `uk_mail_queue_delivery_code` (`code`),
						KEY `idx_mail_queue_delivery_message` (`message_id`),
						KEY `idx_mail_queue_delivery_status` (`status`),
						KEY `idx_mail_queue_delivery_address` (`address`),
						CONSTRAINT `fk_mail_queue_delivery_message` FOREIGN KEY (`message_id`) REFERENCES `mail_queue_messages` (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
				";
				if (! $this->executeSQL($create_deliveries)) {
					$this->SQLError("Error creating mail_queue_deliveries table in ".$this->module."::Schema::upgrade(): ".$this->error());
					return false;
				}

				$create_attempts = "
					CREATE TABLE IF NOT EXISTS `mail_queue_attempts` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`message_id` int(11) NOT NULL,
						`delivery_id` int(11) NOT NULL,
						`date_attempt` datetime NOT NULL,
						`successful` tinyint(1) NOT NULL DEFAULT 0,
						`server_response` text,
						`response_code` varchar(16) NOT NULL DEFAULT '',
						PRIMARY KEY (`id`),
						KEY `idx_mail_queue_attempt_message` (`message_id`,`date_attempt`),
						KEY `idx_mail_queue_attempt_delivery` (`delivery_id`,`date_attempt`),
						CONSTRAINT `fk_mail_queue_attempt_message` FOREIGN KEY (`message_id`) REFERENCES `mail_queue_messages` (`id`),
						CONSTRAINT `fk_mail_queue_attempt_delivery` FOREIGN KEY (`delivery_id`) REFERENCES `mail_queue_deliveries` (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
				";
				if (! $this->executeSQL($create_attempts)) {
					$this->SQLError("Error creating mail_queue_attempts table in ".$this->module."::Schema::upgrade(): ".$this->error());
					return false;
				}

				$this->setVersion(3);
			}

			$this->addRoles(array(
				'email manager'	=> 'Can trigger emails via api'
			));
			return true;
		}
	}
