<?php
	namespace Sales;

	class PurchaseOrder Extends Document {

		public function __construct($id = null) {
			$this->type = DocumentType::PURCHASE;
			parent::__construct($id);
		}

		public function purchaseOrderNumber(): string {
			return $this->local_document_number;
		}

		public function salesOrderNumber(): string {
			return $this->remote_document_number;
		}
	}