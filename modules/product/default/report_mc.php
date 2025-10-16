<?php
$site = new \Site();
$page = $site->page();
$page->requirePrivilege('manage products');
$can_proceed = true;

$parameters = [];

// paginate results
$pagination = new \Site\Page\Pagination();
$pagination->forwardParameters(array('search', 'product_type', 'status_active', 'status_hidden', 'status_deleted', 'sort_field', 'sort_direction'));

// For Validation
$product = new \Product\Item();

$btn_search = $_REQUEST['btn_search'] ?? null;
if (isset($btn_search)) {
    $search = $_REQUEST['search'] ?? null;
    if (!empty($search)) {
        if (!$product->validSearch($search)) {
            $page->addError("Invalid Search String");
            $can_proceed = false;
        } else {
            $parameters['search'] = $search;
        }
    }

    $product_type = $_REQUEST['product_type'] ?? null;
    if (!empty($product_type)) {
        if (!$product->validText($product_type)) {
            $page->addError("Invalid Product Type");
            $can_proceed = false;
        } else {
            $parameters['type'] = $product_type;
        }
    }

    $parameters['status'] = [];

    $status_active = $_REQUEST['status_active'] ?? null;
    if (!empty($status_active)) {
        if (!$product->validBoolean($status_active)) {
            $page->addError("Invalid Active Status Parameter");
            $can_proceed = false;
        } else {
            $parameters['status'][] = 'ACTIVE';
        }
    }

    $status_hidden = $_REQUEST['status_hidden'] ?? null;
    if (!empty($status_hidden)) {
        if (!$product->validBoolean($status_hidden)) {
            $page->addError("Invalid Hidden Status Parameter");
            $can_proceed = false;
        } else {
            $parameters['status'][] = 'HIDDEN';
        }
    }

    $status_deleted = $_REQUEST['status_deleted'] ?? null;
    if (!empty($status_deleted)) {
        if (!$product->validBoolean($status_deleted)) {
            $page->addError("Invalid Deleted Status Parameter");
            $can_proceed = false;
        } else {
            $parameters['status'][] = 'DELETED';
        }
    }
} else {
    $_REQUEST['status_active'] = true;
    $_REQUEST['status_hidden'] = false;
    $_REQUEST['status_deleted'] = false;
    $_REQUEST['product_type'] = '';
    $_REQUEST['search'] = '';
}

// Only proceed if validation passed
if ($can_proceed) {
    $productlist = new \Product\ItemList();
    $totalRecords = $productlist->count($parameters);

    $pagination_start_id = $_REQUEST['pagination_start_id'] ?? 0;
    if (!$product->validInteger($pagination_start_id) && $pagination_start_id !== 0) {
        $page->addError("Invalid pagination start ID");
        $can_proceed = false;
        $pagination_start_id = 0;
    }

    $products = $productlist->find($parameters, ['limit' => $pagination->size(), 'offset' => $pagination->startId()]);
    if ($productlist->error()) {
        $page->addError($productlist->error());
        $can_proceed = false;
    }
}

$page->title("Products");
$page->setAdminMenuSection("Products");  // Keep Products section open
$page->addBreadcrumb("Products");

if ($can_proceed) {
    $pagination->count($totalRecords);
}
