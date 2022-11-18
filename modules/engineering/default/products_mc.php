<?php
	$page = new \Site\Page();
	$page->requirePrivilege('see engineering products');

	$productlist = new \Engineering\ProductList();
	$products = $productlist->find();
