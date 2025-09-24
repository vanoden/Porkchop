<?php
	namespace Sales;

	class SalesOrderList Extends DocumentList {
		public function __construct() {
			$this->document_type = 'SALES_ORDER';
			$this->_modelName = '\Sales\SalesOrder';
			parent::__construct();
		}
	}
