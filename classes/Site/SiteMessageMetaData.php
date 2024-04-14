<?php
	namespace Site;

	class SiteMessageMetaData extends \BaseModel {
	
        public $item_id;
        public $label;
        public $value;
        
        /**
         * construct site_messages_metadata
         */
		public function __construct($item_id = 0, $label = 'title') {
			$this->_tableName = 'site_messages_metadata';
			$this->_tableIDColumn = 'item_id';
			$this->_addFields(array('item_id','label','value'));

			if (is_numeric($item_id) && $item_id > 0) {
				$this->item_id = $item_id;
				$this->label = $label;
				$this->details();
			}
			parent::__construct($item_id);
		}
        
        /**
         * get object in question
         */
		public function details(): bool {
			$getObjectQuery = "SELECT * FROM $this->_tableName WHERE	`$this->_tableIDColumn` = ? AND label = ?";
			$rs = $this->execute($getObjectQuery, array($this->item_id, $this->label));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if (is_numeric($object->item_id)) {
    			foreach ($this->_fields as $field) $this->$field = $object->$field;
			} else {
				foreach ($this->_fields as $field) $this->$field = null;
			}
			return true;
		}

        /**
         * update by params
         * 
         * @param array $parameters, name value pairs to update object by
         */
        public function update($parameters = []): bool {

            $updateQuery = "UPDATE `$this->_tableName` SET `$this->_tableIDColumn` = `$this->_tableIDColumn` ";
            $this->values = $parameters;
		    $bindParams = array();
		    
    	    // unique item_id is required to perform an update
    	    if (!$this->item_id) {
        	    $this->error('ERROR: item_id is required for object update.');
        	    return false;
    	    }

	        foreach ($this->values as $fieldKey => $fieldValue) {
	            if (in_array($fieldKey, $this->_fields)) {
	               $updateQuery .= ", `$fieldKey` = ?";
	               array_push($bindParams, $fieldValue);
	            }
	        }
            $updateQuery .= " WHERE	`$this->_tableIDColumn` = ?";
            array_push($bindParams, $this->id);
            $this->execute($updateQuery, $bindParams);

			if ($this->error()) return false;  

			// audit the update event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Updated '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'update'
			));

            return $this->details();
		}
	}
