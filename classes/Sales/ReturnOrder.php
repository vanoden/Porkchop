<?php
	namespace Sales;

	class ReturnOrder extends Document {
		public function __construct($id = null) {
			$this->type = DocumentType::RETURN;
			parent::__construct($id);
		}
	}