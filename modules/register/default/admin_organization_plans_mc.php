<?php
	/** @view /_register/admin_organization_plans
	 * View for administers to manage organization services and plans.
	 */
	$porkchop = new \Porkchop();
	$site = $porkchop->site();
	$page = $site->page();

	$page->requirePrivilege("manage customers");

	// Play Products
	$product_codes = array(
		"enterprise_web"
	);

	// Identify the Specified Organization
	if (!empty($_REQUEST['organization_id']) && is_numeric($_REQUEST['organization_id'])) {
		$organization = new \Register\Organization($_REQUEST['organization_id']);
		if (! $organization->exists()) {
			$page->addError("Organization not found");
			return;
		}
	}
	elseif (!empty($GLOBALS['_REQUEST_']->query_vars_array[0]) && is_numeric($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$organization = new \Register\Organization($GLOBALS['_REQUEST_']->query_vars_array[0]);
		if (! $organization->exists()) {
			$page->addError("Organization not found");
			return;
		}
	}
	else {
		$organization = $GLOBALS['_SESSION_']->customer->organization();
	}

	if ($_POST) {
		if (! $organization->id) {
			$page->addError("Organization not found");
			return;
		}
		foreach ($product_codes as $product_code) {
			$product = new \Product\Item();
			if (! $product->get($product_code)) {
				$page->addError("Product not found: ".$product_code);
				continue;
			}
			if (isset($_POST['product_'.$product_code]) && $_POST['product_'.$product_code] == "on") {
				if (! $organization->hasProductID($product->id)) {
					if ($organization->addProduct($product->id, 1, '9999-12-31')) {
						$page->success("Added product " . $product->code . " to organization " . $organization->name);
					}
					else {
						$page->addError("Failed to add product " . $product->code . " to organization " . $organization->name . ": " . $organization->error());
					}
				}
			}
			else {
				if ($organization->hasProductID($product->id)) {
					if ($organization->updateProduct($product->id, 0, '9999-12-31')) {
						$page->success("Removed product " . $product->code . " from organization " . $organization->name);
					}
					else {
						$page->addError("Failed to remove product " . $product->code . " from organization " . $organization->name . ": " . $organization->error());
					}
				}
			}
		}
	}