<?
	namespace Email;

	class Schema {
		public $error;
		public $errno;

		public function __construct() {
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();

			if (! in_array("email__info",$schema_list)) {
				# Create company__info table
				$create_table_query = "
					CREATE TABLE email__info (
						label	varchar(100) not null primary key,
						value	varchar(255)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating info table in EmailInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
			}

			# Check Current Schema Version
			$get_version_query = "
				SELECT	value
				FROM	email__info
				WHERE	label = 'schema_version'
			";

			$rs = $GLOBALS['_database']->Execute($get_version_query);
			if (! $rs) {
				$this->error = "SQL Error in EmailInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}

			list($current_schema_version) = $rs->FetchRow();

			if ($current_schema_version < 1) {
				app_log("Upgrading schema to version 1",'notice',__FILE__,__LINE__);

				$add_roles_query = "
					INSERT
					INTO	register_roles
					VALUES	(null,'email manager','Can trigger emails via api')
				";
				$GLOBALS['_database']->Execute($add_roles_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error adding monitor roles in EmailInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$current_schema_version = 1;
				$update_schema_version = "
					INSERT
					INTO	email__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in EmailInit::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}
				$GLOBALS['_database']->CommitTrans();
			}
		}
	}
?>
