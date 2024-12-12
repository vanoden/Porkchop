<?php
    $site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage products');

    $parameters = [];

	// paginate results
    $pagination = new \Site\Page\Pagination();
    $pagination->forwardParameters(array('search','product_type','status_active','status_hidden','status_deleted','sort_field','sort_direction'));

    // For Validation
    $product = new \Product\Item();

    if (isset($_REQUEST['btn_search'])) {
        if (!empty($_REQUEST['search'])) {
            if (! $product->validSearch($_REQUEST['search'])) $page->addError("Invalid Search String");
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
		$_REQUEST['product_type'] = '';
		$_REQUEST['search'] = '';
    }

	$productlist = new \Product\ItemList();
    $totalRecords = $productlist->count($parameters);
	if (!isset($_REQUEST['pagination_start_id'])) $_REQUEST['pagination_start_id'] = 0;
	$products = $productlist->find($parameters,['limit' => $pagination->size(), 'offset' => $pagination->startId()]);
	if ($productlist->error()) $page->addError($productlist->error());

    $page->title("Products");
    $page->addBreadcrumb("Products");

    $pagination->count($totalRecords);