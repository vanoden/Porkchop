<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage products');

	// Initialize validation objects
	$item = new \Product\Item();

	// Validate item by ID
	if ($item->validInteger($_REQUEST['id'] ?? null)) {
		$item = new \Product\Item($_REQUEST['id']);
		if (!$item->id) {
			$page->addError("Item not found");
		}
	}
	// Validate item by code
	elseif ($item->validCode($_REQUEST['code'] ?? null)) {
		$item->get($_REQUEST['code']);
	}
	// Validate item by query vars
	elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0]) && $item->validCode($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$item->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
	}
	else {
		$page->notFound();
	}

	// Check if item has vendors
	if ($item->id) {
		if ($_REQUEST['addPart'] ?? null) {
			// Validate assembly input
			$new_part_id = $_REQUEST['new_part_id'] ?? null;
			$new_quantity = $_REQUEST['new_quantity'] ?? null;

			if (!$new_part_id || !$new_quantity) {
				$page->addError("All fields are required to add a part.");
			} else {
				// Add part to item
				$item->addPart($new_part_id,$new_quantity);
				if ($item->error()) {
					$page->addError("Error adding part: " . $item->error());
				}
			}
		}
		elseif ($_REQUEST['updatePart'] ?? null) {
			// Update part details
			if (empty($_REQUEST["part_id"])) {
				$page->addError("Part ID is required for update.");
			}
			else {
				$part = new \Product\Item\Part($_REQUEST["part_id"]);
				if (!$part->exists()) {
					$page->addError("Part not found.");
				}
				else {
					$part->update(array('quantity' => $_REQUEST['quantity']));
					if ($part->error()) {
						$page->addError("Error updating part: " . $part->error());
					}
				}
			}
		} elseif ($_REQUEST['deletePart'] ?? null) {
			// Delete Part
			if (empty($_REQUEST["part_id"])) {
				$page->addError("Part ID is required for update.");
			}
			else {
				$part = new \Product\Item\Part($_REQUEST["part_id"]);
				if (!$part->exists()) {
					$page->addError("Part not found.");
				}
				else {
					$part->delete();
					if ($part->error()) {
						$page->addError("Error deleting part: " . $part->error());
					}
				}
			}
		}

		$parts = $item->parts();
	}

	$productList = new \Product\ItemList();
	$products = $productList->find(array('status' => 'active'), array('order' => 'name'));

	$page->addBreadcrumb('Products', '/_product/admin_products');
	$page->addBreadcrumb($item->code, '/_product/admin_product/'.$item->code);
	$page->title("Product Parts");