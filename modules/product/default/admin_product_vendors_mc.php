<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage products');

	// Initialize validation objects
	$item = new \Spectros\Product\Item();

	// Validate item by ID
	if ($item->validInteger($_REQUEST['id'] ?? null)) {
		$item = new \Spectros\Product\Item($_REQUEST['id']);
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
		if ($_REQUEST['addVendor'] ?? null) {
			// Validate vendor input
			$new_vendor_id = $_REQUEST['vendor_id'] ?? null;
			$new_vendor_price = $_REQUEST['price'] ?? null;
			$new_vendor_min_order = $_REQUEST['min_order'] ?? null;
			$new_vendor_pack_quantity = $_REQUEST['pack_quantity'] ?? null;
			$new_vendor_price_break_1 = $_REQUEST['price_break_1'] ?? null;
			$new_vendor_price_at_quantity_1 = $_REQUEST['price_at_quantity_1'] ?? null;
			$new_vendor_price_break_2 = $_REQUEST['price_break_2'] ?? null;
			$new_vendor_price_at_quantity_2 = $_REQUEST['price_at_quantity_2'] ?? null;

			if (!$new_vendor_id || !$new_vendor_price || !$new_vendor_min_order || !$new_vendor_pack_quantity) {
				$page->addError("All fields are required to add a vendor.");
			} else {
				// Add vendor to item
				$item->addVendor(
					$new_vendor_id,
					array(
						'price' => $new_vendor_price,
						'min_order' => $new_vendor_min_order,
						'pack_quantity' => $new_vendor_pack_quantity,
						'price_break_1' => $new_vendor_price_break_1,
						'price_at_quantity_1' => $new_vendor_price_at_quantity_1,
						'price_break_2' => $new_vendor_price_break_2,
						'price_at_quantity_2' => $new_vendor_price_at_quantity_2
					)
				);
				if ($item->error()) {
					$page->addError("Error adding vendor: " . $item->error());
				}
			}
		}
		elseif ($_REQUEST['updateVendor'] ?? null) {
			// Update vendor details
			$vendor_id = $_REQUEST['vendor_id'] ?? null;
			$item_id = $_REQUEST['item_id'] ?? $item->id;

			if ($vendor_id && $item_id) {
				$vendorItem = new \Product\VendorItem();
				$vendorItem->get($vendor_id, $item_id);
				print_r("Vendor Item: " . print_r($vendorItem, true));
				if ($vendorItem->error()) {
					$page->addError("Error retrieving vendor item: " . $vendorItem->error());
				} else {
					$parameters = array(
						'cost' => $_REQUEST['price'] ?? $vendorItem->price,
						'minimum_order' => $_REQUEST['min_order'] ?? $vendorItem->minimum_order,
						'pack_quantity' => $_REQUEST['pack_quantity'] ?? $vendorItem->pack_quantity,
						'pack_unit' => $_REQUEST['pack_unit'] ?? $vendorItem->pack_unit,
						'price_break_quantity_1' => $_REQUEST['price_break_quantity_1'] ?? $vendorItem->price_break_quantity_1,
						'price_at_quantity_1' => $_REQUEST['price_at_quantity_1'] ?? $vendorItem->price_at_quantity_1,
						'price_break_quantity_2' => $_REQUEST['price_break_quantity_2'] ?? $vendorItem->price_break_quantity_2,
						'price_at_quantity_2' => $_REQUEST['price_at_quantity_2'] ?? $vendorItem->price_at_quantity_2
					);

					if (!$vendorItem->update($parameters)) {
						$page->addError("Error updating vendor item: " . $vendorItem->error());
					}
				}
			} else {
				$page->addError("Invalid vendor or item ID.");
			}
		} elseif ($_REQUEST['deleteVendor'] ?? null) {
			// Delete vendor
			$vendor_id = $_REQUEST['vendor_id'] ?? null;
			if ($item->deleteVendor($vendor_id)) {
				if ($item->error()) {
					$page->addError("Error deleting vendor: " . $item->error());
				}
			} else {
				$page->addError("Invalid vendor ID.");
			}
		}

		$item_vendors = $item->vendors();
		$vendorList = new \Product\VendorList();
		$vendors = $vendorList->find([]);
		if ($vendorList->error()) {
			$page->addError("Error retrieving vendor list: " . $vendorList->error());
		}
	}

	$page->addBreadcrumb('Products', '/_spectros/admin_products');
	$page->addBreadcrumb($item->code, '/_spectros/admin_product/'.$item->code);
	$page->title("Product Vendors");