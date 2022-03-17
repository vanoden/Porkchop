<?php
	$page = new \Site\Page();
	$page->requireRole('shipping manager');
	$shipment = new \Shipping\Shipment($_REQUEST['id']);

	if (!empty($_REQUEST['btn_shipped'])) {
		$shipment->ship(array('vendor_id' => $_REQUEST['vendor_id']));
	}
	elseif (!empty($_REQUEST['btn_lost'])) {
		$shipment->update(array('status' => 'LOST'));
	}
	elseif (!empty($_REQUEST['btn_received'])) {
		$received = true;
		foreach ($shipment->packages() as $package) {
			if ($package->status != 'RECEIVED') {
				$page->addError("Package ".$package->number." not received");
				$received = false;
			}
		}
		$shipment->update(array('status' => 'RECEIVED'));
	}

	$vendorList = new \Shipping\VendorList();
	$vendors = $vendorList->find();
	$packages = $shipment->packages();
	if (empty($shipment->vendor_id)) $shippingVendor = 'Not provided';
	else $shippingVendor = $shipment->vendor();