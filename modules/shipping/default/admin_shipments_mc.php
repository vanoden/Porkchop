<?php
	$page = new \Site\Page();
	$page->requirePrivilege('see shipments');

	$shipmentList = new \Shipping\ShipmentList();
	$shipments = $shipmentList->find();
