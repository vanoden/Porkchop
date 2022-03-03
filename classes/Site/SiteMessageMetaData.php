<?php
	namespace Site;

	class SiteMessageMetaData extends \ORM\BaseModel {
	
        public $item_id;
        public $label;
        public $value;
        public $tableName = 'site_messages_metadata';
        public $fields = array('item_id','label','value');
        
        /**
         * construct ORM
         */
		public function __construct($item_id = 0, $label = 'title') {
			if (is_numeric($item_id) && $item_id > 0) {
				$this->item_id = $item_id;
				$this->label = $label;
				$this->details();
			}
		}
        
        /**
         * get object in question
         */
		public function details() {
			$getObjectQuery = "SELECT * FROM $this->tableName WHERE	item_id = ? AND label = ?";
			$rs = $this->execute($getObjectQuery, array($this->item_id, $this->label));
			if (! $rs) {
				$parent = get_called_class();
				$method = debug_backtrace()[1]['function'];
				$this->_error = "SQL Error in $parent::$method: ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if (is_numeric($object->item_id)) {
    			foreach ($this->fields as $field) $this->$field = $object->$field;
			} else {
				foreach ($this->fields as $field) $this->$field = null;
			}
			return true;
		}
        
        /**
         * update by params
         * 
         * @param array $parameters, name value pairs to update object by
         */
        public function update($parameters = array()) {
    
            $this->_updateQuery = "UPDATE `$this->tableName` SET item_id = item_id ";
            $this->values = $parameters;
		    $bindParams = array();
		    
    	    // unique item_id is required to perform an update
    	    if (!$this->item_id) {
        	    $this->_error = 'ERROR: item_id is required for object update.';
        	    return false;
    	    }

	        foreach ($this->values as $fieldKey => $fieldValue) {
	            if (in_array($fieldKey, $this->fields)) {
	               $this->_updateQuery .= ", `$fieldKey` = ?";
	               array_push($bindParams, $fieldValue);
	            }
	        }
            $this->_updateQuery .= " WHERE	item_id = ?";
            array_push($bindParams, $this->id);
            $this->execute($this->_updateQuery, $bindParams);

			if ($this->_error) return false;            
            return $this->details();
		}
	}
