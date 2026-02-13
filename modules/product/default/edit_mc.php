<?php
$page = new \Site\Page();
$page->requirePrivilege('manage products');

// Valid Item Types
$item_types = array("inventory", "unique", "group", "kit", "note");

// Fetch Code from Query String if not Posted
$new_item = false;
if (isset($_REQUEST['id']) && $_REQUEST['id'] > 0) {
	$item = new \Product\Item($_REQUEST['id']);
	if (!$item->id && !empty($_REQUEST['code'])) {
		// If ID didn't work, try codeq
		$item = new \Product\Item();
		if ($item->validCode($_REQUEST['code'])) {
			$item->get($_REQUEST['code']);
		}
	}
} elseif (!empty($_REQUEST['code'])) {
	$item = new \Product\Item();
	if ($item->validCode($_REQUEST['code'])) {
		$item->get($_REQUEST['code']);
		if ($item->id) $new_item = false;
		else $new_item = true;
	} else $page->addError("Invalid product code");
} elseif (!empty($GLOBALS['_REQUEST_']->query_vars_array[0])) {
	$item = new \Product\Item();
	if ($item->validCode($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$item->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
		if ($item->id) $new_item = false;
		else $new_item = true;
	} else $page->addError("Invalid product code");
} else {
	$item = new \Product\Item();
	$new_item = true;
}

if (! $new_item && ! $item->id) $page->addError("Item not found");

// Handle Actions
if (isset($_REQUEST['updateSubmit'])) {
	// CSRF Token Check
	if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) $page->addError("Invalid Request");
	if (!$page->errorCount()) {
		if (empty($_REQUEST['code']))
			$page->addError("Code required");
		elseif (! $item->validCode($_REQUEST['code']))
			$page->addError("Invalid code");
		elseif (empty($_REQUEST['status']))
			$page->addError("Status required");
		elseif (! $item->validStatus($_REQUEST['status']))
			$page->addError("Invalid status");
		elseif (empty($_REQUEST['type']))
			$page->addError("Type required");
		elseif (! $item->validType($_REQUEST['type']))
			$page->addError("Invalid type");
		else {
			if ($new_item) {
				app_log("Admin " . $GLOBALS['_SESSION_']->customer->first_name . " editing product " . $_REQUEST['code'], 'notice', __FILE__, __LINE__);
				$item->add(array(
					"code"	=> $_REQUEST['code'],
					"type"	=> $_REQUEST['type'],
					"status"	=> $_REQUEST['status']
				));
			} else app_log("Admin " . $GLOBALS['_SESSION_']->customer->first_name . " adding product " . $_REQUEST['code'], 'notice', __FILE__, __LINE__);

			$item->update(
				array(
					"code"		=> $_REQUEST['code'],
					"type"		=> $_REQUEST['type'],
					"status"	=> $_REQUEST["status"]
				)
			);
			if ($item->error()) {
				app_log("Error updating item: " . $item->error(), 'error', __FILE__, __LINE__);
				$page->addError("Error updating Item");
			}

			# Associate with a Parent if one selected or new product
			if (!empty($_REQUEST['parent_code'])) {
				$parent = new \Product\Item();
				$parent->get($_REQUEST['parent_code']);
				if ($parent->error()) {
					app_log("Error finding item " . $_REQUEST['parent_code'], 'error', __FILE__, __LINE__);
					$page->addError("Error finding parent");
				} elseif ($parent->id) {
					$relationship = new \Product\Relationship();
					$relationship->add(array(
						"parent_id"	=> $parent->id,
						"child_id" => $item->id
					));
				}
			} elseif ($new_item) {
				$relationship = new \Product\Relationship();
				$relationship->add(array(
					"parent_id"	=> 0,
					"child_id" => $item->id
				));
			}

			// Add Company Specific Metadata (REPLACE WITH CONFIGURED LOOP OF KEYS)
			$meta_fields = array("name", "short_description", "description", "model", "emperical_formula", "sensitivity", "measure_range", "accuracy", "manual_id", "datalogger", "spec_table_image", "default_dashboard_id");
			foreach ($meta_fields as $meta_field) {
				if (isset($_REQUEST[$meta_field])) {
					$current = $item->getMetadata($meta_field);
					$value = trim($_REQUEST[$meta_field]);
					if ($current != $value) {
						$item->setMetadata($meta_field,$value);
						if ($item->error()) $page->addError("Error setting " . $meta_field . ": " . $item->error());
						else $page->appendSuccess("Updated '" . $meta_field . "'");
					}
				}
			}

			$image = new \Media\Image();
			if ($_REQUEST['new_image_code']) {
				$image->get($_REQUEST['new_image_code']);
				$item->addImage($image->id);
			}

			if ($_REQUEST['deleteImage']) {
				$image->get($_REQUEST['deleteImage']);
				$item->dropImage($image->id);
			}
		}

		if (is_numeric($_REQUEST['new_price_amount']) && $_REQUEST['new_price_amount'] > 0) {
			$price = new \Product\Price();
			if (! $price->validStatus($_REQUEST['new_price_status'])) {
				$page->addError("Invalid price status");
			} elseif (!get_mysql_date($_REQUEST['new_price_date'])) {
				$page->addError("Invalid price active date: '" . $_REQUEST['new_price_date'] . "'");
			} else {
				$newPrice = $item->addPrice(array('date_active' => $_REQUEST['new_price_date'], 'status' => $_REQUEST['new_price_status'], 'amount' => $_REQUEST['new_price_amount']));

				if ($item->error()) {
					$page->addError($item->error());
				} else {

					$page->success .= "Price Added";

					// audit the price change
					$priceAudit = new \Product\PriceAudit();
					$priceAudit->add(
						array(
							'product_price_id' => $newPrice->id,
							'user_id' => $GLOBALS['_SESSION_']->customer->id,
							'note' => "New Price Added by: " . $GLOBALS['_SESSION_']->customer->first_name . " " . $GLOBALS['_SESSION_']->customer->last_name . " for: $" . $_REQUEST['new_price_amount']
						)
					);

					// catch price audit error
					if ($priceAudit->error()) $page->addError($priceAudit->error());
				}
			}
		}
	}
}

if (isset($_REQUEST['addTag']) && !isset($_REQUEST['removeTag'])) {

	// Ensure item is loaded from POST data if not already loaded
	if (empty($item->id) && !empty($_REQUEST['id']) && $_REQUEST['id'] > 0) {
		$item = new \Product\Item($_REQUEST['id']);
	} elseif (empty($item->id) && !empty($_REQUEST['code'])) {
		$item = new \Product\Item();
		if ($item->validCode($_REQUEST['code'])) {
			$item->get($_REQUEST['code']);
		}
	}
	
	if (empty($item->id)) {
		$page->addError("Cannot add tag: Product must be saved first. Current item ID: " . ($item->id ?? 'NULL') . ". POST ID: " . ($_REQUEST['id'] ?? 'NULL') . ", POST Code: " . ($_REQUEST['code'] ?? 'NULL'));
	} elseif (empty($_REQUEST['newTag'])) {
		$page->addError("Value for Product Tag is required");
	} elseif (!$item->validTagValue($_REQUEST['newTag'])) {
		$page->addError("Invalid tag format. Tags can only contain letters, numbers, dashes, underscores, dots, and spaces.");
	} else {
		if ($item->addTag($_REQUEST['newTag'], 'product_tag')) {
			$page->appendSuccess("Product Tag added Successfully");
		} else {
			$errorMsg = $item->error() ? $item->error() : "Unknown error adding tag";
			$page->addError("Error adding product tag: " . $errorMsg);
		}
	}
}

// remove tag from product (using BaseModel unified tag system)
if (!empty($_REQUEST['removeTagId'])) {
	// Get tag details from xref ID
	$searchTagXrefItem = new \Site\SearchTagXref($_REQUEST['removeTagId']);
	if ($searchTagXrefItem->id) {
		$searchTag = new \Site\SearchTag($searchTagXrefItem->tag_id);
		$tagClass = $item->getTagClass(); // Get the normalized class name
		$isProductTag = ($searchTag->id && $searchTag->class === $tagClass && ($searchTag->category === '' || $searchTag->category === 'product_tag'));
		if ($isProductTag) {
			$category = $searchTag->category === 'product_tag' ? 'product_tag' : '';
			if ($item->removeTag($searchTag->value, $category)) {
				$page->appendSuccess("Product Tag removed Successfully");
			} else {
				$page->addError("Error removing product tag: " . $item->error());
			}
		}
	}
}

// get tags for product (using BaseModel unified tag system)
// Get simple product tags (category empty or product_tag) for display with xref IDs
$tagClass = $item->getTagClass(); // Get the normalized class name (e.g., Product::Item or Spectros::Product::Item)
$searchTagXref = new \Site\SearchTagXrefList();
$searchTagXrefs = $searchTagXref->find(array("object_id" => $item->id, "class" => $tagClass));

$productTags = array();
if ($searchTagXref->error() || !is_array($searchTagXrefs)) {
	$searchTagXrefs = array();
}
foreach ($searchTagXrefs as $searchTagXrefItem) {
	$searchTag = new \Site\SearchTag();
	$searchTag->load($searchTagXrefItem->tag_id);
	// Include tags with empty category or product_tag (simple product tags)
	$isProductTag = ($searchTag->id && $searchTag->class === $tagClass && ($searchTag->category === '' || $searchTag->category === 'product_tag'));
	if ($isProductTag) {
		$tagObj = new stdClass();
		$tagObj->id = $searchTagXrefItem->id; // Use xref ID for removal
		$tagObj->name = $searchTag->value;
		$tagObj->category = $searchTag->category ?: 'product_tag';
		$productTags[] = $tagObj;
	}
}

// add search tag to product (using BaseModel unified tag system)
if (!empty($_REQUEST['newSearchTag']) && empty($_REQUEST['removeSearchTag'])) {
	if (!empty($_REQUEST['newSearchTag']) && !empty($_REQUEST['newSearchTagCategory']) && 
		$item->validTagValue($_REQUEST['newSearchTag']) && 
		$item->validTagCategory($_REQUEST['newSearchTagCategory'])) {
		
		if ($item->addTag($_REQUEST['newSearchTag'], $_REQUEST['newSearchTagCategory'])) {
			$page->appendSuccess("Product Search Tag added Successfully");
		} else {
			$page->addError("Error adding product search tag: " . $item->error());
		}
	} else {
		$page->addError("Value for Product Tag and Category are required");
	}
}

// remove search tag from product (using BaseModel unified tag system)
if (!empty($_REQUEST['removeSearchTagId'])) {
	// Get tag details from xref ID
	$searchTagXrefItem = new \Site\SearchTagXref($_REQUEST['removeSearchTagId']);
	if ($searchTagXrefItem->id) {
		$searchTag = new \Site\SearchTag($searchTagXrefItem->tag_id);
		$tagClass = $item->getTagClass(); // Get the normalized class name
		if ($searchTag->id && $searchTag->class === $tagClass) {
			if ($item->removeTag($searchTag->value, $searchTag->category)) {
				$page->appendSuccess("Product Search Tag removed Successfully");
			} else {
				$page->addError("Error removing product search tag: " . $item->error());
			}
		}
	}
}

// get search tags for product (using BaseModel unified tag system)
// Get all tags with their categories for display (include xref id for remove)
$tagClass = $item->getTagClass(); // Get the normalized class name
$searchTagXref = new \Site\SearchTagXrefList();
$searchTagXrefs = $searchTagXref->find(array("object_id" => $item->id, "class" => $tagClass));

$productSearchTags = array();
if ($searchTagXref->error() || !is_array($searchTagXrefs)) {
	$searchTagXrefs = array();
}
foreach ($searchTagXrefs as $searchTagXrefItem) {
	$searchTag = new \Site\SearchTag();
	$searchTag->load($searchTagXrefItem->tag_id);
	// Only include in Search Tags list if it has a category and is not the simple product_tag category
	if ($searchTag->category !== '' && $searchTag->category !== 'product_tag') {
		$productSearchTags[] = (object) array('searchTag' => $searchTag, 'xrefId' => $searchTagXrefItem->id);
	}
}

// Reload Product if needed (after tag operations)
if (isset($_REQUEST['code']) && empty($item->id)) {
	$item->get($_REQUEST['code']);
	if ($item->error()) $page->addError("Error loading item '" . $_REQUEST['code'] . "': " . $item->error());
} elseif (isset($_REQUEST['id']) && $_REQUEST['id'] > 0 && empty($item->id)) {
	$item = new \Product\Item($_REQUEST['id']);
	if ($item->error()) $page->addError("Error loading item ID '" . $_REQUEST['id'] . "': " . $item->error());
}

// Get Manuals
$documentlist = new \Media\DocumentList();
$manuals = $documentlist->find();
if ($documentlist->error() || !is_array($manuals)) {
	$manuals = array();
}

$imagelist = new \Media\ImageList();
$tables = $imagelist->find();
if ($imagelist->error() || !is_array($tables)) {
	$tables = array();
}

if (defined('MODULES') && is_dir(MODULES . '/Monitor')) {
	$dashboardlist = new \Monitor\DashboardList();
	$dashboards = $dashboardlist->find();
	if ($dashboardlist->error() || !is_array($dashboards)) {
		$dashboards = array();
	}
} else {
	$dashboards = array();
}

$prices = $item->prices();
if (!is_array($prices)) {
	$prices = array();
}

$priceAudit = new \Product\PriceAuditList();
$auditedPrices = $priceAudit->find(array('product_id' => $item->id));
if ($priceAudit->error() || !is_array($auditedPrices)) {
	$auditedPrices = array();
}

$images = $item->images();
if (!is_array($images)) {
	$images = array();
}

$page->setAdminMenuSection("Products");  // Keep Products section open
$page->addBreadcrumb("Products", "/_product/report");
if (isset($item->id)) $page->addBreadcrumb($item->code, "/_product/edit/" . $item->code);

// get unique categories and tags for autocomplete
$searchTagList = new \Site\SearchTagList();
$uniqueTagsData = $searchTagList->getUniqueCategoriesAndTagsJson();
