<?php
    $site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage products');

    $recordsPerPage = 15;
    $parameters = [];
    $can_proceed = true;

    $product = new \Product\Item();

    $hasFilterActivity = !empty($_REQUEST['btn_search'])
        || array_key_exists('pagination_start_id', $_GET)
        || array_key_exists('search', $_GET)
        || array_key_exists('product_type', $_GET)
        || array_key_exists('status_active', $_GET)
        || array_key_exists('status_hidden', $_GET)
        || array_key_exists('status_deleted', $_GET)
        || array_key_exists('sort', $_GET);

    if (!$hasFilterActivity) {
        $_REQUEST['status_active'] = 1;
    }

    if (!empty($_REQUEST['search'])) {
        if (!$product->validSearch($_REQUEST['search'])) {
            $page->addError("Invalid Search String");
            $can_proceed = false;
        } else {
            $parameters['search'] = $_REQUEST['search'];
        }
    }

    if (!empty($_REQUEST['product_type'])) {
        $parameters['type'] = $_REQUEST['product_type'];
    }

    $parameters['status'] = [];
    if (!empty($_REQUEST['status_active'])) $parameters['status'][] = 'ACTIVE';
    if (!empty($_REQUEST['status_hidden'])) $parameters['status'][] = 'HIDDEN';
    if (!empty($_REQUEST['status_deleted'])) $parameters['status'][] = 'DELETED';

	$productlist = new \Product\ItemList();
    $totalRecords = $productlist->count($parameters);

	$pagination = new \Site\Page\Pagination();
	$pagination->baseURI = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : ($_SERVER['SCRIPT_URI'] ?? '');
	$pagination->forwardParameters(array('search','product_type','status_active','status_hidden','status_deleted','sort'));
	$pagination->size($recordsPerPage);
	$pagination->count($totalRecords);

	$controls = [
		'limit' => $recordsPerPage,
		'offset' => $pagination->startId(),
		'sort' => $_REQUEST['sort'] ?? 'code'
	];

	if ($can_proceed) {
		$products = $productlist->find($parameters, $controls);
		if ($productlist->error()) $page->addError($productlist->error());
	} else {
		$products = [];
	}

    $page->title("Products");
    $page->addBreadcrumb("Products");
