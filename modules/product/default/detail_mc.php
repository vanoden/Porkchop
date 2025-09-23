<?php
	$site = new \Site();
	$page = $site->page();

	$product = new \Product\Item();
	
	if (! isset($_REQUEST['product_code'])) $_REQUEST['product_code'] = $GLOBALS['_REQUEST_']->query_vars_array[0];
	
	if (isset($_REQUEST['product_code'])) {
		if ($product->validCode($_REQUEST['product_code'])) {
			$product->get($_REQUEST['product_code']);
			$_REQUEST['product_id'] = $product->id;
		}
		else {
			$page->addError("Invalid product code");
		}
	}
