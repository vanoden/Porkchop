<?php

namespace Product;

class Vendor Extends \Register\Organization {
	/** @method items()
	 * Returns a list of products associated with this vendor.
	 * @return \Product\ItemList
	 */
	public function items() {
		$itemList = new \Product\ItemList();
		$items = $itemList->find();
		$vendorItems = [];
		foreach ($items as $item) {
			if ($item->hasVendor($this->id)) {
				$vendorItems[] = $item;
			}
		}
		return $vendorItems;
	}

	/** @method hasItem(item)
	 * Checks if this vendor has a specific item.
	 * @param \Product\Item $item
	 * @return bool
	 */
	public function hasItem($item) {
		$items = $this->items();
		foreach ($items as $i) {
			if ($i->id === $item->id) {
				return true;
			}
		}
		return false;
	}

	/** @method item(item)
	 * Returns the item associated with this vendor.
	 * @param \Product\Item $item
	 * @return \Product\Item|null
	 */
	public function item($item) {
		$this->clearError();

		$item = new \Product\VendorItem();
		$item->get($this->id, $item->product_id);
		if ($item->error()) {
			$this->SQLError($item->error());
			return null;
		}
		return $item;
	}
}