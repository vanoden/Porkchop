<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage product builds');

	$productList = new \Build\ProductList();
	$products = $productList->find();
