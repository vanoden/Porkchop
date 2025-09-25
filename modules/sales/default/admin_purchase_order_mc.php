<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage purchase orders');

	if (!empty($_REQUEST['id'])) {
		if (!is_numeric($_REQUEST['id'])) {
			$page->addError("Invalid purchase order ID format");
			$can_proceed = false;
		} else {
			$purchaseOrder = new \Ordering\PurchaseOrder($_REQUEST['id']);
		}
	} elseif (!empty($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$purchaseOrder = new \Ordering\PurchaseOrder();
		$purchaseOrder->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
	} else {
		$page->addError("Purchase Order Not Specified");
		$can_proceed = false;
	}