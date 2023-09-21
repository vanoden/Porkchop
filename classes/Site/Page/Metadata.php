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
		
	}
