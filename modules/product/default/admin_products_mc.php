<?php
    $site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage products');

    $recordsPerPage = 15;
    $parameters = [];
    $can_proceed = true;

    // For Validation
    $product = new \Product\Item();

    if (isset($_REQUEST['btn_search'])) {
        if (!empty($_REQUEST['search'])) {
            if (!$product->validSearch($_REQUEST['search'] ?? '')) $page->addError("Invalid Search String");
            else $parameters['search'] = $_REQUEST['search'];
        }
        
        if (!empty($_REQUEST['product_type'])) $parameters['type'] = $_REQUEST['product_type'];
        
        $parameters['status'] = [];
        if (!empty($_REQUEST['status_active'])) $parameters['status'][] = 'ACTIVE';
        if (!empty($_REQUEST['status_hidden'])) $parameters['status'][] = 'HIDDEN';
        if (!empty($_REQUEST['status_deleted'])) $parameters['status'][] = 'DELETED';
    }
    else {
        $_REQUEST['status_active'] = true;
        $_REQUEST['status_hidden'] = false;
        $_REQUEST['status_deleted'] = false;
    }
	$productlist = new \Product\ItemList();
	$allProducts = $productlist->find($parameters);
    $totalRecords = $productlist->count($parameters);

	// Pagination offset: ensure non-negative integer so "next page" advances correctly
	$paginationOffset = isset($_REQUEST['pagination_start_id']) && is_numeric($_REQUEST['pagination_start_id']) ? max(0, (int) $_REQUEST['pagination_start_id']) : 0;

	// Prepare controls for sorting and pagination
	$controls = [
		'limit' => $recordsPerPage,
		'offset' => $paginationOffset,
		'sort' => $_REQUEST['sort'] ?? 'code'  // Default sort by code
	];

	$products = $productlist->find($parameters, $controls);
	if ($productlist->error()) $page->addError($productlist->error());

    $page->title("Products");
    $page->addBreadcrumb("Products");

	// Paginate results: use current path so "Next" links go to same page with new offset
	$pagination = new \Site\Page\Pagination();
	$pagination->baseURI = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : ($_SERVER['SCRIPT_URI'] ?? '');
	$pagination->forwardParameters(array('search','product_type','status_active','status_hidden','status_deleted','sort'));
	$pagination->size($recordsPerPage);
	$pagination->count($totalRecords);
