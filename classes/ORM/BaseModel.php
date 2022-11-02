<?php
	namespace ORM;
	
	class BaseModel Extends \BaseClass {

	    protected $fields = array();
	    public $values = array();	
	    protected $tableName;
	    private $_updateQuery;
	    private $_addQuery;
		protected $ukColumn = 'code';
	
        /**
         * construct ORM
         */
		public function __construct($id = 0) {
			if (is_numeric($id) && $id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

        /**
         * update by params
         * 
         * @param array $parameters, name value pairs to update object by
         */
        public function update($parameters = array()) {
        
            $this->_updateQuery = "UPDATE `$this->tableName` SET id = id ";
            $this->values = $parameters;
		    $bindParams = array();
		    
    	    // unique id is required to perform an update
    	    if (!$this->id) {
        	    $this->_error = 'ERROR: id is required for object update.';
        	    return false;
    	    }

	        foreach ($this->values as $fieldKey => $fieldValue) {
	            if (in_array($fieldKey, $this->fields)) {
	               $this->_updateQuery .= ", `$fieldKey` = ?";
	               array_push($bindParams, $fieldValue);
	            }
	        }
            $this->_updateQuery .= " WHERE	id = ?";
            array_push($bindParams, $this->id);
            $this->execute($this->_updateQuery, $bindParams);

			if ($this->_error) return false;
            
            return $this->details();
		}

        /**
         * add by params
         * 
         * @param array $parameters, name value pairs to add and populate new object by
         */
		public function add($parameters = array()) {
		
    		$this->_addQuery = "INSERT INTO `$this->tableName` ";
			$bindParams = array();
			$bindFields = array();		
            $this->values = $parameters;
	        foreach ($this->values as $fieldKey => $fieldValue) {
	            if (in_array($fieldKey, $this->fields)) {
    	            array_push($bindFields, $fieldKey);
    	            array_push($bindParams, $fieldValue);
	            }
	        }
	        $this->_addQuery .= '(`'.implode('`,`',$bindFields).'`';
            $this->_addQuery .= ") VALUES (" . trim ( str_repeat("?,", count($bindParams)) ,',') . ")";
			query_log($this->_addQuery,$bindParams);
            $this->execute($this->_addQuery, $bindParams);

			if ($this->_error) return false;

			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}
	
        /**
         * get object in question
         */
		public function details() {
			$getObjectQuery = "SELECT * FROM $this->tableName WHERE	id = ?";
			$rs = $this->execute($getObjectQuery, array($this->id));
			if (! $rs) {
				$parent = get_called_class();
				$method = debug_backtrace()[1]['function'];
				$this->_error = "SQL Error in $parent::$method: ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if (is_numeric($object->id)) {
    			foreach ($this->fields as $field) $this->$field = $object->$field;
			} else {
				foreach ($this->fields as $field) $this->$field = null;
			}
			return true;
		}

        /**
         * get object by code
         *
         * @param string $code
         * @param string $columnName, which column we search for the code value by
         */
		public function get($code) {
			$getObjectQuery = "SELECT id FROM `$this->tableName` WHERE `$this->ukColumn` = ?";
			$rs = $this->execute($getObjectQuery, array($code));
            if ($rs) {
                list($id) = $rs->FetchRow();
                if ($id) {
                    $this->id = $id;
                    return $this->details();
                }
            }
            $this->_error = "ERROR: no records found for this value.";
            return false;
		}

		public function delete() {
			$deleteObjectQuery = "DELETE FROM `$this->tableName` WHERE `id` = ?";
			$this->execute($deleteObjectQuery,array($this->id));
			if ($this->_error) return false;
			return true;
		}

        /**
         * get max value from a column in the current DB table
         */
		public function maxColumnValue($column='id') {
			$getMaxValueQuery = "SELECT MAX(`$column`) FROM `$this->tableName`";
			$rs = $this->execute($getMaxValueQuery, array());
            if ($rs) {
                list($value) = $rs->FetchRow();
                return $value;
            } else {
                $this->_error = "ERROR: no columns found for max value.";
                return false;
            }
		}

        /**
         * get the error that may have happened on the DB level
         *
         * @params string $query, prepared statement query
         * @params array $params, values to populated prepared statement query
         */		
		protected function execute($query, $params) {
			$rs = $GLOBALS["_database"]->Execute($query,$params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in ORM::BaseModel::execute(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
            return $rs;
		}
	}
