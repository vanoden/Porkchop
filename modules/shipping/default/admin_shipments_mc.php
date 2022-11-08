<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage shipments');

	$shipmentList = new \Shipping\ShipmentList();
	$shipments = $shipmentList->find();
	if ($shipmentList->error()) {
		$page->addError($shipmentList->error());
	}