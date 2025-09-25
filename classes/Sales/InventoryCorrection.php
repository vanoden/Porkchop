<?php
	namespace Sales;

	class InventoryCorrection Extends \Sales\Document {
		public function __construct() {
			$this->type = DocumentType::INVENTORY;
			parent::__construct();
		}
	}