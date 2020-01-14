<?php
	$page = new \Site\Page();
	$page->requireRole('shipping manager');

	$shipment = new \Shipping\Shipment($_REQUEST['id']);

	$vendorList = new \Shipping\VendorList();
	$vendors = $vendorList->find();

	$packages = $shipment->packages();