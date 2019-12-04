<?php
	namespace Database;

	class Schema {
		public $errno;
		public $error;
		private $info_table;

		public function __construct() {
			app_log(__FUNCTION__." Called");
			$this->info_table = strtolower($this->module)."__info";
			
			// Create Info Table If Not Exists
			$this->initInfoTable();

			// Upgrade to Latest Version
			$this->upgrade();
		}

		private function initInfoTable() {
			app_log(__FUNCTION__." Called");
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();

			if (! in_array($this->info_table,$schema_list)) {
				# Create __info table
				$create_table_query = "
					CREATE TABLE `".$this->info_table."` (
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
		}

		public function version() {
			app_log(__FUNCTION__." Called");

			# Check Current Schema Version
			$get_version_query = "
				SELECT  value
				FROM    `".$this->info_table."`
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

		public function setVersion($version) {
			app_log(__FUNCTION__." Called");
			$this->current_version = $version;
			$update_schema_version = "
				INSERT
				INTO    `".$this->info_table."`
				VALUES  ('schema_version',?)
				ON DUPLICATE KEY UPDATE
					value = ?
			";
			$GLOBALS['_database']->Execute($update_schema_version,array($version,$version));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in ".$this->module."::Schema::upgrade(): ".$GLOBALS['_database']->ErrorMsg();
				app_log($this->error,'error',__FILE__,__LINE__);
				$GLOBALS['_database']->RollbackTrans();
				return undef;
			}
		}

		public function executeSQL($sql,$parameters = array()) {
			app_log(__FUNCTION__." Called");
			$GLOBALS['_database']->Execute($sql,$parameters);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = $GLOBALS['_database']->ErrorMsg();
				$GLOBALS['_database']->RollbackTrans();
				return false;
			}
			return true;
		}

		public function addRoles($roles = array()) {
			# Add Roles
			foreach ($roles as $name => $description) {
				$role = new \Register\Role();
				if (! $role->get($name)) {
					app_log("Adding role '$name'");
					$role->add(array('name' => $name,'description' => $description));
				}
				if ($role->error) {
					$this->_error = "Error adding role '$name': ".$role->error;
					return false;
				}
			}
			return true;
		}

		public function error() {
			return $this->error;
		}
	}
