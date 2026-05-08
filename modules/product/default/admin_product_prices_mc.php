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

	// Initialize arrays in case item is not found
	$prices = array();
	$auditedPrices = array();

	if ($item->id) {
		$prices = $item->prices();
		$priceAudit = new \Product\PriceAuditList();
		$auditedPrices = $priceAudit->find(array('product_id' => $item->id));

		// Price validation
		if ($item->validDecimal($_REQUEST['new_price_amount'] ?? null) && $_REQUEST['new_price_amount'] > 0) {
			$price = new \Product\Price();
			$valid_price = true;
			
			if (!$price->validStatus($_REQUEST['new_price_status'] ?? null)) {
				$page->addError("Invalid price status");
				$valid_price = false;
			}

			$price_date = get_mysql_date($_REQUEST['new_price_date'] ?? null);
			
			if ($valid_price) {
				$newPrice = $item->addPrice(array(
					'date_active' => $price_date,
					'status' => $_REQUEST['new_price_status'],
					'amount' => $_REQUEST['new_price_amount']
				));

				if ($item->error()) $page->addError($item->error());
				else {
					$page->success .= "Price Added";

					// audit the price change
					$priceAudit = new \Product\PriceAudit();
					$priceAudit->add(
						array(
							'product_price_id' => $newPrice->id,
							'user_id' => $GLOBALS['_SESSION_']->customer->id,
							'note' => "New Price Added by: " . $GLOBALS['_SESSION_']->customer->first_name . " " . $GLOBALS['_SESSION_']->customer->last_name . " for $" . $newPrice->amount . " " . $newPrice->status . " on " . $price_date
						)
					);

					// catch price audit error
					if ($priceAudit->error()) $page->addError($priceAudit->error());
				}
			}
		}
		$prices = $item->prices();
	}

	$page->addBreadcrumb('Products', '/_product/admin_products');
	$page->addBreadcrumb($item->code, '/_product/admin_product/'.$item->code);
	$page->title("Product Prices");