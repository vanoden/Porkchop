<?php
	$page = new \Site\Page();

	$productList = new \Product\ItemList();
	$parent = new \Product\Item();
	
	if (! isset($_REQUEST['parent_code'])) $_REQUEST['parent_code'] = $GLOBALS['_REQUEST_']->query_vars_array[0];
	
	if (isset($_REQUEST['parent_code'])) {
		if ($parent->validCode($_REQUEST['parent_code'])) {
			$parent->get($_REQUEST['parent_code']);
			$_REQUEST['parent_id'] = $parent->id;
		}
		else {
			$page->addError("Invalid parent code");
		}
	}
	
	if (! $_REQUEST['parent_id']) {
		$_REQUEST['parent_id'] = 0;
		$parent->code = '';
		$parent->name = "Our Products";
	}
	$products = $productList->find(array("parent_id" => $_REQUEST['parent_id']));
	if ($productList->error()) $page->addError($productList->error());