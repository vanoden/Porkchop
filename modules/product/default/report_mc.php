<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage products');

	$productlist = new \Product\ItemList();
	$products = $productlist->find();
	if ($productlist->error()) $page->addError($productlist->error());
