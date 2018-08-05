<?
	namespace Company;

	class Schema {
		public $errno;
		public $error;
		public $module = "Company";
		
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
				$update_schema_query = "
					INSERT
					INTO	company__info
					VALUES	('schema_version',1)
					ON DUPLICATE KEY UPDATE
							value = 1
				";
				$GLOBALS['_database']->Execute($update_schema_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Company::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
				$current_schema_version = 1;

				$update_schema_version = "
					UPDATE	company__info
					SET		value = $current_schema_version
					WHERE	label = 'schema_version'
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Company::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
			}
			if ($current_schema_version < 2) {
				$create_companies_query = "
					CREATE TABLE IF NOT EXISTS `company_companies` (
						`id` int(5) NOT NULL auto_increment,
						`name` varchar(255) NOT NULL default '',
						`login` varchar(50) NOT NULL default '',
						`primary_domain` int(5) NOT NULL default '0',
						`status` int(1) default '1',
						`deleted` int(1) NOT NULL default '0',
						PRIMARY KEY  (`id`),
						UNIQUE KEY `name` (`name`)
					)
				";
				$GLOBALS['_database']->Execute($create_companies_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Site::Companies::schema_manager(): ".$GLOBALS['_database']->ErrorMsg();
					return undef;
				}
				$create_locations_query = "
					CREATE TABLE IF NOT EXISTS `company_locations` (
						`id` int(8) NOT NULL auto_increment,
						`company_id` int(6) NOT NULL default '0',
						`code` varchar(100) NOT NULL default '',
						`address_1` varchar(255) NOT NULL default '',
						`address_2` varchar(255) NOT NULL default '',
						`city` varchar(255) NOT NULL default '',
						`state_id` int(3) NOT NULL default '0',
						`zip_code` int(5) NOT NULL default '0',
						`zip_ext` int(4) NOT NULL default '0',
						`content` text NOT NULL,
						`order_number_sequence` int(8) NOT NULL default '0',
						`area_code` int(3) NOT NULL default '0',
						`phone_pre` int(3) NOT NULL default '0',
						`phone_post` int(11) NOT NULL default '0',
						`phone_ext` int(5) NOT NULL default '0',
						`fax_code` int(11) NOT NULL default '0',
						`fax_pre` int(3) NOT NULL default '0',
						`fax_post` int(4) NOT NULL default '0',
						`active` int(1) NOT NULL default '0',
						`name` varchar(255) NOT NULL default '',
						`service_contact` int(11) NOT NULL default '0',
						`sales_contact` int(11) NOT NULL default '0',
						`domain_id` int(11) unsigned NOT NULL default '0',
						`host` varchar(45) NOT NULL default '',
						PRIMARY KEY  (`id`),
						UNIQUE KEY `location_key` (`company_id`,`code`),
						FOREIGN KEY `fk_company_id` (`company_id`) REFERENCES company_companies (`id`) 
					)
				";
				$GLOBALS['_database']->Execute($create_locations_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Company::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
				$create_domains_query = "
					CREATE TABLE IF NOT EXISTS `company_domains` (
						`id` int(11) NOT NULL auto_increment,
						`status` int(11) NOT NULL default '0',
						`comments` varchar(100) NOT NULL default '',
						`location_id` int(11) NOT NULL default '0',
						`domain_name` varchar(100) NOT NULL default '',
						`date_registered` date NOT NULL default '0000-00-00',
						`date_created` datetime NOT NULL default '0000-00-00 00:00:00',
						`date_expires` date NOT NULL default '0000-00-00',
						`registration_period` int(11) NOT NULL default '0',
						`register` varchar(100) NOT NULL default '',
						`company_id` int(5) NOT NULL default '0',
						PRIMARY KEY  (`id`),
						UNIQUE KEY `uk_domain` (`domain_name`),
						FOREIGN KEY `fk_company_id` (`company_id`) REFERENCES `company_companies` (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_domains_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Company::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
				$current_schema_version = 2;

				$update_schema_version = "
					UPDATE	company__info
					SET		value = $current_schema_version
					WHERE	label = 'schema_version'
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Company::Schema::update(): ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
			}
			
			if ($current_schema_version < 3) {
				$create_companies_query = "
					CREATE TABLE IF NOT EXISTS `company_departments` (
						`id` int(5) NOT NULL auto_increment,
						`code` varchar(32) NOT NULL default '',
						`name` varchar(255) NOT NULL default '',
						`description` text,
						`manager_id` int(1) NOT NULL default '0',
						`status` enum('ACTIVE','DELETED') NOT NULL default 'ACTIVE',
						PRIMARY KEY  (`id`),
						UNIQUE KEY `uk_code` (`code`)
					)
				";
				$GLOBALS['_database']->Execute($create_companies_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Company::Schema::update(): ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
				$create_locations_query = "
					CREATE TABLE IF NOT EXISTS `company_department_users` (
						`department_id` int(5) NOT NULL auto_increment,
						`user_id` int(11) NOT NULL,
						PRIMARY KEY  `pk_department_user` (`department_id`,`user_id`),
						FOREIGN KEY `fk_department_id` (`department_id`) REFERENCES company_departments (`id`),
						FOREIGN KEY `fk_user_id` (`user_id`) REFERENCES register_users (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_locations_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Company::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}

				$current_schema_version = 3;

				$update_schema_version = "
					UPDATE	company__info
					SET		value = $current_schema_version
					WHERE	label = 'schema_version'
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Company::Schema::update(): ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
			}
		}
	}
?>