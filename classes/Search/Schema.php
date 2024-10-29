<?php
	namespace Search;

	class Schema Extends \Database\BaseSchema {
		public $module = 'search';

		public function upgrade ($max_version = 999) {
			$this->clearError();

			if ($this->version() < 1) {
				app_log("Upgrading ".$this->module." schema to version 1",'notice',__FILE__,__LINE__);
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `search_tags` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`class` varchar(255) NOT NULL DEFAULT '',
					`category` varchar(255) NOT NULL DEFAULT '',
					`value` varchar(255) NOT NULL DEFAULT '',
					PRIMARY KEY (`id`),
					UNIQUE KEY `unique_tag` (`class`, `category`, `value`)
					) ENGINE=InnoDB;
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->SQLError("create site_audit_events table: ".$this->error());
					return false;
				}

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `search_tags_xref` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`tag_id` int(11) NOT NULL,
						`object_id` int(11) NOT NULL,
						PRIMARY KEY (`id`),
						KEY `tag_id` (`tag_id`),
						KEY `object_id` (`object_id`),
						CONSTRAINT `search_tags_xref_ibfk_1` FOREIGN KEY (`tag_id`) REFERENCES `search_tags` (`id`) ON DELETE CASCADE
					) ENGINE=InnoDB;
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->SQLError("create site_audit_events table: ".$this->error());
					return false;
				}

				$this->setVersion(1);
				$GLOBALS['_database']->CommitTrans();
			}
		}
	}