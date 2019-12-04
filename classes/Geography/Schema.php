<?php
	namespace Geography;

	class Schema Extends \Database\Schema {
		public $module = "Geography";

		public function upgrade() {
			$this->error = null;

			if ($this->version() < 1) {
				app_log("Upgrading schema to version 1",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				# Geography Countries
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `geography_countries` (
						id INT(4) NOT NULL AUTO_INCREMENT,
						name VARCHAR(255) NOT NULL,
						abbreviation VARCHAR(100),
						view_order INT(11) NOT NULL DEFAULT 500,
						PRIMARY KEY `pk_id` (`id`),
						UNIQUE KEY `uk_name` (`name`),
						UNIQUE KEY `uk_abbrev` (`abbreviation`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating geography_countries table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				# Collection of Geography Regions
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `geography_provinces` (
						id INT(11) NOT NULL AUTO_INCREMENT,
						code varchar(255) NOT NULL,
						country_id INT(4) NOT NULL,
						name varchar(255) NOT NULL,
						type varchar(100),
						abbreviation varchar(100) NOT NULL,
						label	varchar(255),
						PRIMARY KEY `pk_id` (`id`),
						UNIQUE KEY `uk_code` (`code`),
						UNIQUE KEY `uk_name` (`country_id`,`name`),
						FOREIGN KEY `fk_country` (`country_id`) REFERENCES `geography_countries` (`id`)
					)
				";
				if (! $this->executeSQL($create_table_query)) {
					$this->error = "SQL Error creating geography_provinces table in ".$this->module."::Schema::upgrade(): ".$this->error;
					app_log($this->error, 'error');
					return false;
				}

				$this->setVersion(1);
				$GLOBALS['_database']->CommitTrans();
			}
			return true;
		}
	}
