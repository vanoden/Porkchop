<?php
	$page = new \Site\Page();
	$page->requireRole('build user');

	$productList = new \Build\ProductList();
	$products = $productList->find();
?>
