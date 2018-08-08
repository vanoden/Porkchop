<?php
	namespace Site;

	class Schema {
		public $errno;
		public $error;
		public $module = "Session";
		
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
                    $this->error = "SQL Error creating info table in ".$this->module."Schema::version: ".$GLOBALS['_database']->ErrorMsg();
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
				app_log("Upgrading session schema to version 1",'notice',__FILE__,__LINE__);
				$update_schema_query = "
					INSERT
					INTO	session__info
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
					UPDATE	session__info
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
					CREATE TABLE IF NOT EXISTS `session_sessions` (
					  `active` int(1) NOT NULL DEFAULT '1',
					  `id` int(10) NOT NULL AUTO_INCREMENT,
					  `code` varchar(32) NOT NULL DEFAULT '',
					  `user_id` int(6) DEFAULT NULL,
					  `last_hit_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					  `first_hit_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					  `browser` varchar(255) DEFAULT NULL,
					  `company_id` int(5) NOT NULL DEFAULT '0',
					  `c_id` int(8) DEFAULT NULL,
					  `e_id` int(8) DEFAULT NULL,
					  `prev_session` varchar(100) NOT NULL DEFAULT '',
					  `refer_url` text,
					  PRIMARY KEY (`id`),
					  KEY `company_id` (`company_id`,`user_id`),
					  KEY `code` (`code`),
					  KEY `end_time` (`last_hit_date`),
					  KEY `idx_active` (`company_id`,`active`,`id`,`user_id`),
					  FOREIGN KEY `fk_company_id` (`company_id`) REFERENCES `company_companies` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating contact types table in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `session_hits` (
					  `id` int(10) NOT NULL AUTO_INCREMENT,
					  `session_id` int(10) NOT NULL DEFAULT '0',
					  `server_id` int(11) NOT NULL DEFAULT '0',
					  `hit_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					  `remote_ip` varchar(20) DEFAULT NULL,
					  `secure` int(1) NOT NULL DEFAULT '0',
					  `script` varchar(100) NOT NULL DEFAULT '',
					  `query_string` text,
					  `order_id` int(8) NOT NULL DEFAULT '0',
					  `module_id` int(3) NOT NULL,
					  PRIMARY KEY (`id`),
					  KEY `session_id` (`session_id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating contact types table in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
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
					ALTER TABLE `session_sessions` MODIFY `code` char(64) NOT NULL DEFAULT ''
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error altering sessions table in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
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
			if ($current_schema_version < 4) {
				app_log("Upgrading ".$this->module." schema to version 4",'notice',__FILE__,__LINE__);
				$create_table_query = "
					ALTER TABLE `session_sessions` ADD `timezone` varchar(32) NOT NULL DEFAULT 'America/New_York'
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error adding timezone to sessions table in ".$this->module."Schema::upgrade: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
				$current_schema_version = 4;
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
