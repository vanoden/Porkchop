<?php

namespace Product;

class Vendor Extends \Register\Organization{
	/** @method items()
	 * Returns a list of products associated with this vendor.
	 * @return \Product\ItemList
	 */
	public function items() {
		$itemList = new \Product\ItemList();
		$itemList->filterByVendor($this->id);
		return $itemList;
	}
}