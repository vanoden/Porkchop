<?php
	namespace Form;

	class Schema Extends \Database\BaseSchema {
		public $module = "Form";

		public function upgrade() {
			$this->error = null;

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
						`method` enum('post','post') NOT NULL DEFAULT 'post',
						PRIMARY KEY (`id`),
						UNIQUE KEY `idx_form_code` (`company_id`,`user_id`)
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
						`type` enum('hidden','text','textarea','select','submit') NOT NULL DEFAULT 'text',
						`name` varchar(64) NOT NULL,
						`prompt` varchar(64) DEFAULT NULL,
						`validation_pattern` varchar(128),
						`group_id` varchar(64) DEFAULT NULL,
						`default` varchar(64) DEFAULT NULL,
						`sort_order` INT(3) DEFAULT 50,
						`required` INT(1) DEFAULT 0,
						PRIMARY KEY (`id`),
						INDEX `idx_form_question` (`form_id`, `group_id`, `sort_order`),
						FOREIGN KEY `fk_form_question` (`form_id`) REFERENCES `form_forms` (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->SQLError($this->error());
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(1);
				$GLOBALS['_database']->CommitTrans();
			}
		
			return true;
		}
	}
