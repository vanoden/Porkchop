<?php
	namespace Form;

	class Schema Extends \Database\BaseSchema {
		public $module = "Form";

		public function upgrade() {
			$this->clearError();

			if ($this->version() < 1) {
				app_log("Upgrading ".$this->module." schema to version 1",'notice',__FILE__,__LINE__);

				$create_form_versions = "
					CREATE TABLE IF NOT EXISTS `form_versions` (
						`id` int NOT NULL AUTO_INCREMENT,
						`form_id` int NOT NULL DEFAULT '0',
						`code` varchar(32) NOT NULL DEFAULT '',
						`name` varchar(64) NOT NULL DEFAULT '',
						`description` text,
						`instructions` text,
						`user_id_activated` int DEFAULT NULL,
						`date_activated` datetime DEFAULT NULL,
						PRIMARY KEY (`id`),
						UNIQUE KEY `idx_form_version_code` (`form_id`,`code`)
					)
				";
				if (! $this->executeSQL($create_form_versions)) {
					$this->SQLError($this->error());
					return false;
				}

				$create_form_forms = "
					CREATE TABLE IF NOT EXISTS `form_forms` (
						`id` int NOT NULL AUTO_INCREMENT,
						`code` varchar(32) NOT NULL DEFAULT '',
						`title` varchar(64) NOT NULL DEFAULT '',
						`user_created` int DEFAULT NULL,
						`date_created` datetime DEFAULT NULL,
						`description` text,
						`instructions` text,
						`action` varchar(128) DEFAULT NULL,
						`method` enum('get','post') NOT NULL DEFAULT 'post',
						`active_version_id` int DEFAULT NULL,
						PRIMARY KEY (`id`),
						UNIQUE KEY `idx_form_code` (`code`),
						KEY `idx_form_active_version` (`active_version_id`),
						CONSTRAINT `fk_form_active_version` FOREIGN KEY (`active_version_id`) REFERENCES `form_versions` (`id`) ON DELETE SET NULL
					) 
				";
				if (! $this->executeSQL($create_form_forms)) {
					$this->SQLError($this->error());
					return false;
				}

				$create_question_groups = "
					CREATE TABLE IF NOT EXISTS `form_question_groups` (
						`id` int NOT NULL AUTO_INCREMENT,
						`version_id` int NOT NULL,
						`title` varchar(128) NOT NULL DEFAULT '',
						`instructions` text,
						`sort_order` int NOT NULL DEFAULT '50',
						PRIMARY KEY (`id`),
						KEY `idx_form_question_groups` (`version_id`,`sort_order`),
						CONSTRAINT `fk_form_question_groups_version` FOREIGN KEY (`version_id`) REFERENCES `form_versions` (`id`) ON DELETE CASCADE
					)
				";
				if (! $this->executeSQL($create_question_groups)) {
					$this->SQLError($this->error());
					return false;
				}

				$create_form_questions = "
					CREATE TABLE IF NOT EXISTS `form_questions` (
						`id` int NOT NULL AUTO_INCREMENT,
						`aggregate_key` varchar(32) NOT NULL DEFAULT '',
						`type` enum('hidden','text','textarea','select','checkbox','radio','submit') NOT NULL DEFAULT 'text',
						`text` varchar(64) NOT NULL,
						`prompt` varchar(256) NOT NULL,
						`example` varchar(64) DEFAULT NULL,
						`validation_pattern` varchar(128) DEFAULT NULL,
						`group_id` varchar(64) DEFAULT NULL,
						`default` varchar(64) DEFAULT NULL,
						`sort_order` int DEFAULT '50',
						`required` int DEFAULT '0',
						`help` varchar(256) DEFAULT NULL,
						PRIMARY KEY (`id`),
						KEY `idx_form_question` (`group_id`,`sort_order`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
				";
				if (! $this->executeSQL($create_form_questions)) {
					$this->SQLError($this->error());
					return false;
				}

				$create_form_question_options = "
					CREATE TABLE IF NOT EXISTS `form_question_options` (
						`id` int NOT NULL AUTO_INCREMENT,
						`question_id` int NOT NULL DEFAULT '0',
						`text` varchar(128) NOT NULL,
						`value` varchar(128) NOT NULL,
						`sort_order` int NOT NULL DEFAULT '50',
						PRIMARY KEY (`id`),
						KEY `idx_form_question_` (`question_id`,`sort_order`),
						CONSTRAINT `form_question_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `form_questions` (`id`)
					)
				";
				if (! $this->executeSQL($create_form_question_options)) {
					$this->SQLError($this->error());
					return false;
				}

				$create_form_submissions = "
					CREATE TABLE IF NOT EXISTS `form_submissions` (
						`id` int NOT NULL AUTO_INCREMENT,
						`form_id` int NOT NULL,
						`version_id` int NOT NULL,
						`date_submitted` datetime DEFAULT NULL,
						`object_type` varchar(64) DEFAULT NULL,
						`object_id` int DEFAULT NULL,
						`remote_addr` varchar(45) DEFAULT NULL,
						PRIMARY KEY (`id`),
						KEY `idx_form_submissions_form` (`form_id`),
						KEY `idx_form_submissions_version` (`version_id`),
						KEY `idx_form_submissions_object` (`object_type`,`object_id`),
						CONSTRAINT `fk_form_submission_form` FOREIGN KEY (`form_id`) REFERENCES `form_forms` (`id`),
						CONSTRAINT `fk_form_submission_version` FOREIGN KEY (`version_id`) REFERENCES `form_versions` (`id`)
					)
				";
				if (! $this->executeSQL($create_form_submissions)) {
					$this->SQLError($this->error());
					return false;
				}

				$create_form_submission_answers = "
					CREATE TABLE IF NOT EXISTS `form_submission_answers` (
						`id` int NOT NULL AUTO_INCREMENT,
						`submission_id` int NOT NULL,
						`question_id` int NOT NULL,
						`aggregate_key` varchar(32) NOT NULL DEFAULT '',
						`value` text,
						PRIMARY KEY (`id`),
						KEY `idx_fsa_submission` (`submission_id`),
						KEY `idx_fsa_question` (`question_id`),
						KEY `idx_fsa_aggregate` (`aggregate_key`),
						CONSTRAINT `fk_fsa_question` FOREIGN KEY (`question_id`) REFERENCES `form_questions` (`id`),
						CONSTRAINT `fk_fsa_submission` FOREIGN KEY (`submission_id`) REFERENCES `form_submissions` (`id`) ON DELETE CASCADE
					)
				";
				if (! $this->executeSQL($create_form_submission_answers)) {
					$this->SQLError($this->error());
					return false;
				}

				$this->setVersion(1);
				$GLOBALS['_database']->CommitTrans();
			}

			return true;
		}
	}
