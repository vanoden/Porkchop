<?php
	namespace Site;

	class SiteMessageMetaData extends \ORM\BaseModel {
	
        public $item_id;
        public $label;
        public $value;
        public $tableName = 'site_messages_metadata';
        public $fields = array('item_id','label','value');
        
        /**
         * update by params
         * 
         * @param array $parameters, name value pairs to update object by
         */
        public function update($parameters = array()) {
    
            $this->_updateQuery = "UPDATE `$this->tableName` SET item_id = item_id ";
            $this->values = $parameters;
		    $bindParams = array();
		    
    	    // unique id is required to perform an update
    	    if (!$this->id) {
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
