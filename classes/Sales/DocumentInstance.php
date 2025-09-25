<?php
	namespace Sales;

	class DocumentInstance extends \Document {
		public function __construct($id) {
			// Get Instance of Document
			$database = new \Database\Service();
			$database->AddParam($id);
			$rs = $database->Execute("SELECT `id`,`type` FROM sales_orders WHERE id = ?");
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			if (! $rs) {
				$this->SQLError("Document not found with ID: $id");
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if ($object) {
				if ($object->type == 'SALES') {
					return new \Sales\SalesOrder($id);
				}
				elseif ($object->type == 'PURCHASE') {
					return new \Sales\PurchaseOrder($id);
				}
				elseif ($object->type == 'INVENTORY') {
					return new \Sales\InventoryCorrection($id);
				}
				elseif ($object->type == 'RETURN') {
					return new \Sales\ReturnOrder($id);
				}
				else {
					$this->SQLError("Unknown document type: " . $object->type);
					return false;
				}
			} else {
				$this->SQLError("No document found with ID: $id");
				return false;
			}
		}
	}