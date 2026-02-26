<?php
$page = new \Site\Page();
$page->requirePrivilege('manage products');

// Validation Class
$validationClass = new \Product\Item();

// Validate item by code
if ($validationClass->validCode($_REQUEST['code'] ?? null)) {
    $item = new \Product\Item();
    $item->get($_REQUEST['code']);
    if (!$item->id) $page->addError("Item not found");
}
// Validate item by query vars
elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0]) && $validationClass->validCode($GLOBALS['_REQUEST_']->query_vars_array[0])) {
    $item = new \Product\Item();
    $item->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
    if (!$item->id) $page->addError("Item not found");
}

// Initialize $item variable if not already set or not a valid object
if (!isset($item) || !is_object($item)) {
    $item = new \Product\Item();
}

$metadataKeys = $item->getInstanceMetadataKeys();

// Define standard metadata fields that should always be processed from the form
$standardMetadataFields = ['name', 'description', 'short_description', 'default_dashboard_id'];

// Handle Actions
if (!empty($_REQUEST['updateMetadata'])) {
    // CSRF Token Check
    $csrfToken = $_POST['csrfToken'] ?? '';
    if (!$GLOBALS['_SESSION_']->verifyCSRFToken($csrfToken)) {
        $page->addError("Invalid Request");
    }
    if (!$page->errorCount()) {
        // Ensure we have a valid item with an ID
        if (!$item->id) {
            $page->addError("Invalid product: cannot update metadata without a valid product ID");
        } else {
            // Merge standard fields with existing metadata keys to ensure all form fields are processed
            $fieldsToProcess = array_unique(array_merge($standardMetadataFields, $metadataKeys));

            $metadataUpdated = false;

            // Update metadata fields (both existing and standard form fields)
            foreach ($fieldsToProcess as $meta_field) {
                if (isset($_REQUEST[$meta_field])) {
                    $value = trim($_REQUEST[$meta_field]);
                    $isValid = false;
                    
                    // Handle integer fields (like default_dashboard_id)
                    if ($meta_field === 'default_dashboard_id') {
                        if ($value === '' || $item->validInteger($value)) {
                            $isValid = true;
                        }
                    } else {
                        // For text fields, allow empty values or valid text
                        // Note: validText may fail on empty strings, so we check empty separately
                        if ($value === '') {
                            $isValid = true;
                        } elseif ($item->validText($value)) {
                            $isValid = true;
                        }
                    }
                    
                    if ($isValid) {
                        $currentValue = $item->getMetadata($meta_field);
                        
                        // Use strict comparison to ensure we catch actual differences
                        if (strval($currentValue) !== strval($value)) {
                            $item->setMetadata($meta_field, $value);
                            if ($item->error()) {
                                $errorMsg = $item->error();
                                $page->addError("Error setting " . $meta_field . ": " . $errorMsg);
                            } else {
                                $metadataUpdated = true;
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

            // Handle adding new metadata if fields are present
            if (!empty($_REQUEST['new_metadata_key']) && !empty($_REQUEST['new_metadata_value'])) {
                $newKey = trim($_REQUEST['new_metadata_key']);
                $newValue = trim($_REQUEST['new_metadata_value']);
                
                if (empty($newKey)) {
                    $page->addError("Metadata key is required");
                } elseif (empty($newValue)) {
                    $page->addError("Metadata value is required");
                } elseif (!$item->validMetadataKey($newKey)) {
                    $page->addError("Invalid metadata key format");
                } elseif (!$item->validMetadataValue($newValue)) {
                    $page->addError("Invalid metadata value format");
                } else {
                    // Check if key already exists
                    if ($item->getMetadata($newKey) !== '') {
                        $page->addError("Metadata key '" . $newKey . "' already exists");
                    } else {
                        // Add the new metadata
                        $item->setMetadata($newKey, $newValue);
                        if ($item->error()) {
                            $page->addError("Error adding metadata: " . $item->error());
                        } else {
                            $metadataUpdated = true;
                            $page->appendSuccess("Added new metadata '" . $newKey . "'");
                            // Refresh metadata keys to include the new one
                            $metadataKeys = $item->getInstanceMetadataKeys();
                        }
                    }
                }
            }
        }
    }
}

// Handle deleting metadata
if (!empty($_REQUEST['deleteMetadata'])) {
    // CSRF Token Check
    if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'] ?? '')) $page->addError("Invalid Request");
    if (!$page->errorCount()) {
        $deleteKey = trim($_REQUEST['delete_metadata_key'] ?? '');
        
        if (empty($deleteKey)) {
            $page->addError("Metadata key is required for deletion");
        } else {
            // Check if it's a protected key
            if (in_array($deleteKey, ['default_dashboard_id', 'manual_id', 'spec_table_image', 'name', 'description', 'short_description'])) {
                $page->addError("Cannot delete protected metadata field: " . $deleteKey);
            } else {
                // Delete the metadata
                $item->unsetMetadata($deleteKey);
                if ($item->error()) {
                    $page->addError("Error deleting metadata: " . $item->error());
                } else {
                    $page->appendSuccess("Deleted metadata field '" . $deleteKey . "'");
                    // Refresh metadata keys
                    $metadataKeys = $item->getInstanceMetadataKeys();
                }
            }
        }
    }
}

// After metadata updates, reload the item to ensure we have the latest data
if (!empty($_REQUEST['updateMetadata']) || !empty($_REQUEST['deleteMetadata'])) {
    // Reload the item to get fresh metadata values
    if (isset($item->id) && $item->id && isset($item->code)) {
        $item->get($item->code);
    }
}

// Get data for dropdowns
$documentlist = new \Media\DocumentList();
$manuals = $documentlist->find();
$imagelist = new \Media\ImageList();
$tables = $imagelist->find();
if (defined('MODULES') && is_dir(MODULES . '/Monitor')) {
	$dashboardlist = new \Monitor\DashboardList();
	$dashboards = $dashboardlist->find();
	if ($dashboardlist->error() || !is_array($dashboards)) {
		$dashboards = array();
	}
} else {
	$dashboards = array();
}

$page->setAdminMenuSection("Products");  // Keep Products section open
$page->addBreadcrumb("Products", "/_product/admin_products");
if (isset($item->id)) $page->addBreadcrumb($item->code, "/_product/admin_product/" . $item->code);
$page->addBreadcrumb("Metadata", "/_product/admin_product_metadata/" . $item->code);
