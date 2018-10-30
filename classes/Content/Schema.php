<?php
	namespace Content;

	class Schema {
		public $errno;
		public $error;
		public $module = "content";
		
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
			$current_schema_version = $this->version();

			if ($current_schema_version < 1) {
				$update_schema_query = "
					INSERT
					INTO	content__info
					VALUES	('schema_version',1)
					ON DUPLICATE KEY UPDATE
							value = 1
				";
				$GLOBALS['_database']->Execute($update_schema_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating _info table in content::Content::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
				$current_schema_version = 1;
				$update_schema_version = "
					INSERT
					INTO	content__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Content::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			if ($current_schema_version < 2) {
				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `content_messages` (
					  `id` int(6) NOT NULL AUTO_INCREMENT,
					  `company_id` int(5) NOT NULL DEFAULT '0',
					  `target` varchar(255) NOT NULL DEFAULT '',
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
					  FOREIGN KEY `fk_company_id` (`company_id`) REFERENCES `company_companies` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating contact types table in content::Content::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
				
				# Add Roles for Content Module
				$insert_roles_query = "
					INSERT
					INTO	register_roles
					(		name,description)
					VALUES
					(		'content operator','Can edit web site content')
					ON DUPLICATE KEY UPDATE
							name = name
				";
				$GLOBALS['_database']->Execute($insert_roles_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error adding roles in register::Person::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
				$current_schema_version = 2;
				$update_schema_version = "
					INSERT
					INTO	content__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Content::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			if ($current_schema_version < 3) {
				# Add Roles for Content Module
				$insert_roles_query = "
					INSERT
					INTO	register_roles
					(		name,description)
					VALUES
					(		'content developer','Can view api page')
					ON DUPLICATE KEY UPDATE
							name = name
				";
				$GLOBALS['_database']->Execute($insert_roles_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error adding roles in register::Person::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
				$current_schema_version = 3;
				$update_schema_version = "
					INSERT
					INTO	content__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Content::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}
				$GLOBALS['_database']->CommitTrans();
			}
		}
	}
?>