<?php
	namespace Geography;

	class Schema Extends \Database\BaseSchema {
		public function __construct($parameters = array()) {
			$this->module = "Geography";
			parent::__construct($parameters);
		}

		public function upgrade() {
			$this->clearError();
			$database = new \Database\Service();

			if ($this->version() < 1) {
				app_log("Upgrading schema to version 1",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $database->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				# Geography Countries
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `geography_countries` (
						id INT(4) NOT NULL AUTO_INCREMENT,
						name VARCHAR(150) NOT NULL,
						abbreviation VARCHAR(100),
						view_order INT(11) NOT NULL DEFAULT 500,
						PRIMARY KEY `pk_id` (`id`),
						UNIQUE KEY `uk_name` (`name`),
						UNIQUE KEY `uk_abbrev` (`abbreviation`)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Error creating geography_countries table in ".$this->module."::Schema::upgrade(): ".$database->ErrorMsg());
					app_log($this->error(), 'error');
					return false;
				}

				# Collection of Geography Regions
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `geography_provinces` (
						id INT(11) NOT NULL AUTO_INCREMENT,
						code varchar(150) NOT NULL,
						country_id INT(4) NOT NULL,
						name varchar(150) NOT NULL,
						type varchar(100),
						abbreviation varchar(100) NOT NULL,
						label	varchar(255),
						PRIMARY KEY `pk_id` (`id`),
						UNIQUE KEY `uk_code` (`code`),
						UNIQUE KEY `uk_name` (`country_id`,`name`),
						UNIQUE KEY `uk_province_abbreviation` (`country_id`,`abbreviation`),
						FOREIGN KEY `fk_country` (`country_id`) REFERENCES `geography_countries` (`id`)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Error creating geography_provinces table in ".$this->module."::Schema::upgrade(): ".$database->ErrorMsg());
					app_log($this->error(), 'error');
					return false;
				}

				$this->setVersion(1);
				$database->CommitTrans();
			}

			if ($this->version() < 2) {
				app_log("Upgrading schema to version 2",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $database->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				# Create Counties Table
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `geography_counties` (
						id INT(11) NOT NULL AUTO_INCREMENT,
						code varchar(150) NOT NULL,
						province_id INT(11) NOT NULL,
						name varchar(150) NOT NULL,
						PRIMARY KEY `pk_geography_county_id` (`id`),
						UNIQUE KEY `uk_geography_county_code` (`code`),
						UNIQUE KEY `uk_geography_county_name` (`province_id`,`name`),
						FOREIGN KEY `fk_geography_county_province` (`province_id`) REFERENCES `geography_provinces` (`id`)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Error creating geography_counties table in ".$this->module."::Schema::upgrade(): ".$database->ErrorMsg());
					app_log($this->error(), 'error');
					return false;
				}

				// Create Cities Table
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `geography_cities` (
						id INT(11) NOT NULL AUTO_INCREMENT,
						code varchar(150) NOT NULL,
						name varchar(150) NOT NULL,
						province_id INT(11),
						county_id INT(11),
						latitude DECIMAL(10, 7),
						longitude DECIMAL(10, 7),
						PRIMARY KEY `pk_geography_city_id` (`id`),
						UNIQUE KEY `uk_geography_city_code` (`code`),
						UNIQUE KEY `uk_geography_city_name` (`province_id`,`name`),
						FOREIGN KEY `fk_geography_city_province` (`province_id`) REFERENCES `geography_provinces` (`id`)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Error creating geography_cities table in ".$this->module."::Schema::upgrade(): ".$database->ErrorMsg());
					app_log($this->error(), 'error');
					return false;
				}

				// Create Zip Codes Table
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `geography_zip_codes` (
						id INT(11) NOT NULL AUTO_INCREMENT,
						code varchar(20) NOT NULL,
						province_id INT(11),
						county_id INT(11),
						city_id INT(11),
						latitude DECIMAL(10, 7),
						longitude DECIMAL(10, 7),
						PRIMARY KEY `pk_geography_zip_code_id` (`id`),
						UNIQUE KEY `uk_geography_zip_code_code` (`code`),
						FOREIGN KEY `fk_geography_zip_code_province` (`province_id`) REFERENCES `geography_provinces` (`id`)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Error creating geography_zip_codes table in ".$this->module."::Schema::upgrade(): ".$database->ErrorMsg());
					app_log($this->error(), 'error');
					return false;
				}

				$this->setVersion(2);
				$database->CommitTrans();
			}

			if ($this->version() < 3) {
				app_log("Upgrading schema to version 3",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $database->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				// Create Weather Table
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `geography_weather` (
						`id` INT(11) NOT NULL AUTO_INCREMENT,
						`zip_code_id` INT(11) NOT NULL,
						`date_record` DATETIME NOT NULL,
						`temperature` DECIMAL(5,2),
						`humidity` DECIMAL(5,2),
						`pressure` DECIMAL(7,2),
						`wind_speed` DECIMAL(5,2),
						`wind_direction` DECIMAL(5,2),
						`wind_gust` DECIMAL(5,2),
						`precipitation` DECIMAL(5,2),
						`visibility` INT(9),
						`conditions` VARCHAR(255),
						`forecast` tinyint(1) NOT NULL DEFAULT 0,
						PRIMARY KEY `pk_geography_weather_id` (`id`),
						INDEX `idx_geography_weather_zip_code_id` (`zip_code_id`,`date_record`),
						FOREIGN KEY `fk_geography_weather_zip_code` (`zip_code_id`) REFERENCES `geography_zip_codes` (`id`)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Error creating geography_weather table in ".$this->module."::Schema::upgrade(): ".$database->ErrorMsg());
					app_log($this->error(), 'error');
					return false;
				}

				// Create Times Table
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `geography_times` (
						id INT(11) NOT NULL AUTO_INCREMENT,
						zip_code_id INT(11) NOT NULL,
						date DATE NOT NULL,
						sunrise TIME NOT NULL,
						sunset TIME NOT NULL,
						moonrise TIME,
						moonset TIME,
						moon_phase VARCHAR(255),
						timezone VARCHAR(50),
						timezone_offset INT(6),
						PRIMARY KEY `pk_geography_times_id` (`id`),
						FOREIGN KEY `fk_geography_times_zip_code` (`zip_code_id`) REFERENCES `geography_zip_codes` (`id`)
					)
				";
				if (! $database->Execute($create_table_query)) {
					$this->SQLError("Error creating geography_times table in ".$this->module."::Schema::upgrade(): ".$database->ErrorMsg());
					app_log($this->error(), 'error');
					return false;
				}

				$this->setVersion(3);
				$database->CommitTrans();
			}
			return true;
		}
	}
