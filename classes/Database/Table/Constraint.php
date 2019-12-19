<?php
	namespace Database\Table;

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
			$drop_constraint_query = "
				ALTER TABLE `".$this->table."` DROP CONSTRAINT `".$this->name."`";
			$GLOBALS['_database']->Execute($drop_constraint_query);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Database::Table::Constraint::drop(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			else {
				return true;
			}
		}

		public function error() {
			return $this->_error;
		}
	}
