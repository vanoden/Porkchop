<?php
    $page = new \Site\Page();
	$page->fromRequest();
	$page->requireRole('support user');
	
    $productList = new \Product\ItemList();
	$productsAvailable = $productList->find(array('type' => 'unique','status' => 'active'));

    // form values
    $productId = 0;
    $selectedProduct = 0;
    $purchased = '';
    $distributor = '';
    $serialNumber = '';

    // if form submit
	if ($_REQUEST['btnSubmit']) {
        $productId = $_REQUEST['productId'];
        $selectedProduct = $_REQUEST['productId'];
        $purchased = $_REQUEST['purchased'];
        $distributor = $_REQUEST['distributor'];
        $serialNumber = $_REQUEST['serialNumber'];
	}
