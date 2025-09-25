<?php
	namespace Sales;

	class ReturnOrderList extends DocumentList {
		public function __construct() {
			$this->_modelName = '\Sales\ReturnOrder';
			$this->document_type = 'RETURN';
			parent::__construct();
		}
	}
