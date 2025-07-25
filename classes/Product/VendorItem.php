<?php

namespace Product;

class VendorItem extends \BaseXREF {
	public $vendor_id;
	public $product_id;
	public $price;
	public $minimum_order;
	public $pack_quantity;
	public $pack_unit;
	public $price_break_quantity_1;
	public $price_at_quantity_1;
	public $price_break_quantity_2;
	public $price_at_quantity_2;

	/** @method get($vendor_id,$item_id)
	 * Fetches a vendor item by vendor ID and item ID.
	 * @param int $vendor_id
	 * @param int $item_id
	 * @return \Product\VendorItem|null
	 */
	public function get($vendor_id, $item_id) {
		$this->clearError();

		$database = new \Database\Service();
		$query = "
			SELECT *
			FROM product_vendor_items
			WHERE vendor_id = ?
			AND product_id = ?
		";
		$database->AddParam($vendor_id);
		$database->AddParam($item_id);
		$rs = $database->Execute($query);
		if (!$rs) {
			$this->SQLError($database->ErrorMsg());
			return null;
		}
		$object = $rs->FetchNextObject();
		$this->product_id = $object->product_id;
		$this->vendor_id = $object->vendor_id;
		$this->price = $object->cost;
		$this->minimum_order = $object->minimum_order;
		$this->pack_quantity = $object->pack_quantity;
		$this->pack_unit = $object->pack_unit;
		$this->price_break_quantity_1 = $object->price_break_quantity_1;
		$this->price_at_quantity_1 = $object->price_at_quantity_1;
		$this->price_break_quantity_2 = $object->price_break_quantity_2;
		$this->price_at_quantity_2 = $object->price_at_quantity_2;
		return $this;
	}

	/** @method update($parameters)
	 * Updates the vendor item with the given parameters.
	 * @param array $parameters
	 * @return bool
	 */
	public function update($parameters): bool {
		$this->clearError();
		$database = new \Database\Service();

		if (!isset($this->product_id) || !isset($this->vendor_id)) {
			$this->error("Product ID and Vendor ID are required for update.");
			return false;
		}
		$query = "
			UPDATE product_vendor_items
			SET vendor_id = vendor_id";

		$changes = false;
		$change_description = "";

		if (!empty($parameters['cost'])) {
			if ($this->price !== $parameters['cost']) {
				$query .= ", cost = ?";
				$database->AddParam($parameters['cost']);
				$changes = true;
				$change_description .= "Price changed from {$this->price} to {$parameters['cost']}. ";
			}
		}
		if (!empty($parameters['minimum_order'])) {
			if ($this->minimum_order !== $parameters['minimum_order']) {
				$query .= ", minimum_order = ?";
				$database->AddParam($parameters['minimum_order']);
				$changes = true;
				$change_description .= "Minimum order changed from {$this->minimum_order} to {$parameters['minimum_order']}. ";
			}
		}
		if (!empty($parameters['pack_quantity'])) {
			if ($this->pack_quantity !== $parameters['pack_quantity']) {
				$query .= ", pack_quantity = ?";
				$database->AddParam($parameters['pack_quantity']);
				$changes = true;
				$change_description .= "Pack quantity changed from {$this->pack_quantity} to {$parameters['pack_quantity']}. ";
			}
		}
		if (!empty($parameters['pack_unit'])) {
			if ($this->pack_unit !== $parameters['pack_unit']) {
				$query .= ", pack_unit = ?";
				$database->AddParam($parameters['pack_unit']);
				$changes = true;
				$change_description .= "Pack unit changed from {$this->pack_unit} to {$parameters['pack_unit']}. ";
			}
		}
		if (!empty($parameters['price_break_quantity_1'])) {
			if ($this->price_break_quantity_1 !== $parameters['price_break_quantity_1']) {
				$query .= ", price_break_quantity_1 = ?";
				$database->AddParam($parameters['price_break_quantity_1']);
				$changes = true;
				$change_description .= "Price break quantity 1 changed from {$this->price_break_quantity_1} to {$parameters['price_break_quantity_1']}. ";
			}
		}
		if (!empty($parameters['price_at_quantity_1'])) {
			if ($this->price_at_quantity_1 !== $parameters['price_at_quantity_1']) {
				$query .= ", price_at_quantity_1 = ?";
				$database->AddParam($parameters['price_at_quantity_1']);
				$changes = true;
				$change_description .= "Price at quantity 1 changed from {$this->price_at_quantity_1} to {$parameters['price_at_quantity_1']}. ";
			}
		}
		if (!empty($parameters['price_break_quantity_2'])) {
			if ($this->price_break_quantity_2 !== $parameters['price_break_quantity_2']) {
				$query .= ", price_break_quantity_2 = ?";
				$database->AddParam($parameters['price_break_quantity_2']);
				$changes = true;
				$change_description .= "Price break quantity 2 changed from {$this->price_break_quantity_2} to {$parameters['price_break_quantity_2']}. ";
			}
		}
		if (!empty($parameters['price_at_quantity_2'])) {
			if ($this->price_at_quantity_2 !== $parameters['price_at_quantity_2']) {
				$query .= ", price_at_quantity_2 = ?";
				$database->AddParam($parameters['price_at_quantity_2']);
				$changes = true;
				$change_description .= "Price at quantity 2 changed from {$this->price_at_quantity_2} to {$parameters['price_at_quantity_2']}. ";
			}
		}
		$query .= "
			WHERE vendor_id = ?
			AND product_id = ?
		";
		$database->AddParam($this->vendor_id);
		$database->AddParam($this->product_id);

		if (! $changes) {
			return true; // No changes to update
		}
		$rs = $database->Execute($query);
		if (!$rs) {
			$this->SQLError($database->ErrorMsg());
			return false;
		}

		// audit the update event
		$auditLog = new \Site\AuditLog\Event();
		$auditLog->add(array(
			'instance_id' => $parameters["product_id"],
			'description' => 'Changes for vendor '.$parameters["vendor_id"].': '.$change_description,
			'class_name' => 'Product\VendorItem',
			'class_method' => 'update'
		));	
		if ($auditLog->error()) {
			$this->error("Failed to log audit event: ".$auditLog->error());
			print_r($auditLog->error());
			return false;
		}
		return true;
	}

	/** @method vendor()
	 * Returns the vendor associated with this item.
	 * @return \Product\Vendor|null
	 */
	public function vendor() {
		$vendor = new \Product\Vendor();
		$vendor->id = $this->vendor_id;
		return $vendor;
	}

	/** @method item()
	 * Returns the item associated with this vendor item.
	 * @return \Product\Item|null
	 */
	public function item() {
		$item = new \Product\Item();
		$item->id = $this->product_id;
		return $item;
	}
}