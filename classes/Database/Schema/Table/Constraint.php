<?php
	namespace Database\Schema\Table;

	class Constraint {
		private $_error;
		public $name;
		public $type;
		public $schema;
		public $table;

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
					ALTER TABLE `".$this->table."` DROP FOREIGN KEY `".$this->name."`";

				$GLOBALS['_database']->Execute($drop_constraint_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->_error = "SQL Error dropping FOREIGN KEY `".$this->name."` from ".$this->table." in Database::Table::Constraint::drop(): ".$GLOBALS['_database']->ErrorMsg().": $drop_constraint_query";
					return false;
				}
			}
			else {
				$drop_constraint_query = "
					ALTER TABLE `".$this->table."` DROP INDEX `".$this->name."`";
				$GLOBALS['_database']->Execute($drop_constraint_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->_error = "SQL Error dropping INDEX `".$this->name."` from ".$this->table." in Database::Table::Constraint::drop(): ".$GLOBALS['_database']->ErrorMsg().": $drop_constraint_query";
					return false;
				}
			}
			return true;
		}

		public function error() {
			return $this->_error;
		}
	}
