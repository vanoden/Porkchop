<?php
    class BaseMetadataClass Extends \BaseClass {
        protected $_tableMetaKeyColumn = 'key';
        protected $_tableMetaValueColumn = 'value';
        public $key;
        public $value;

        public function __construct($id = 0, $key = null) {
            if (is_numeric($id) && $id > 0) {
                $this->id = $id;
                if (!empty($key)) {
                    $this->key = $key;
                    $this->details();
                }
            }
        }

        public function getMeta($key) {
            $this->clearError();

            $database = new \Database\Service();

            $get_meta_query = "
                SELECT  `$this->_tableIDColumn`,`$this->_tableMetaKeyColumn`
                FROM    `$this->_tableName`
                WHERE   `$this->_tableIDColumn` = ?
                AND     `$this->_tableMetaKeyColumn` = ?
            ";
            $database->AddParam($this->id);
            $database->AddParam($key);

            $rs = $database->Execute($get_meta_query);
            if (! $rs) {
                $this->SQLError($database->ErrorMsg());
                return false;
            }
            list($id,$key) = $rs->FetchRow();
            if ($id > 0 && !empty($key)) {
                $this->id = $id;
                $this->key = $key;
                return $this->details();
            }
            else {
                $this->error("Data not found");
                return false;
            }
        }

        public function getForId($id) {
            $this->clearError();

            $database = new \Database\Service();

            $get_meta_query = "
                SELECT  `$this->_tableMetaKeyColumn` `key`,
                        `$this->_tableMetaValueColumn` `value`
                FROM    `$this->_tableName`
                ORDER BY `$this->_tableMetaKeyColumn`
            ";

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

        public function details() {
            $this->clearError();

            $database = new \Database\Service();

            $get_key_query = "
                SELECT  *
                FROM    `$this->_tableName`
                WHERE   `$this->_tableIDColumn` = ?
                AND     `$this->_tableMetaKeyColumn` = ?
            ";

            $database->AddParam($this->id);
            $database->AddParam($this->key);

            $rs = $database->Execute($get_key_query);
            if (! $rs) {
                $this->SQLError($database->ErrorMsg());
                return false;
            }
            $object = $rs->FetchNextObject(false);
            if ($object->id) {
                $this->id = $object->$this->_tableIDColumn;
                $this->key = $object->$this->_tableMetaKeyColumn;
                $this->value = $object->$this->_tableMetaValueColumn;
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