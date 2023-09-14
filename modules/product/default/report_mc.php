<?php
    $site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage products');

    $parameters = [];
    if (isset($_REQUEST['btn_search'])) {
        if (!empty($_REQUEST['search'])) {
            if (! preg_match('/^[\w\-\_\.\s]+$/',$_REQUEST['search']) ) $page->addError("Invalid Search String");
            else $parameters['search'] = $_REQUEST['search'];
        }
        if (!empty($_REQUEST['product_type'])) {
            $parameters['type'] = $_REQUEST['product_type'];
        }
        $parameters['status'] = [];
        if (!empty($_REQUEST['status_active'])) {
            $parameters['status'][] = 'ACTIVE';
        }
        if (!empty($_REQUEST['status_hidden'])) {
            $parameters['status'][] = 'HIDDEN';
        }
        if (!empty($_REQUEST['status_deleted'])) {
            $parameters['status'][] = 'DELETED';
        }
    }
    else {
        $_REQUEST['status_active'] = true;
        $_REQUEST['status_hidden'] = false;
        $_REQUEST['status_deleted'] = false;
    }
	$productlist = new \Product\ItemList();
	$products = $productlist->find($parameters);
	if ($productlist->error()) $page->addError($productlist->error());

    $page->title("Products");
    $page->addBreadcrumb("Products");