<?php
	namespace Site\Page;

use Aws\Iam\Exception\DeleteConflictException;
use Elasticsearch\Endpoints\Indices\Alias\Delete;

	class Metadata Extends \BaseMetadataClass {

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
			}
			elseif (func_num_args() == 1 && is_numeric(func_get_arg(0))) {
				$this->id = func_get_arg(0);
				$this->details();
			}
		}
	}
