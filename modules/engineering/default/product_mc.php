<?php
	if (! $GLOBALS['_SESSION_']->customer->has_role('engineering user')) {
		$page->error = "Permission Denied";
		return;
	}
	$page = new \Site\Page();
	$product = new \Engineering\Product();
	
	if ($_REQUEST['product_id']) {
		$product = new \Engineering\Product($_REQUEST['product_id']);
	}
	elseif (isset($_REQUEST['code'])) {
		$product->get($_REQUEST['code']);
		if ($product->error) $page->error = $product->error;
	}
	elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$code = $GLOBALS['_REQUEST_']->query_vars_array[0];
		$product->get($code);
	}

	if (isset($_REQUEST['btn_submit'])) {
		$parameters = array();
		if (isset($_REQUEST['title'])) $parameters['title'] = $_REQUEST['title'];
		else {
			$page->error = "Title required";
		}
		if (isset($_REQUEST['description'])) $parameters['description'] = $_REQUEST['description'];
		if (isset($_REQUEST['code'])) $parameters['code'] = $_REQUEST['code'];

		app_log("Submitted product form",'debug',__FILE__,__LINE__);
		if ($product->id) {
			if ($product->update($parameters)) {
				$page->success = "Updates applied";
				app_log("Product updated",'debug',__FILE__,__LINE__);
			}
			else {
				$page->error = "Error saving updates: ".$product->error();
			}
		}
		else {
			if ($product->add($parameters)) {
				$page->success = "Product Created";
				app_log("Product created",'debug',__FILE__,__LINE__);
			}
			else {
				$page->error = "Error creating product: ".$product->error();
			}
		}
	}

	if ($product->id) {
		$product->details();
		$form['code'] = $product->code;
		$form['title'] = $product->title;
		$form['description'] = $product->description;
	}
	elseif ($page->error) {
		$form['code'] = $_REQUEST['code'];
		$form['title'] = $_REQUEST['title'];
		$form['description'] = $_REQUEST['description'];
	}
	else {
	}
?>
