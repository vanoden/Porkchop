<?php

namespace Product;

class VendorList Extends \Register\OrganizationList {
	/** @method find()
	 * Returns a list of all vendors.
	 * @return \Product\Vendor[]
	 */
	public function findAdvanced($parameters, $advanced, $controls): array {
		$parameters['is_vendor'] = true;

		return parent::findAdvanced($parameters, $advanced, $controls);
	}
}