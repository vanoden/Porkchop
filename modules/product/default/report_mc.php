<?php
    $site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage products');

    $recordsPerPage = 15;

    $parameters = [];

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
    }
	$productlist = new \Product\ItemList();
    $totalRecords = $productlist->count($parameters);
	$products = $productlist->find($parameters,['limit' => $recordsPerPage,'offset' => $_REQUEST['pagination_start_id']]);
	if ($productlist->error()) $page->addError($productlist->error());

    $page->title("Products");
    $page->addBreadcrumb("Products");

	// paginate results
    $pagination = new \Site\Page\Pagination();
    $pagination->forwardParameters(array('search','product_type','status_active','status_hidden','status_deleted','sort_field','sort_direction'));
    $pagination->size($recordsPerPage);
    $pagination->count($totalRecords);