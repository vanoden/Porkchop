<?php
	namespace Content;

	class Schema Extends \Database\BaseSchema {
		public $module = "Content";

		public function upgrade() {
			$this->error = null;

			if ($this->version() < 2) {
				app_log("Upgrading schema to version 2",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `content_messages` (
					  `id` int(6) NOT NULL AUTO_INCREMENT,
					  `company_id` int(5) NOT NULL DEFAULT '0',
					  `target` varchar(150) NOT NULL DEFAULT '',
					  `view_order` int(3) NOT NULL DEFAULT '500',
					  `active` int(1) NOT NULL DEFAULT '1',
					  `deleted` int(1) NOT NULL DEFAULT '0',
					  `title` varchar(80) NOT NULL DEFAULT '',
					  `menu_id` int(11) NOT NULL DEFAULT '0',
					  `name` varchar(255) NOT NULL DEFAULT '',
					  `date_modified` datetime NOT NULL,
					  `content` text,
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `uk_target` (`company_id`,`target`),
					  KEY `idx_main` (`company_id`,`target`,`deleted`),
					  FOREIGN KEY `fk_message_company_id` (`company_id`) REFERENCES `company_companies` (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating content_messages table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(2);
				$GLOBALS['_database']->CommitTrans();
			}
			if ($this->version() < 3) {
				// Existing upgrade tool already had version 3...don't want to add conflicts later
				app_log("Upgrading schema to version 3",'notice',__FILE__,__LINE__);

				$this->setVersion(3);
				$GLOBALS['_database']->CommitTrans();
			}

			$this->addRoles(array(
				'content operator'	=> 'Can edit web site content',
				'content developer'	=> 'Can view api page'
			));
			return true;
		}
	}
