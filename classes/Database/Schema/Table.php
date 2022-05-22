<?php
	namespace Database\Schema;

	class Table {
		private $name;
		private $exists = false;
		private $database;
		public $type;
		public $engine;
		public $version;
		public $rows;
		public $data_length;
		public $data_free;
		public $comment;
		public $create_time;
		public $collation;
		public $auto_increment_id;
		private $_error;

		public function __construct($name) {
			if (preg_match('/^\w[\w\_]*$/',$name)) {
				$this->name = $name;
				$this->load();
			}
			else $this->_error = "Invalid name";
		}

		public function load() {
			$get_sql_query = "
				SELECT	*
				FROM	information_schema.tables
				WHERE	table_schema = ?
				AND		table_name = ?";
			$rs = $GLOBALS['_database']->Execute($get_sql_query,array($GLOBALS['_config']->database->schema,$this->name));
			if (! $rs) {
				$this->_error = "SQL Error in Database::Table::load(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if ($rs->recordCount() > 0) {
				app_log("Found table ".$object->TABLE_NAME);
				//$this->name = $object->TABLE_NAME;
				$this->type = $object->TABLE_TYPE;
				$this->engine = $object->ENGINE;
				$this->version = $object->VERSION;
				$this->rows = $object->TABLE_ROWS;
				$this->data_length = $object->DATA_LENGTH;
				$this->data_free = $object->DATA_FREE;
				$this->comment = $object->TABLE_COMMENT;
				$this->create_time = $object->CREATE_TIME;
				$this->collation = $object->TABLE_COLLATION;
				$this->auto_increment_id = $object->AUTO_INCREMENT;
				$this->exists = true;
			}
			else {
				$this->exists = false;
				app_log("Table ".$this->name." not found",'warning');
			}
			return true;
		}

		public function columns() {
			$get_columns_query = "
				SELECT	*
				FROM	information_schema.columns
				WHERE	table_schema = ?
				AND		table_name = ?";
			$rs = $GLOBALS['_database']->Execute($get_columns_query,array($GLOBALS['_config']->database->schema,$this->name));
			if (! $rs) {
				$this->_error = "SQL Error in Database::Table::columns(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$columns = array();
			while ($object = $rs->FetchNextObject(false)) {
				$column = new \Database\Schema\Table\Column($object->TABLE_NAME);
				$column->schema_name = $object->TABLE_SCHEMA;
				$column->table_name = $object->TABLE_NAME;
				$column->name = $object->COLUMN_NAME;
				$column->default = $object->COLUMN_DEFAULT;
				if ($object->IS_NULLABLE == 'YES') $column->nullable = true;
				else $column->nullable = false;
				$column->data_type = $object->DATA_TYPE;
				$column->character_maximum_length = $object->CHARACTER_MAXIMUM_LENGTH;
				$column->numeric_precision = $object->NUMERIC_PRECISION;
				$column->numeric_scale = $object->NUMERIC_SCALE;
				$column->datetime_precision = $object->DATETIME_PRECISION;
				$column->type = $object->COLUMN_TYPE;
				$column->key = $object->COLUMN_KEY;
				$column->comment = $object->COLUMN_COMMENT;
				$column->exists = true;
				array_push($columns,$column);
			}
			return $columns;
		}

		public function has_column($name) {
			$columns = $this->columns();
			foreach ($columns as $column) {
				if ($column->name == $name) return true;
			}
			return false;
		}

		public function disable_keys() {
			$disable_keys_query = "
				ALTER TABLE `".$this->name."` DISABLE KEYS";
			$GLOBALS['_database']->Execute($disable_keys_query);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Database::Table::disable_keys(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			return true;
		}

		public function enable_keys() {
			$enable_keys_query = "
				ALTER TABLE `".$this->name."` ENABLE KEYS";
			$GLOBALS['_database']->Execute($enable_keys_query);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Database::Table::enable_keys(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			return true;
		}

		public function constraints() {
			$get_constraints_query = "
				SELECT	*
				FROM	information_schema.table_constraints
				WHERE	table_schema = ?
				AND		table_name = ?";

			$rs = $GLOBALS['_database']->Execute($get_constraints_query,array($GLOBALS['_config']->database->schema,$this->name));
			if (! $rs) {
				$this->_error = "SQL Error in Database::Table::constraints(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$constraints = array();
			app_log("Found ".$rs->recordCount()." constraints");
			while ($object = $rs->FetchNextObject(false)) {
				app_log("Found constraint ".$object->CONSTRAINT_NAME);
				$constraint = new \Database\Schema\Table\Constraint();
				$constraint->schema = $object->CONSTRAINT_SCHEMA;
				$constraint->name = $object->CONSTRAINT_NAME;
				$constraint->table = $object->TABLE_NAME;
				$constraint->type = $object->CONSTRAINT_TYPE;
				if ($constraint->type == 'PRIMARY KEY' && !empty($this->auto_increment_id)) $constraint->auto_increment = true;
				else $constraint->auto_increment = false;
				array_push($constraints,$constraint);
			}
			return $constraints;
		}

		public function has_constraint($name) {
			$constraints = $this->constraints();
			foreach ($constraints as $constraint) {
				if ($constraint->name == $name) return true;
			}
			return false;
		}

		public function error() {
			return $this->_error;
		}
	}
