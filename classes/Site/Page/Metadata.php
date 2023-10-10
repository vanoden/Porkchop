<?php
	namespace Site\Page;

    use Aws\Iam\Exception\DeleteConflictException;
    use Elasticsearch\Endpoints\Indices\Alias\Delete;

	class Metadata Extends \BaseMetadataClass {

        public $id;
		public $template;
		public $page_id;
		public $key;
		public $value;

        public $_fields = array('id','template','page_id','key','value');

		public function __construct() {
		
			$this->_tableName = 'page_metadata';
			$this->_tableMetaFKColumn = 'page_id';
			$this->_tableMetaKeyColumn = 'key';
			$this->_tableMetaValueColumn = 'value';

			if (func_num_args() == 2) {
				$this->get(func_get_arg(0),func_get_arg(1));
			} elseif (func_num_args() == 1 && is_numeric(func_get_arg(0))) {
				$this->id = func_get_arg(0);
				$this->details();
			}
		}

        public function addByParameters($parameters): bool {
            $this->clearError();

            $database = new \Database\Service();

            $add_query = "
                INSERT INTO `$this->_tableName`
                (
                    `page_id`,
                    `key`,
                    `value`
                )
                VALUES
                (
                    ?,
                    ?,
                    ?
                )
            ";
            $database->NewQuery($add_query);
            $database->AddParam($parameters['page_id']);
            $database->AddParam($parameters['key']);
            $database->AddParam($parameters['value']);

            $rs = $database->Execute($add_query);
            if (! $rs) {
                $this->SQLError($database->ErrorMsg());
                return false;
            }

            $this->id = $database->Insert_ID();
            return true;
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
                $this->key = $object->$keyColumn;
                $this->value = $object->$valueColumn;
                $this->page_id = $object->page_id;
            }
            else {
                $this->value = null;
            }
            return true;
        }		
		
        public function getByPageIdKey($page_id,$key) {
            $get_object_query = "
                SELECT	id
                FROM	$this->_tableName
                WHERE	page_id = ?
                AND		`key` = ?
            ";

            $rs = $GLOBALS['_database']->Execute($get_object_query,array($page_id,$key));
            if (! $rs) {
                $this->error = $GLOBALS['_database']->ErrorMsg();
                return null;
            }
            list($id) = $rs->FetchRow();
            if ($id > 0) {
                $this->id = $id;
                return $this->details();
            }
            return null;
        }

        /**
         * update by params
         * 
         * @param array $parameters, name value pairs to update object by
         */
        public function update($parameters = []): bool {

			$this->clearError();
			$database = new \Database\Service();

            $updateQuery = "UPDATE `$this->_tableName` SET `$this->_tableIDColumn` = `$this->_tableIDColumn` ";
		    
    	    // unique id is required to perform an update
    	    if (!$this->id) {
        	    $this->error('ERROR: id is required for '.$this->_objectName().' update.');
        	    return false;
    	    }

	        foreach ($parameters as $fieldKey => $fieldValue) {
	            if (in_array($fieldKey, $this->_fields)) {
	               $updateQuery .= ", `$fieldKey` = ?";
	               $database->AddParam($fieldValue);
	            }
	        }
	        
            $updateQuery .= " WHERE	`$this->_tableIDColumn` = ?";
            $database->AddParam($this->id);
            $database->Execute($updateQuery);

			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

            return $this->details();
		}

	}
