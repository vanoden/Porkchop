<?php
$page = new \Site\Page();
$page->requirePrivilege('manage products');

// Validation Class
$validationClass = new \Spectros\Product\Item();

// Validate item by code
if ($validationClass->validCode($_REQUEST['code'] ?? null)) {
    $item = new \Spectros\Product\Item();
    $item->get($_REQUEST['code']);
    if (!$item->id) $page->addError("Item not found");
}
// Validate item by query vars
elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0]) && $validationClass->validCode($GLOBALS['_REQUEST_']->query_vars_array[0])) {
    $item = new \Spectros\Product\Item();
    $item->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
    if (!$item->id) $page->addError("Item not found");
}

// Initialize $item variable if not already set or not a valid object
if (!isset($item) || !is_object($item)) {
    $item = new \Spectros\Product\Item();
}

$metadataKeys = $item->getMetadataKeys();

// Handle Actions
if (!empty($_REQUEST['updateMetadata'])) {
    // CSRF Token Check
    if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'] ?? '')) $page->addError("Invalid Request");
    if (!$page->errorCount()) {
        app_log("Admin " . $GLOBALS['_SESSION_']->customer->first_name . " updating metadata for product " . $item->code, 'notice', __FILE__, __LINE__);

        // Update metadata fields
        foreach ($metadataKeys as $meta_field) {
            if (isset($_REQUEST[$meta_field])) {
                $value = trim($_REQUEST[$meta_field]);
                if ($item->validText($value)) {
                    if ($item->getMetadata($meta_field) != $value) {
                        $item->setMetadata($meta_field, $value);
                        if ($item->error()) {
                            $page->addError("Error setting " . $meta_field . ": " . $item->error());
                        } else {
                            $page->appendSuccess("Updated '" . $meta_field . "'");
                        }
                    }
                }
            }
        }

        // Handle special fields that might not be in metadata
        if (isset($_REQUEST['manual_id']) && $item->validInteger($_REQUEST['manual_id'])) {
            $item->update(array('manual_id' => $_REQUEST['manual_id']));
            if ($item->error()) $page->addError("Error updating manual: " . $item->error());
        }

        if (isset($_REQUEST['spec_table_image']) && $item->validInteger($_REQUEST['spec_table_image'])) {
            $item->update(array('spec_table_image' => $_REQUEST['spec_table_image']));
            if ($item->error()) $page->addError("Error updating spec table: " . $item->error());
        }
    }
}

// Get data for dropdowns
$documentlist = new \Media\DocumentList();
$manuals = $documentlist->find();
$imagelist = new \Media\ImageList();
$tables = $imagelist->find();
$dashboardlist = new \Monitor\DashboardList();
$dashboards = $dashboardlist->find();

$page->addBreadcrumb("Products", "/_spectros/admin_products");
if (isset($item->id)) $page->addBreadcrumb($item->code, "/_spectros/admin_product/" . $item->code);
$page->addBreadcrumb("Metadata", "/_product/admin_product_metadata/" . $item->code);
