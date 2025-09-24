<?php
	namespace Sales;

	class PurchaseOrderList Extends DocumentList {
		public function __construct() {
			$this->document_type = 'PURCHASE_ORDER';
			$this->_modelName = '\Sales\PurchaseOrder';
			parent::__construct();
		}
	}