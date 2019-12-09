<?php
	namespace ORM;
	
	class BaseModel {
	
	    public $tableName;
	    public $updateQuery;
	    public $addQuery;
	    public $fields = array();
	    public $values = array();
	    private $_error;
	
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

            $this->updateQuery = "UPDATE `$this->tableName` SET id = id ";
            $this->values = $parameters;
		    $bindParams = array();
		    
    	    // unique id is required to perform an update
    	    if (!$this->id) {
        	    $this->_error = 'ERROR: id is required for object update.';
        	    return false;
    	    }

	        foreach ($this->values as $fieldKey => $fieldValue) {
	            if (in_array($fieldKey, $this->fields)) {
	               $this->updateQuery .= ", `$fieldKey` = ?";
	               array_push($bindParams, $fieldValue);
	            }
	        }
            $this->updateQuery .= " WHERE	id = ?";
            array_push($bindParams, $this->id);
            $this->execute($this->updateQuery, $bindParams);
            
            return $this->details();
		}

        /**
         * add by params
         * 
         * @param array $parameters, name value pairs to add and populate new object by
         */
		public function add($parameters = array()) {
		
    		$this->addQuery = "INSERT INTO `$this->tableName` ";
			$bindParams = array();
			$bindFields = array();		
            $this->values = $parameters;
	        foreach ($this->values as $fieldKey => $fieldValue) {
	            if (in_array($fieldKey, $this->fields)) {
    	            array_push($bindFields, $fieldKey);
    	            array_push($bindParams, $fieldValue);
	            }
	        }
	        $this->addQuery .= '(`'.implode('`,`',$bindFields).'`';
            $this->addQuery .= ") VALUES (" . trim ( str_repeat("?,", count($bindParams)) ,',') . ")";
            $this->execute($this->addQuery, $bindParams);
            
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}
	
        /**
         * get object in question
         */
		public function details() {
			$getObjectQuery = "SELECT * FROM $this->tableName WHERE	id = ?";
			$rs = $this->execute($getObjectQuery, array($this->id));
            $object = $rs->FetchNextObject(false);
			if (is_numeric($object->id)) {
    			foreach ($this->fields as $field) $this->$field = $object->$field;
			}
			else {
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
		public function get($code, $columnName='code') {
			$getObjectQuery = "SELECT id FROM `$this->tableName` WHERE `$columnName` = ?";
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

        /**
         * get the error that may have happened on the DB level
         *
         * @params string $query, prepared statement query
         * @params array $params, values to populated prepared statement query
         */		
		protected function execute($query, $params) {
			$rs = $GLOBALS["_database"]->Execute($query,$params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = print_r(debug_backtrace(), true) . $GLOBALS['_database']->ErrorMsg();
				return false;
			}
            return $rs;
		}
	   
        /**
         * get the error that may have happened on the DB level
         */
		public function error() {
			return $this->_error;
		}
	}
