<?php
	namespace Form;

	class Schema Extends \Database\BaseSchema {
		public $module = "Form";

		public function upgrade() {
			$this->clearError();

			if ($this->version() < 1) {
				app_log("Upgrading ".$this->module." schema to version 1",'notice',__FILE__,__LINE__);
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `form_forms` (
						`id` int(5) NOT NULL AUTO_INCREMENT,
						`code` varchar(32) NOT NULL DEFAULT '',
						`title` varchar(64) NOT NULL DEFAULT '',
						`user_created` int(6) DEFAULT NULL,
						`date_created` datetime,
						`description` text,
						`instructions` text,
						`action` varchar(128),
						`method` enum('get','post') NOT NULL DEFAULT 'post',
						PRIMARY KEY (`id`),
						UNIQUE KEY `idx_form_code` (`code`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->SQLError($this->error());
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `form_questions` (
						`id` int(10) NOT NULL AUTO_INCREMENT,
						`form_id` int(5) NOT NULL DEFAULT '0',
						`type` enum('hidden','text','textarea','select','checkbox','radio','submit') NOT NULL DEFAULT 'text',
						`text` varchar(64) NOT NULL,
						`prompt` varchar(256) NOT NULL,
						`example` varchar(64) DEFAULT NULL,
						`validation_pattern` varchar(128),
						`group_id` varchar(64) DEFAULT NULL,
						`default` varchar(64) DEFAULT NULL,
						`sort_order` INT(3) DEFAULT 50,
						`required` INT(1) DEFAULT 0,
						`help` varchar(256),
						PRIMARY KEY (`id`),
						INDEX `idx_form_question` (`form_id`, `group_id`, `sort_order`),
						FOREIGN KEY `fk_form_question` (`form_id`) REFERENCES `form_forms` (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->SQLError($this->error());
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `form_question_options` (
						`id` int(10) NOT NULL AUTO_INCREMENT,
						`question_id` int(5) NOT NULL DEFAULT '0',
						`text` varchar(128) NOT NULL,
						`value`	varchar(128) NOT NULL,
						`sort_order` INT(3) NOT NULL DEFAULT 50,
						PRIMARY KEY (`id`),
						INDEX `idx_form_question_` (`question_id`, `sort_order`),
						FOREIGN KEY `fk_form_option_question` (`question_id`) REFERENCES `form_questions` (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->SQLError($this->error());
					return false;
				}

				$this->setVersion(1);
				$GLOBALS['_database']->CommitTrans();
			}

			if ($this->version() < 2) {
				app_log("Upgrading ".$this->module." schema to version 2",'notice',__FILE__,__LINE__);

				// Initialize Database Service
				$database = new \Database\Service();

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `form_versions` (
						`id` int(10) NOT NULL AUTO_INCREMENT,
						`form_id` int(5) NOT NULL DEFAULT '0',
						`code` varchar(32) NOT NULL DEFAULT '',
						`name` varchar(64) NOT NULL DEFAULT '',
						`description` text,
						`instructions` text,
						`user_id_activated` int(6) DEFAULT NULL,
						`date_activated` datetime,
						PRIMARY KEY (`id`),
						UNIQUE KEY `idx_form_version_code` (`form_id`, `code`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->SQLError($this->error());
					return false;
				}

				// See if fk_form_question exists before trying to drop it, since some users may have already manually altered their tables
				$schema = new \Database\Schema();
				$table = $schema->table('form_questions');
				if ($table->has_constraint('fk_form_question')) {
					$alter_table_query = "
						ALTER TABLE `form_questions`
						DROP FOREIGN KEY `fk_form_question`
					";
					if (! $this->executeSQL($alter_table_query)) {
						$this->SQLError($this->error());
						return false;
					}
				}
				elseif ($table->has_constraint('form_questions_ibfk_1')) {
					$alter_table_query = "
						ALTER TABLE `form_questions`
						DROP FOREIGN KEY `form_questions_ibfk_1`
					";
					if (! $this->executeSQL($alter_table_query)) {
						$this->SQLError($this->error());
						return false;
					}
				}

				$alter_table_query = "
					ALTER TABLE `form_questions`
					ADD COLUMN `version_id` int(10) NOT NULL DEFAULT '0' AFTER `id`,
					DROP COLUMN `form_id`
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->SQLError($this->error());
					return false;
				}

				$alter_table_query = "
					ALTER TABLE `form_questions`
					ADD FOREIGN KEY `fk_form_question_version` (`version_id`) REFERENCES `form_versions` (`id`)
				";
				if (! $this->executeSQL($alter_table_query)) {
					$this->SQLError($this->error());
					return false;
				}

				$this->setVersion(2);
				$GLOBALS['_database']->CommitTrans();
			}

			if ($this->version() < 3) {
				app_log("Upgrading ".$this->module." schema to version 3",'notice',__FILE__,__LINE__);

				$alter = "
					ALTER TABLE `form_forms`
					ADD COLUMN `active_version_id` int(10) DEFAULT NULL AFTER `method`,
					ADD KEY `idx_form_active_version` (`active_version_id`)
				";
				if (! $this->executeSQL($alter)) {
					$this->SQLError($this->error());
					return false;
				}
				$fk = "
					ALTER TABLE `form_forms`
					ADD CONSTRAINT `fk_form_active_version`
					FOREIGN KEY (`active_version_id`) REFERENCES `form_versions` (`id`)
					ON DELETE SET NULL
				";
				if (! $this->executeSQL($fk)) {
					$this->SQLError($this->error());
					return false;
				}

				$alter_q = "
					ALTER TABLE `form_questions`
					ADD COLUMN `aggregate_key` varchar(32) NOT NULL DEFAULT '' AFTER `version_id`
				";
				if (! $this->executeSQL($alter_q)) {
					$this->SQLError($this->error());
					return false;
				}

				$upd = "
					UPDATE `form_questions`
					SET `aggregate_key` = MD5(CONCAT('fq:',`id`,':',`version_id`))
					WHERE `aggregate_key` = '' OR `aggregate_key` IS NULL
				";
				if (! $this->executeSQL($upd)) {
					$this->SQLError($this->error());
					return false;
				}

				$this->setVersion(3);
				$GLOBALS['_database']->CommitTrans();
			}

			if ($this->version() < 4) {
				app_log("Upgrading ".$this->module." schema to version 4",'notice',__FILE__,__LINE__);

				$create_sub = "
					CREATE TABLE IF NOT EXISTS `form_submissions` (
						`id` int(10) NOT NULL AUTO_INCREMENT,
						`form_id` int(5) NOT NULL,
						`version_id` int(10) NOT NULL,
						`date_submitted` datetime DEFAULT NULL,
						`object_type` varchar(64) DEFAULT NULL,
						`object_id` int(10) DEFAULT NULL,
						`remote_addr` varchar(45) DEFAULT NULL,
						PRIMARY KEY (`id`),
						KEY `idx_form_submissions_form` (`form_id`),
						KEY `idx_form_submissions_version` (`version_id`),
						KEY `idx_form_submissions_object` (`object_type`,`object_id`),
						CONSTRAINT `fk_form_submission_form` FOREIGN KEY (`form_id`) REFERENCES `form_forms` (`id`),
						CONSTRAINT `fk_form_submission_version` FOREIGN KEY (`version_id`) REFERENCES `form_versions` (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
				";
				if (! $this->executeSQL($create_sub)) {
					$this->SQLError($this->error());
					return false;
				}

				$create_ans = "
					CREATE TABLE IF NOT EXISTS `form_submission_answers` (
						`id` int(10) NOT NULL AUTO_INCREMENT,
						`submission_id` int(10) NOT NULL,
						`question_id` int(10) NOT NULL,
						`aggregate_key` varchar(32) NOT NULL DEFAULT '',
						`value` text,
						PRIMARY KEY (`id`),
						KEY `idx_fsa_submission` (`submission_id`),
						KEY `idx_fsa_question` (`question_id`),
						KEY `idx_fsa_aggregate` (`aggregate_key`),
						CONSTRAINT `fk_fsa_submission` FOREIGN KEY (`submission_id`) REFERENCES `form_submissions` (`id`) ON DELETE CASCADE,
						CONSTRAINT `fk_fsa_question` FOREIGN KEY (`question_id`) REFERENCES `form_questions` (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
				";
				if (! $this->executeSQL($create_ans)) {
					$this->SQLError($this->error());
					return false;
				}

				$this->setVersion(4);
				$GLOBALS['_database']->CommitTrans();
			}

			return true;
		}
	}
