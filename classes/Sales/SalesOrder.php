<?php
	namespace Sales;

	class SalesOrder extends Document {
		public function __construct($id = null) {
			$this->type = DocumentType::SALES;
			parent::__construct($id);
		}

		public function salesOrderNumber(): string {
			return $this->local_document_number;
		}

		public function purchaseOrderNumber(): string {
			return $this->remote_document_number;
		}
	}