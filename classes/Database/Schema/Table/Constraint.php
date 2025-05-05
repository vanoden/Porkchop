<?php
	namespace Database\Schema\Table;

	class Constraint {
		private $_error;
		public $name;
		public $type;
		public $schema;
		public $table;
		public $auto_increment;

		public function __construct($schema = null, $table = null, $name = null) {
			$this->schema = $schema;
			$this->table = $table;
			$this->name = $name;
			$this->type = null;
			$this->auto_increment = false;
			$this->_error = null;
		}

		public function name($name = null) {
			if (! empty($name)) {
				if (preg_match('/^\w[\w\_]*$/',$name)) {
					$this->name = $name;
				}
				else {
					$this->_error = "Invalid name";
				}
			}
			return $this->name;
		}
		public function drop() {
			if ($this->type == 'FOREIGN KEY') {
				$drop_constraint_query = "
					ALTER TABLE `".$this->schema."`.`".$this->table."` DROP FOREIGN KEY `".$this->name."`";

				$GLOBALS['_database']->Execute($drop_constraint_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->_error = "SQL Error dropping FOREIGN KEY `".$this->name."` from ".$this->table." in Database::Table::Constraint::drop(): ".$GLOBALS['_database']->ErrorMsg().": $drop_constraint_query";
					return false;
				}
			}
			else {
				$drop_constraint_query = "
					ALTER TABLE `".$this->schema."`.`$this->`".$this->table."` DROP INDEX `".$this->name."`";
				$GLOBALS['_database']->Execute($drop_constraint_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->_error = "SQL Error dropping INDEX `".$this->name."` from ".$this->table." in Database::Table::Constraint::drop(): ".$GLOBALS['_database']->ErrorMsg().": $drop_constraint_query";
					return false;
				}
			}
			return true;
		}

		public function columns() {
			$get_columns_query = "
				SELECT	*
				FROM	information_schema.key_column_usage
				WHERE	table_schema = ?
				AND		constraint_name = ?
				AND		table_name = ?";

			$rs = $GLOBALS['_database']->Execute($get_columns_query,array($this->schema,$this->name,$this->table));
			if (! $rs) {
				$this->_error = "SQL Error getting columns for constraint `".$this->name."` from ".$this->table." in Database::Table::Constraint::columns(): ".$GLOBALS['_database']->ErrorMsg().": $get_columns_query";
				return false;
			}
			$columns = array();
			while ($object = $rs->FetchNextObject(false)) {
				$columns[] = $object->COLUMN_NAME;
			}
			return $columns;
		}

		public function error() {
			return $this->_error;
		}
	}
