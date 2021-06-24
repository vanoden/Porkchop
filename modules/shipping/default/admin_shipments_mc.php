<?php
	$page = new \Site\Page();
	$page->requireRole('shipping manager');

	$shipmentList = new \Shipping\ShipmentList();
	$shipments = $shipmentList->find();
