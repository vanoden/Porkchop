<?php
	namespace ORM;
	
	class BaseModel Extends \BaseClass {
	    public $values = array();
	    private $_addQuery;

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
        public function update($parameters = array()): bool {
        
            $updateQuery = "UPDATE `$this->_tableName` SET `$this->_tableIDColumn` = `$this->_tableIDColumn` ";
            $this->values = $parameters;

			$database = new \Database\Service();
		    
    	    // unique id is required to perform an update
    	    if (!$this->id) {
        	    $this->error('ERROR: id is required for object update.');
        	    return false;
    	    }

	        foreach ($this->values as $fieldKey => $fieldValue) {
	            if (in_array($fieldKey, $this->_fields)) {
	               $updateQuery .= ", `$fieldKey` = ?";
	               $database->AddParam($fieldValue);
	            }
	        }
            $updateQuery .= " WHERE	`$this->_tableIDColumn` = ?";
            $database->AddParam($this->id);
            $database->execute($updateQuery);

			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
            return $this->details();
		}

        /**
         * add by params
         * 
         * @param array $parameters, name value pairs to add and populate new object by
         */
		public function add($parameters = array()) {
	
    		$this->_addQuery = "INSERT INTO `$this->_tableName` ";
			$bindParams = array();
			$bindFields = array();		
            $this->values = $parameters;
	        foreach ($this->values as $fieldKey => $fieldValue) {
	            if (in_array($fieldKey, $this->_fields)) {
    	            array_push($bindFields, $fieldKey);
    	            array_push($bindParams, $fieldValue);
	            }
	        }
	        $this->_addQuery .= '(`'.implode('`,`',$bindFields).'`';
            $this->_addQuery .= ") VALUES (" . trim ( str_repeat("?,", count($bindParams)) ,',') . ")";
			query_log($this->_addQuery,$bindParams);
            $this->execute($this->_addQuery, $bindParams);
			if ($this->error()) return false;

			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}

        /**
         * get max value from a column in the current DB table
         */
		public function maxColumnValue($column='id') {
			$getMaxValueQuery = "SELECT MAX(`$column`) FROM `$this->_tableName`";
			$rs = $this->execute($getMaxValueQuery, array());
            if ($rs) {
                list($value) = $rs->FetchRow();
                return $value;
            } else {
                $this->error("ERROR: no columns found for max value.");
                return false;
            }
		}

		public function deleteByKey($keyName) {
			$deleteObjectQuery = "DELETE FROM `$this->_tableName` WHERE `$this->_tableUKColumn` = ?";
			$this->execute($deleteObjectQuery,array($keyName));
			if ($this->error()) return false;
			return true;
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
            return $rs;
		}
	}
