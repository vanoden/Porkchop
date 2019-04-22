<?php
	$page = new \Site\Page();
	$page->requireRole('engineering user');

	$productlist = new \Engineering\ProductList();
	$products = $productlist->find();
