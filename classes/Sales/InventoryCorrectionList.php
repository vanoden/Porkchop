<?php
	namespace Sales;

	class InventoryCorrectionList extends DocumentList {
		public function __construct() {
			$this->document_type = 'INVENTORY_CORRECTION';
			$this->_modelName = '\Sales\InventoryCorrection';
			parent::__construct();
		}
	}