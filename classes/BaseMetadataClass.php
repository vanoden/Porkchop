<?php
    class BaseMetadataClass Extends \BaseClass {
    
		public $id;
		protected $_tableName;
		protected $_tableIDColumn = 'id';
		protected $_tableMetaFKColumn;
        protected $_tableMetaKeyColumn = 'key';
        protected $_tableMetaValueColumn = 'value';
		public $fk_id;
        public $key;
        public $value;

		public function __call($name,$params) {
			$this->clearError();

			if ($name == 'get' && count($params) == 2) return $this->getWithKeys($params[0],$params[1]);
			elseif ($name == 'get' && count($params) == 1) return $this->getWithKey($params[0]);
			elseif ($name == 'get') return $this->getSimple();
			elseif ($name == 'getValue') {
				if (count($params) == 1) {
					return $this->getValWithKey($params[0]);
				}
				else {
					return $this->getVal();
				}
			}
			else {
				$this->error("Unrecognized method");
				return false;
			}
		}

		// Called with Relative ID AND Key
        public function getWithKeys($fk_id,$key): bool {
			$this->fk_id = $fk_id;
			$this->key = $key;
			return $this->getSimple();
		}

		// Called with Just Key
		public function getWithKey($key): bool {
			$this->key = $key;
			return $this->getSimple();
		}

		// Just Return the Value
		public function getValWithKey($key) {
			$this->key = $key;
			if ($this->getSimple($key)) return $this->value;
			else return null;
		}

		// Just Return the Value
		public function getVal() {
			if ($this->getSimple()) return $this->value;
			else return null;
		}

		// Relative ID and Key Already Set
		public function getSimple(): bool {
            $this->clearError();

            $database = new \Database\Service();

            $get_meta_query = "
                SELECT  `$this->_tableIDColumn`,
						`$this->_tableMetaFKColumn`,
						`$this->_tableMetaKeyColumn`,
						`$this->_tableMetaValueColumn`
                FROM    `$this->_tableName`
                WHERE   `$this->_tableMetaFKColumn` = ?
                AND     `$this->_tableMetaKeyColumn` = ?
            ";
            $database->AddParam($this->fk_id);
            $database->AddParam($this->key);

            $rs = $database->Execute($get_meta_query);
            if (! $rs) {
                $this->SQLError($database->ErrorMsg());
                return false;
            }
            list($id,$fk,$key,$value) = $rs->FetchRow();
            if ($id > 0 && !empty($key)) {
                $this->id = $id;
				$this->fk_id = $fk;
                $this->key = $key;
                $this->value = $value;
            }
            else {
                $this->error("Data not found");
                return false;
            }
			return true;
        }

		public function set($value) {
			$this->clearError();

			if (! is_numeric($this->fk_id)) {
				$this->error("Invalid relative id");
				return false;
			}
			elseif (! isset($this->key)) {
				$this->error("Invalid key name");
				return false;
			}

			$database =  new \Database\Service();

			$set_data_query = "
				REPLACE
				INTO	`$this->_tableName`
				(		`$this->_tableMetaFKColumn`,`$this->_tableMetaKeyColumn`,`$this->_tableMetaValueColumn`)
				VALUES
				(		?,?,?)
			";

			$database->AddParam($this->fk_id);
			$database->AddParam($this->key);
			$database->AddParam($value);
			$database->Execute($set_data_query);

			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			return true;
		}

		public function drop() {
			if (empty($this->id)) {
				$this->error("Metadata id not set");
				return false;
			}
			$drop_key_query = "
				DELETE
				FROM	`$this->_tableName`
				WHERE	`$this->_tableIDColumn` = ?
			";
			query_log($drop_key_query,array($this->id),true);
			$GLOBALS['_database']->Execute($drop_key_query,array($this->id));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			return true;
		}

        public function getForId($id) {
            $this->clearError();

            $database = new \Database\Service();

            $get_meta_query = "
                SELECT  `$this->_tableMetaKeyColumn` `key`,
                        `$this->_tableMetaValueColumn` `value`
                FROM    `$this->_tableName`
				WHERE	`$this->_tableMetaFKColumn` = ?
                ORDER BY `$this->_tableMetaKeyColumn`
            ";
			$database->AddParam($this->fk_id);

			$rs = $database->Execute($get_meta_query);
            if (! $rs) {
                $this->SQLError($database->ErrorMsg());
                return false;
            }

            $records = array();
            while (list($key,$value) = $rs->FetchRow()) {
                $record = array($key,$value);
                array_push($records,$record);
            }
            return $records;
        }

        public function details(): bool {
            $this->clearError();

            $database = new \Database\Service();

            $get_key_query = "
                SELECT  *
                FROM    `$this->_tableName`
                WHERE   `$this->_tableIDColumn` = ?
            ";

            $database->AddParam($this->id);

            $rs = $database->Execute($get_key_query);
            if (! $rs) {
                $this->SQLError($database->ErrorMsg());
                return false;
            }

			// Dereference column names to avoid error
			$idColumn = $this->_tableIDColumn;
			$fkColumn = $this->_tableMetaFKColumn;
			$keyColumn = $this->_tableMetaKeyColumn;
			$valueColumn = $this->_tableMetaValueColumn;

            $object = $rs->FetchNextObject(false);
            if ($object->id) {
                $this->id = $object->$idColumn;
				$this->fk_id = $object->$fkColumn;
                $this->key = $object->$keyColumn;
                $this->value = $object->$valueColumn;
            }
            else {
                $this->value = null;
            }
            return true;
        }

        public function getKeys() {
            $this->clearError();

            $database = new \Database\Service();

            $get_keys_query = "
                SELECT  `$this->_tableMetaKeyColumn`
                FROM    `$this->_tableName`
                GROUP BY `$this->_tableMetaKeyColumn`
            ";
            $rs = $database->Execute($get_keys_query);
            if (! $rs) {
                $this->SQLError($database->ErrorMsg());
                return null;
            }
            $keys = array();
            while(list($key) = $rs->FetchRow()) {
                array_push($keys,$key);
            }
            return $keys;
        }
        public function validKey($string) {
            if (preg_match('/\.\./',$string)) return false;
            elseif (preg_match('/^\w[\w\-\.\_]*$/',$string)) return true;
            else return false;
        }
    }
