<?php
	namespace Solvay;

	class Schema {
		public $errno;
		public $error;
		public $module = "Solvay";
		
		public function __construct() {
			$this->upgrade();
		}
		public function version() {
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();
			$info_table = strtolower($this->module)."__info";

			if (! in_array($info_table,$schema_list)) {
                # Create __info table
                $create_table_query = "
                    CREATE TABLE `$info_table` (
                        label   varchar(100) not null primary key,
                        value   varchar(255)
                    )
                ";
                $GLOBALS['_database']->Execute($create_table_query);
                if ($GLOBALS['_database']->ErrorMsg()) {
                    $this->error = "SQL Error creating info table in ".$this->class."Schema::version: ".$GLOBALS['_database']->ErrorMsg();
                    return null;
                }
            }

            # Check Current Schema Version
            $get_version_query = "
                SELECT  value
                FROM    `$info_table`
                WHERE   label = 'schema_version'
            ";

            $rs = $GLOBALS['_database']->Execute($get_version_query);
            if (! $rs) {
                $this->error = "SQL Error in ".$this->module."::version: ".$GLOBALS['_database']->ErrorMsg();
                return null;
            }

            list($version) = $rs->FetchRow();
            if (! $version) $version = 0;
            return $version;
		}
		public function upgrade() {
			$this->error = '';
			$info_table = strtolower($this->module)."__info";

			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();

			if (! in_array($info_table,$schema_list)) {
				# Create company__info table
				$create_table_query = "
					CREATE TABLE `$info_table` (
						label	varchar(100) not null primary key,
						value	varchar(255)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating info table in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
			}

			# Check Current Schema Version
			$get_version_query = "
				SELECT	value
				FROM	`$info_table`
				WHERE	label = 'schema_version'
			";

			$rs = $GLOBALS['_database']->Execute($get_version_query);
			if (! $rs) {
				$this->error = "SQL Error in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			list($current_schema_version) = $rs->FetchRow();

			if ($current_schema_version < 1) {
				app_log("Upgrading ".$this->module." Schema to version 1",'notice',__FILE__,__LINE__);
				$update_schema_query = "
					INSERT
					INTO	`$info_table`
					VALUES	('schema_version',1)
					ON DUPLICATE KEY UPDATE
							value = 1
				";
				$GLOBALS['_database']->Execute($update_schema_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating _info table in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
				$current_schema_version = 1;
				$update_schema_version = "
					UPDATE	`$info_table
					SET		value = $current_schema_version
					WHERE	label = 'schema_version'
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
			}
			if ($current_schema_version < 2) {
				app_log("Upgrading ".$this->module." schema to version 2",'notice',__FILE__,__LINE__);
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `solvay_cylinders` (
					  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					  `code` varchar(32) NOT NULL,
					  UNIQUE KEY `uk_cylinder_code` (`code`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating cylinders table in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `monitor_collection_cylinders` (
					  `collection_id` int(11) NOT NULL,
					  `cylinder_id` int(11) NOT NULL,
					  `weight_start` decimal(5,2),
					  `weight_end` decimal(5,2),
					  PRIMARY KEY `pk_collection_cylinder` (`collection_id`,`cylinder_id`),
					  FOREIGN KEY `fk_collection_id` (`collection_id`) REFERENCES monitor_collections (`id`),
					  FOREIGN KEY `fk_cylinder_id` (`cylinder_id`) REFERENCES solvay_cylinders (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating cylinders table in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}

				$current_schema_version = 2;
				$update_schema_version = "
					UPDATE	`$info_table`
					SET		value = $current_schema_version
					WHERE	label = 'schema_version'
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
			}
			if ($current_schema_version < 3) {
				app_log("Upgrading ".$this->module." schema to version 3",'notice',__FILE__,__LINE__);
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `solvay_protocols` (
						`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
						`name`	varchar(255) NOT NULL,
						UNIQUE KEY `uk_protocol_name` (`name`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating protocols table in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
				
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `solvay_protocol_metadata` (
						`key`	varchar(100) NOT NULL PRIMARY KEY,
						`value`	varchar(255),
						UNIQUE KEY `uk_key` (`key`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating protocols table in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}

				$current_schema_version = 3;
				$update_schema_version = "
					UPDATE	`$info_table`
					SET		value = $current_schema_version
					WHERE	label = 'schema_version'
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
			}
		}
	}
?>
