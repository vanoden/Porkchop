<?php

namespace Product;

class VendorList Extends \Register\OrganizationList {
	/** @method find()
	 * Returns a list of all vendors.
	 * @return \Product\Vendor[]
	 */
	public function find($parameters): array {
		$parameters['is_vendor'] = true;
		$organizations = parent::find($parameters);
		$vendors = [];
		foreach ($organizations as $org) {
			$vendor = new Vendor($org->id);
			$vendors[] = $vendor;
		}
		return $vendors;
	}
}