<?php
	/** @class Register\Organization\ProvidedProduct
	 * Represents a product provided by an organization. This is used to link organizations to the products they provide.  Products are Products.
	 */
	namespace Register\Organization;

	class ProvidedProduct extends \Product\Product {
		public ?int $organization_id = null;
		public ?int $product_item_id = null;
		public ?string $notes = null;
		public ?float $current_price = null;
		public ?float $pack_quantity = null;
		public ?string $units = null;
		public ?string $date_added = null;

		public function __construct(int $id = 0) {
			parent::__construct($id);
			if ($this->id > 0) {
				$this->details();
			}
		}

		public function details() {
			$this->clearError();
			$database = new \Database\Service();

			$get_details_query = "
				SELECT  organization_id,
						product_item_id,
						notes,
						current_price,
						pack_quantity,
						units,
						date_added
				FROM    organization_products_provided
				WHERE   id = ?
			";
			$database->AddParam($this->id);
			$result = $database->Execute($get_details_query);
			if ($result === false) {
				$this->SQLError("Error fetching details for ProvidedService ID {$this->id}: ".$database->ErrorMsg());
				return false;
			}
			elseif ($result->RecordCount() == 0) {
				$this->error("ProvidedService not found with ID {$this->id}");
				return false;
			}
			else {
				$this->organization_id = $result->fields['organization_id'];
				$this->product_item_id = $result->fields['product_item_id'];
				$this->notes = $result->fields['notes'];
				$this->current_price = $result->fields['current_price'];
				$this->pack_quantity = $result->fields['pack_quantity'];
				$this->units = $result->fields['units'];
				$this->date_added = $result->fields['date_added'];
				return true;
			}
		}

		public function product(): \Product\Item {
			return new \Product\Item($this->product_item_id);
		}

		public function organization(): \Register\Organization {
			return new \Register\Organization($this->organization_id);
		}
	}