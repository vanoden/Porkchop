<?php
/**
 * BaseMetadataClass
 * 
 * This class provides a base implementation for handling metadata associated with various entities.
 * It includes methods for getting, setting, and managing metadata values.
 */
class BaseMetadataClass extends \BaseClass {
    
    /** @var int The ID of the metadata entry */
    public $id;

    /** @var string The name of the database table */
    protected $_tableName;

    /** @var string The name of the ID column in the database table */
    protected $_tableIDColumn = 'id';

    /** @var string The name of the foreign key column in the database table */
    protected $_tableMetaFKColumn;

    /** @var string The name of the key column in the database table */
    protected $_tableMetaKeyColumn = 'key';

    /** @var string The name of the value column in the database table */
    protected $_tableMetaValueColumn = 'value';

    /** @var int The foreign key ID associated with this metadata */
    public $fk_id;

    /** @var string The key of the metadata entry */
    public $key;

    /** @var mixed The value of the metadata entry */
    public $value;

    /**
     * Magic method to handle dynamic method calls
     *
     * @param string $name The name of the method being called
     * @param array $params The parameters passed to the method
     * @return mixed The result of the method call
     */
    public function __call($name, $params) {
        $this->clearError();

        if ($name == 'get' && count($params) == 2) return $this->getWithKeys($params[0], $params[1]);
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

    /**
     * Get metadata with both foreign key ID and key
     *
     * @param int $fk_id The foreign key ID
     * @param string $key The metadata key
     * @return bool True if successful, false otherwise
     */
    public function getWithKeys($fk_id, $key): bool {
        $this->fk_id = $fk_id;
        $this->key = $key;
        return $this->getSimple();
    }

    /**
     * Get metadata with just the key
     *
     * @param string $key The metadata key
     * @return bool True if successful, false otherwise
     */
    public function getWithKey($key): bool {
        $this->key = $key;
        return $this->getSimple();
    }

    /**
     * Get the value of metadata with a given key
     *
     * @param string $key The metadata key
     * @return mixed The metadata value or null if not found
     */
    public function getValWithKey($key) {
        $this->key = $key;
        if ($this->getSimple($key)) return $this->value;
        else return null;
    }

    /**
     * Get the value of the current metadata entry
     *
     * @return mixed The metadata value or null if not found
     */
    public function getVal() {
        if ($this->getSimple()) return $this->value;
        else return null;
    }

    /**
     * Get metadata using the current foreign key ID and key
     *
     * @return bool True if successful, false otherwise
     */
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
            $this->warn("Data not found");
            return false;
        }
        return true;
    }

    /**
     * Set the value of a metadata entry
     *
     * @param mixed $value The value to set
     * @return bool True if successful, false otherwise
     */
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
            INTO    `$this->_tableName`
            (       `$this->_tableMetaFKColumn`,`$this->_tableMetaKeyColumn`,`$this->_tableMetaValueColumn`)
            VALUES
            (       ?,?,?)
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

    /**
     * Delete the current metadata entry
     *
     * @return bool True if successful, false otherwise
     */
    public function drop() {
        if (empty($this->id)) {
            $this->warn("Metadata id not set");
            return false;
        }
        $drop_key_query = "
            DELETE
            FROM    `$this->_tableName`
            WHERE   `$this->_tableIDColumn` = ?
        ";
        query_log($drop_key_query,array($this->id),true);
        $GLOBALS['_database']->Execute($drop_key_query,array($this->id));
        if ($GLOBALS['_database']->ErrorMsg()) {
            $this->SQLError($GLOBALS['_database']->ErrorMsg());
            return false;
        }
        return true;
    }

    /**
     * Get all metadata for a given foreign key ID
     *
     * @param int $id The foreign key ID
     * @return array|false An array of metadata records or false on error
     */
    public function getForId($id) {
        $this->clearError();

        $database = new \Database\Service();

        $get_meta_query = "
            SELECT  `$this->_tableMetaKeyColumn` `key`,
                    `$this->_tableMetaValueColumn` `value`
            FROM    `$this->_tableName`
            WHERE   `$this->_tableMetaFKColumn` = ?
            ORDER BY `$this->_tableMetaKeyColumn`
        ";
        $database->AddParam($id);

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

    /**
     * Get details of the current metadata entry
     *
     * @return bool True if successful, false otherwise
     */
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

    /**
     * Get all unique keys in the metadata table
     *
     * @return array|null An array of keys or null on error
     */
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

    /**
     * Validate a metadata key
     *
     * @param string $string The key to validate
     * @return bool True if valid, false otherwise
     */
    public function validKey($string) {
        if (preg_match('/\.\./',$string)) return false;
        elseif (preg_match('/^\w[\w\-\.\_]*$/',$string)) return true;
        else return false;
    }

    /**
     * Get the value of a specific key for a given ID
     *
     * @param int $id The foreign key ID
     * @param string $key The metadata key to retrieve
     * @return mixed|null The value of the specified key, or null if not found
     */
    public function getKeyById($id, $key) {
        $this->clearError();

        $database = new \Database\Service();

        $get_key_query = "
            SELECT  `$this->_tableMetaValueColumn`
            FROM    `$this->_tableName`
            WHERE   `$this->_tableMetaFKColumn` = $id
            AND     `$this->_tableMetaKeyColumn` = '$key'
        ";

        $rs = $database->Execute($get_key_query);
        if (! $rs) {
            $this->SQLError($database->ErrorMsg());
            return null;
        }

        list($value) = $rs->FetchRow();
        return $value !== false ? $value : null;
    }
}
