<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage products');

	$validationClass = new \Product\Item();


	if ($_REQUEST['product_id']) {
		$item_id = (int)$_REQUEST['product_id'];
	}
	else if ($_REQUEST['product_code']) {
		$item = new \Product\Item();
		if (! $item->get($_REQUEST['product_code'])) {
			$item_id = 0;
			$page->addError('Product not found');
		}
		else {
			$item_id = $item->id;
		}
	}
	// Validate item by query vars
	elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0]) && $validationClass->validCode($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$item = new \Product\Item();
		$item->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
	}
	
	if (! $item->id) {
		$page->addError('Product not found');
	}
	else {
		$parts = $item->getParts();
	}

	$itemList = new \Product\ItemList();
	$items = $itemList->find();