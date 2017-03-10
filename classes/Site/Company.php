<?
	namespace Site;

	class Company {
		private $schema_version = 1;
		public	$error;
		public	$id;
		public $login;
		public $primary_domain;
		public $status;
		public $deleted;

		public function __construct($id = 0) {
			# Check Schema
			$this->schema_manager();

			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function get($name) {
			$get_object_query = "
				SELECT	id
				FROM	company_companies
				WHERE	name = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($name)
			);
			if (! $rs) {
				$this->error = "SQL Error in Site::Company::get(): ".$GLOBALS['_database']->ErrorMsg();
				return undef;
			}
			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
		}

		public function details() {
			$get_details_query = "
				SELECT	*
				FROM	company_companies
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_details_query,
				array($this->id)
			);
			if (! $rs) {
				$this->error = "SQL Error in Site::Company::details(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$object = $rs->FetchNextObject(false);
			if (is_object($object)) {
				$this->name = $object->name;
				$this->login = $object->login;
				$this->primary_domain = $object->primary_domain;
				$this->status = $object->status;
				$this->deleted = $object->deleted;
				return $object;
			}
			else {
				app_log("No company found for id '".$this->id."'",'error');
				return new stdClass();
			}
		}

		public function add($parameters = array()) {
			if (! preg_match('/\w/',$parameters['name'])) {
				$this->error = "name parameter required in company::Company::add";
				return 0;
			}
			
			$add_object_query = "
				INSERT
				INTO	company_companies
				(name)
				VALUES
				(".$GLOBALS['_database']->qstr($parameters['name'],get_magic_quotes_gpc()).")";
			$GLOBALS['_database']->Execute($add_object_query);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in company::Company::add: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			
			return $this->update($this->id,$parameters);
		}

		public function update($parameters = array()){
			if (! preg_match('/^\d+$/',$this->id)) {
				$this->error = "Valid id required for details in company::Company::update";
				return undef;
			}

			if ($parameters['name'])
				$update_object_query .= ",
					name = ".$GLOBALS['_database']->qstr($parameters['name'],get_magic_quotes_gpc());

			# Update Object
			$update_object_query = "
				UPDATE	company_companies
				SET		id = id";
			
			$update_object_query .= "
				WHERE	id = ?
			";

			$GLOBALS['_database']->Execute(
				$update_object_query,
				array($this->id)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Site::Company::update(): ".$GLOBALS['_database']->ErrorMsg();
				return undef;
			}
			
			return $this->details($id);
		}

		private function schema_manager() {
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();

			if (! in_array("company__info",$schema_list))
			{
				# Create company__info table
				$create_table_query = "
					CREATE TABLE company__info (
						label	varchar(100) not null primary key,
						value	varchar(255)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating info table in company::Company::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
			}

			# Check Current Schema Version
			$get_version_query = "
				SELECT	value
				FROM	company__info
				WHERE	label = 'schema_version'
			";

			$rs = $GLOBALS['_database']->Execute($get_version_query);
			if (! $rs) {
				$this->error = "SQL Error in company::Company::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
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
					$this->error = "SQL Error in Site::Company::schema(): ".$GLOBALS['_database']->ErrorMsg();
					return undef;
				}
				$current_schema_version = 1;
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
					$this->error = "SQL Error in company::Companies::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					return undef;
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
					$this->error = "SQL Error in Site::Companies::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					return undef;
				}
				$current_schema_version = 2;
			}

			$update_schema_version = "
				UPDATE	company__info
				SET		value = $current_schema_version
				WHERE	label = 'schema_version'
			";
			$GLOBALS['_database']->Execute($update_schema_version);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Site::Company::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
				return undef;
			}
		}
	}
?>
