<?php
	$page = new \Site\Page();
	$page->requireRole('administrator');

	$productlist = new \Product\ItemList();
	$products = $productlist->find();
	if ($productlist->error()) $page->addError($productlist->error());
