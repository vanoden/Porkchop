<?php
	class BaseListClass Extends \BaseClass {
		protected $_count = 0;
		
		// Name of the Table
		protected $_tableName;

		// Name of Autoincrementing id column
		protected $_tableIDColumn = 'id';

		// Foreign Parent ID Column
		protected $_tableFKColumn;

		// Column with Incrementing Line Number within Parent
		protected $_tableNumberColumn;

        public function count() {
            return $this->_count;
        }

		public function incrementCount() {
			$this->_count ++;
		}

		public function resetCount() {
			$this->_count = 0;
		}

		// Return Incremented Line Number
		public function nextNumber($parent_id) {
			$this->clearError();

			if (empty($this->_tableName || empty($this->_tableFKColumn || empty($this->_tableNumberColumn)))) {
				$this->error("Class not configured for Line Numbers");
			}

			$database = new \Database\Service();

			$get_number_query = "
				SELECT	max(`$this->_tableNumberColumn`)
				FROM	`$this->_tableName`
				WHERE	`$this->_tableFKColumn` = ?
			";
			$database->AddParam($parent_id);
			$rs = $database->Execute($get_number_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}
			list($last) = $rs->FetchRow();
			if (is_numeric($last)) return $last + 1;
			else return 1;
		}

		public function validSearchString($string) {
			if (preg_match('/^[\w\-\.\_\s\*]*$/',$string)) return true;
			else return false;
		}
	}
