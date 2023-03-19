<?php
	namespace Product\Item;

	class Metadata Extends \BaseMetadataClass {
		public function __construct() {
			$this->_tableName = 'product_metadata';
			$this->_tableIDColumn = 'id';
			$this->_tableMetaFKColumn = 'product_id';
			$this->_tableMetaKeyColumn = 'key';
			$this->_tableMetaValueColumn = 'value';
		}
	}