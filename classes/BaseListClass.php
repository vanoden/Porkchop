<?php
class BaseListClass extends \BaseClass {
	protected $_count = 0;

	protected $_modelName;

	// Default Sort Controls
	protected $_tableDefaultSortBy;
	protected $_tableDefaultSortOrder;

	public function count() {
		return $this->_count;
	}

	public function incrementCount() {
		$this->_count++;
	}

	public function resetCount() {
		$this->_count = 0;
	}

	public function __call($name, $parameters) {
		if ($name == "find") {
			if (func_num_args() == 2) {
				return $this->findAdvanced($parameters[0], $parameters[1]);
			} else
				return $this->findAdvanced($parameters[0], []);
		} else {
			$this->error("Invalid method '$name'");
			return false;
		}
	}

	public function findAdvanced($parameters = [], $controls = []) {
		$this->clearError();
		$this->resetCount();

		$database = new \Database\Service();
		if (empty($this->_modelName)) {
			$this->error("Model Name Not Set");
			return array();
		}

		$modelName = $this->_modelName;
		$model = new $modelName();

		$tableName = $model->_tableName();
		$tableIDColumn = $model->_tableIDColumn();
		$fields = $model->_fields();

		$find_objects_query = "
				SELECT	`$tableIDColumn`
				FROM	`$tableName`
				WHERE	`$tableIDColumn` = `$tableIDColumn`
			";

		foreach ($parameters as $key => $value) {
			if (in_array($key, $fields)) {
				$find_objects_query .= "
					AND	`$key` = ?";
				$database->AddParam($value);
			}
		}

		if (!empty($controls['sort'])) {
			if (!in_array($controls['sort'], $fields)) {
				$this->error("Invalid sort column name");
				return null;
			}
			$find_objects_query .= "
					ORDER BY `" . $controls['sort'] . "`";
			if (!empty($controls['order']) && preg_match('/^(asc|desc)$/i', $controls['order'])) {
				$find_objects_query .= " " . $controls['order'];
			}
		} elseif (!empty($this->_tableDefaultSortBy)) {
			$find_objects_query .= "
					ORDER BY `" . $this->_tableDefaultSortBy . "`";
			if (!empty($this->_tableDefaultSortOrder)) {
				$find_objects_query .= " " . $this->_tableDefaultSortOrder;
			}
		}

		if (!empty($controls['limit'])) {
			if (is_numeric($controls['limit'])) {
				if (!empty($controls['offset'])) {
					if (is_numeric($controls['offset'])) {
						$find_objects_query .= "
							LIMIT " . $controls['offset'] . "," . $controls['limit'];
					}
				}
				$find_objects_query .= "
					LIMIT " . $controls['limit'];
			} else {
				$this->error("Invalid limit qty");
				return array();
			}
		}
		$objects = array();
		$rs = $database->Execute($find_objects_query);
		if (!$rs) {
			$this->SQLError($database->ErrorMsg());
			return array();
		}

		while (list($id) = $rs->FetchRow()) {
			$object = new $this->_modelName($id);
			array_push($objects, $object);
			$this->incrementCount();
		}

		return $objects;
	}

	// Return Incremented Line Number
	public function nextNumber($parent_id = null) {
		$this->clearError();
		$database = new \Database\Service();
		$modelName = $this->_modelName;
		$model = new $modelName();

		if (empty($model->_tableName() || empty($model->_tableFKColumn() || empty($model->_tableNumberColumn())))) {
			$this->error("Class not configured for Line Numbers");
		}

		$get_number_query = "
				SELECT	max(`$model->_tableNumberColumn`)
				FROM	`$model->_tableName`
			";
		if (isset($parent_id)) {
			$get_number_query .= "
				WHERE	`$model->_tableFKColumn` = ?
				";
			$database->AddParam($parent_id);
		}
		$rs = $database->Execute($get_number_query);
		if (!$rs) {
			$this->SQLError($database->ErrorMsg());
			return null;
		}
		list($last) = $rs->FetchRow();
		if (is_numeric($last))
			return $last + 1;
		else
			return 1;
	}

	public function first($parameters = array()) {
		$objects = $this->findAdvanced($parameters, array('sort' => $this->_tableDefaultSortBy, 'order' => 'asc', 'limit' => 1));
		if ($this->error())
			return null;
		if (count($objects) < 1)
			return null;
		return $objects[0];
	}

	public function last($parameters = array()) {
		$objects = $this->findAdvanced($parameters, array('sort' => $this->_tableDefaultSortBy, 'order' => 'desc', 'limit' => 1));
		if ($this->error())
			return null;
		return end($objects);
	}

	public function validSearchString($string) {
		if (preg_match('/^[\w\-\.\_\s\*]*$/', $string))
			return true;
		else
			return false;
	}
}
