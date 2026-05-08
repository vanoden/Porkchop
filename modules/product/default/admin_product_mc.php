<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage products');

// Valid Item Types
$item_types = array("inventory", "unique", "group", "kit", "note");

// Validation Class
$validationClass = new \Product\Item();

// Validate item by ID
if (!empty($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
	$item = new \Product\Item($_REQUEST['id']);
	if (!$item->id) $page->addError("Item not found");
}
// Validate item by code
elseif ($validationClass->validCode($_REQUEST['code'] ?? null)) {
	// This is a New Item
	$item = new \Product\Item();
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

// Preserve form values for display (for new items or when there are errors)
if (!empty($_REQUEST['updateSubmit'])) {
	// For new items, always preserve form values
	if (!$item->id) {
		if (isset($_REQUEST['code'])) $item->code = $_REQUEST['code'];
		if (isset($_REQUEST['type'])) $item->type = $_REQUEST['type'];
		if (isset($_REQUEST['status'])) $item->status = $_REQUEST['status'];
	}
}

// Handle Actions
if (!empty($_REQUEST['updateSubmit'])) {
	// CSRF Token Check
	if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'] ?? '')) $page->addError("Invalid Request");
	if (!$page->errorCount()) {
		$valid_form = true;
		
		// Validate required fields
		if (!$item->id) {
			// For new items, code must come from the form
			$code_value = trim($_REQUEST['code'] ?? '');
			if (!empty($code_value) && $validationClass->validCode($code_value)) {
				$code = $code_value;
			} else {
				$page->addError("Invalid or missing code");
				$valid_form = false;
			}
		} else {
			// For existing items, use the existing code
			$code = $item->code;
		}
		
		if ($validationClass->validStatus($_REQUEST['status'] ?? null)) {
			$status = $_REQUEST['status'];
		} else {
			$page->addError("Invalid or missing status");
			$valid_form = false;
		}
		
		if ($validationClass->validType($_REQUEST['type'] ?? null)) {
			$type = $_REQUEST['type'];
		} else {
			$page->addError("Invalid or missing type");
			$valid_form = false;
		}
		
		if ($valid_form) {
			if (! $item->id) {
				app_log("Admin " . $GLOBALS['_SESSION_']->customer->first_name . " adding product " . $code, 'notice', __FILE__, __LINE__);
				$item->add(array(
					"code" => $code,
					"type" => $type,
					"status" => $status,
					"min_quantity" => $_REQUEST['min_quantity'] ?? 0,
					"max_quantity" => $_REQUEST['max_quantity'] ?? 0,
				));
				if ($item->error()) {
					app_log("Error adding item: " . $item->error(), 'error', __FILE__, __LINE__);
					$page->addError("Error adding Item: " . $item->error());
				} else {
					// Save metadata fields (name and description) for new items
					$metadataFields = ['name', 'description'];
					foreach ($metadataFields as $meta_field) {
						if (isset($_REQUEST[$meta_field])) {
							$value = trim($_REQUEST[$meta_field]);
							if ($value !== '') {
								$item->setMetadata($meta_field, $value);
								if ($item->error()) {
									app_log("Error setting metadata {$meta_field}: " . $item->error(), 'error', __FILE__, __LINE__);
								}
							}
						}
					}
					$page->appendSuccess("Product added successfully");
					// Redirect to the new product's page using the code from the form
					header("Location: /_product/admin_product/" . $code);
					exit;
				}
			} else {
				app_log("Admin " . $GLOBALS['_SESSION_']->customer->first_name . " updating product " . $code, 'notice', __FILE__, __LINE__);

				$item->update(
					array(
						"code" => $code,
						"type" => $type,
						"status" => $status,
						"min_quantity" => $_REQUEST['min_quantity'] ?? 0,
						"max_quantity" => $_REQUEST['max_quantity'] ?? 0,
						"default_vendor" => $_REQUEST['default_vendor_id'] ?? null,
					)
				);
				if ($item->error()) {
					app_log("Error updating item: " . $item->error(), 'error', __FILE__, __LINE__);
					$page->addError("Error updating Item: " . $item->error());
				}
				else {
					// Save metadata fields (name and description) for existing items
					$metadataFields = ['name', 'description'];
					foreach ($metadataFields as $meta_field) {
						if (isset($_REQUEST[$meta_field])) {
							$value = trim($_REQUEST[$meta_field]);
							$item->setMetadata($meta_field, $value);
							if ($item->error()) {
								app_log("Error setting metadata {$meta_field}: " . $item->error(), 'error', __FILE__, __LINE__);
								$page->addError("Error updating {$meta_field}: " . $item->error());
							}
						}
					}
					$page->appendSuccess("Product updated successfully");
				}
			}

			# Associate with a Parent if one selected or new product
			if (!empty($_REQUEST['parent_code'])) {
				$parent = new \Product\Item();
				if ($parent->validCode($_REQUEST['parent_code'])) {
					$parent->get($_REQUEST['parent_code']);
					if ($parent->error()) {
						app_log("Error finding item " . $_REQUEST['parent_code'], 'error', __FILE__, __LINE__);
						$page->addError("Error finding parent: " . $parent->error());
					}
					elseif ($parent->id) {
						$relationship = new \Product\Relationship();
						$relationship->add(array(
							"parent_id" => $parent->id,
							"child_id" => $item->id
						));
					}
				} else {
					$page->addError("Invalid parent code format");
				}
			}
			elseif ($new_item) {
				$relationship = new \Product\Relationship();
				$relationship->add(array(
					"parent_id" => 0,
					"child_id" => $item->id
				));
			}

			// Update Visibility
			if (isset($_REQUEST['visibility_marketing'])) {
				$item->setVisibility(productVisibilityRealm::MARKETING, true);
			} else {
				$item->setVisibility(productVisibilityRealm::MARKETING, false);
			}
			if (isset($_REQUEST['visibility_sales'])) {
				$item->setVisibility(productVisibilityRealm::SALES, true);
			} else {
				$item->setVisibility(productVisibilityRealm::SALES, false);
			}
			if (isset($_REQUEST['visibility_support'])) {
				$item->setVisibility(productVisibilityRealm::SUPPORT, true);
			} else {
				$item->setVisibility(productVisibilityRealm::SUPPORT, false);
			}
			if (isset($_REQUEST['visibility_assembly'])) {
				$item->setVisibility(productVisibilityRealm::ASSEMBLY, true);
			} else {
				$item->setVisibility(productVisibilityRealm::ASSEMBLY, false);
			}
		}
	}
}

// Get Manuals
$documentlist = new \Media\DocumentList();
$manuals = $documentlist->find();
$imagelist = new \Media\ImageList();
$tables = $imagelist->find();
$vendors = $item->vendors();

// Get unique categories and tags for autocomplete
$searchTagList = new \Site\SearchTagList();
$uniqueTagsData = $searchTagList->getUniqueCategoriesAndTagsJson();

$page->addBreadcrumb("Products", "/_product/admin_products");
if (isset($item->id)) $page->addBreadcrumb($item->code, "/_product/admin_product/" . $item->code);