<?php
	namespace Database;

	class BaseSchema {
		public $errno;
		public $error;
		public $module;
		public $infoTable;
		public $infoKey;

		public function __construct() {
			if (! isset($this->infoTable))
				$this->infoTable = strtolower($this->module)."__info";
			if (! isset($this->infoKey))
				$this->infoKey = 'schema_version';
			// Create Info Table If Not Exists
			$this->initInfoTable();

			// Upgrade to Latest Version
#			$this->upgrade();
		}

		private function initInfoTable() {
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();

			if (! in_array($this->infoTable,$schema_list)) {
				# Create __info table
				$create_table_query = "
					CREATE TABLE `".$this->infoTable."` (
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
			# Check Current Schema Version
			$get_version_query = "
				SELECT  value
				FROM    `".$this->infoTable."`
				WHERE   label = '".$this->infoKey."'
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
			$this->current_version = $version;
			$update_schema_version = "
				INSERT
				INTO    `".$this->infoTable."`
				VALUES  ('".$this->infoKey."',?)
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
