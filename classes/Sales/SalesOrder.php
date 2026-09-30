<?php
	namespace Sales;

	class SalesOrder extends Document {
		public function __construct($id = null) {
			$this->type = DocumentType::SALES->value;
			parent::__construct($id);
		}

		public function salesOrderNumber(): string {
			return $this->local_document_number;
		}

		public function purchaseOrderNumber(): string {
			return $this->remote_document_number;
		}

		public function add($parameters = []): bool {
			$parameters['type'] = DocumentType::SALES->value;
			return parent::add($parameters);
		}
	}