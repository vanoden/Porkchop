<?
	class Location {
		public $name;
		public $address_1;
		public $address_2;
		public $address_3;
		public $country;
		public $region;
		public $zip_code;

		public function __construct($id = 0) {
			
		}

		public function add($parameters = array()) {
		
		}

		public function update($parameters = array()) {
		
		}

		public function details() {
		
		}
	}

	class LocationList {
		public $error;

		public function __construct($id = 0) {

		}

		public function find($parameters = array()) {

		}
	}

	class BuiltInSchema {		public $error;
		public $errno;
		public $module = "builtin";

		public function __construct() {
			$this->upgrade();
		}

		public function version() {
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();
			$info_table = "builtin__info";

			if (! in_array($info_table,$schema_list)) {
				# Create __info table
				$create_table_query = "
					CREATE TABLE `$info_table` (
						label	varchar(100) not null primary key,
						value	varchar(255)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating info table in RegisterSchema::__construct: ".$GLOBALS['_database']->ErrorMsg();
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
				$this->error = "SQL Error in RegisterInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			list($version) = $rs->FetchRow();
			if (! $version) $version = 0;
			return $version;
		}
		public function upgrade() {
			$current_schema_version = $this->version();

			if ($current_schema_version < 1) {
				app_log("Upgrading schema to version 1",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `builtin_locations` (
						`id` int(11) NOT NULL,
						`address_1` varchar(255),
						`address_2` varchar(255),
						`address_3` varchar(255),
						`city` varchar(255),
						`country` varchar(255),
						`region` varchar(255),
						`zip_code` varchar(255),
						PRIMARY KEY (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating builtin locations table in RegisterInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 1;
				$update_schema_version = "
					INSERT
					INTO	builtin__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					app_log("SQL Error in BuiltInSchema::update: ".$GLOBALS['_database']->ErrorMsg(),'error',__FILE__,__LINE__);
					$this->error = "Error updating database";
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
		}
	}
?>