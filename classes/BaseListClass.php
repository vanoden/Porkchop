<?php
	class BaseListClass Extends \BaseClass {
	
		protected $_count = 0;

		protected $_modelName;

		// Name of the Table
		protected $_tableName;

		// Name of Autoincrementing id column
		protected $_tableIDColumn = 'id';

		// Array of Fields for Query Filters
		protected $_fields;

		// Foreign Parent ID Column
		protected $_tableFKColumn;

		// Column with Incrementing Line Number within Parent
		protected $_tableNumberColumn;

		// Default Sort Controls
		protected $_tableDefaultSortBy;
		protected $_tableDefaultSortOrder;

        public function count() {
            return $this->_count;
        }

		public function incrementCount() {
			$this->_count ++;
		}

		public function resetCount() {
			$this->_count = 0;
		}	

		public function __call($name,$parameters) {
			if ($name == "find") {
				if (func_num_args() == 2) {
					return $this->findAdvanced($parameters[0],$parameters[1]);
				}
				else return $this->findAdvanced($parameters[0],[]);
			}
			else {
				$this->error("Invalid method");
				return false;
			}
		}

		public function findAdvanced($parameters = [], $controls = []) {
			$this->clearError();
			$this->resetCount();

			$database = new \Database\Service();
			$model = new $this->_modelName();

			$find_objects_query = "
				SELECT	`$model->_tableIDColumn`
				FROM	`$model->_tableName`
				WHERE	`$model->_tableIDColumn` = `$model->_tableIDColumn`
			";

			foreach ($parameters as $key => $value) {
				if (in_array($key,$model->_fields)) {
					$find_objects_query .= "
					AND	`$key` = ?";
					$database->AddParam($value);
				}
			}

			if (!empty($controls['sort'])) {
				if (!in_array($controls['sort'],$model->_fields)) {
					$this->error("Invalid sort column name");
					return null;
				}
				$find_objects_query .= "
					ORDER BY `".$controls['sort']."`";
				if (!empty($controls['order']) && preg_match('/^(asc|desc)$/i',$controls['order'])) {
					$find_objects_query .= " ".$controls['order'];
				}
			}
			elseif (!empty($this->_tableDefaultSortBy)) {
				$find_objects_query .= "
					ORDER BY `".$this->_tableDefaultSortBy."`";
				if (!empty($this->_tableDefaultSortOrder)) {
					$find_objects_query .= " ".$this->_tableDefaultSortOrder;
				}
			}

			if (!empty($controls['limit'])) {
				if (is_numeric($controls['limit'])) {
					if (!empty($controls['offset'])) {
						if (is_numeric($controls['offset'])) {
							$find_objects_query .= "
							LIMIT BY ".$controls['offset'].",".$controls['limit'];
						}
					}
					$find_objects_query .= "
					LIMIT BY ".$parameters['limit'];
				}
				else {
					$this->error("Invalid limit qty");
					return null;
				}
			}

			$events = array();

			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				$events;
			}

			while (list($id) = $rs->FetchRow()) {
				$object = new $this->_modelName($id);
				array_push($events,$object);
				$this->incrementCount();
			}
			return $events;
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
