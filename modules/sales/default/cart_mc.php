<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('see sales quotes');

	/****************************************/
	/* Validate Form Data					*/
	/****************************************/
	// Get the order from Post or Get Vars
	if (isset($_REQUEST['order_id'])) {
		$order = new \Sales\Order($_REQUEST['order_id']);
	}
	elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$order = new \Sales\Order();
		$order->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
	}

	// Initialize Parameter Array
	$parameters = array();

	// Load Each of the necessary objects
	$form = array();
	if (!empty($_REQUEST['organization_id'])) {
		$organization = new \Register\Organization($_REQUEST['organization_id']);
		$parameters['organization_id'] = $organization->id;
		$form['organization_id'] = $organization->id;
	}
	else $organization = $order->organization();

	if (!empty($_REQUEST['customer_id'])) {
		$customer = new \Register\Customer($_REQUEST['customer_id']);
		$parameters['customer_id'] = $customer->id;
		$form['customer_id'] = $customer->id;
	}
	else $customer = $order->customer();

	if (!empty($_REQUEST['shipping_location'])) {
		$shipping_location = new \Register\Location($_REQUEST['shipping_location']);
		$parameters['shipping_location_id'] = $shipping_location->id;
		$form['shipping_location_id'] = $shipping_location->id;
	}
	else $shipping_location = $order->shipping_location();

	if (!empty($_REQUEST['billing_location'])) {
		$billing_location = new \Register\Location($_REQUEST['billing_location']);
		$parameters['billing_location_id'] = $billing_location->id;
		$form['billing_location_id'] = $billing_location->id;
	}
	else $billing_location = $order->billing_location();

	if (!empty($_REQUEST['shipping_vendor_id'])) {
		$shipping_vendor = new \Shipping\Vendor($_REQUEST['shipping_vendor_id']);
		$parameters['shipping_vendor_id'] = $shipping_vendor->id;
		$form['shipping_vendor_id'] = $shipping_vendor->id;
	}
	else $shipping_vendor = $order->shipping_vendor();

	/********************************************/
	/* Create a New Order						*/
	/********************************************/
	if ($order->id < 1 && $customer->id > 0 && $shipping_location->id > 0 && $billing_location->id > 0) {
		// We Have What We Need to Create an Order
		// New Order Parameters
		$parameters['salesperson_id'] = $GLOBALS['_SESSION_']->customer->id;
		$orderList = new \Sales\OrderList();
		$parameters['order_number'] = $orderList->nextNumber();

		$order->add($parameters);
		if ($order->error()) {
			$page->addError("Error: sales order could not be created: ".$order->error());
		}
		else {
			$page->appendSuccess("Sales Order Created");
		}
	}
	/********************************************/
	/* Update Existing Order					*/
	/********************************************/
	elseif ($order->id > 0) {
		// Update the order
		$order->update($parameters);
		if ($order->error()) $page->addError("Error updating order: ".$order->error());

		// Update any existing items
		if (isset($_REQUEST['items'])) {
			foreach ($_REQUEST['items'] as $item_id => $one) {
				$item_params = array();
				$item = $order->getItem($item_id);
				if ($_REQUEST['description'][$item_id] != $item->description) $item_params['description'] = $_REQUEST['description'][$item_id];
				if ($_REQUEST['price'][$item_id] != $item->unit_price) $item_params['unit_price'] = $_REQUEST['price'][$item_id];
				if ($item->product()->type != 'unique') {
					if ($_REQUEST['quantity'][$item_id] != $item->quantity) {
						if ($_REQUEST['quantity'][$item_id] <= 0) {
							$order->dropItem($item_id);
							$page->appendSuccess("Dropped item ".$item_id);
							continue;
						}
						else $item_params['quantity'] = $_REQUEST['quantity'][$item_id];
					}
				}
				else {
				if ($_REQUEST['serial_number'][$item_id] != $item->serial_number) $item_params['serial_number'] = $_REQUEST['serial_number'][$item_id];
				}
				if (count($item_params)) {
					if ($item->update($item_params)) {
						$page->appendSuccess("Updated line ".$item_id);
					}
					else {
						$page->addError("Cannot update order item: ".$item->error());
					}
				}
			}
		}

		// Add a new Item
		if ($_REQUEST['new_item']) {
			$product = new \Product\Item($_REQUEST['new_item']);
			if (!$product->exists()) $page->addError("Product not found");
			elseif ($product->type != 'unique') {
				// Don't want numerous lines of same product
				// unless it's a Unique product.
				// See if Order has An Item with that Product
				$line = $order->productLine($product->id);
				if (!empty($line)) {
					// Update the existing line
					$line->update(array(
						'quantity'	=> $line->quantity + $_REQUEST['product_id'][$item_id]
					));
					if ($line->error()) $page->addError($line->error());
					else $page->appendSuccess("Incremented quantity of ".$product->code);
				}
				else {
					// Add as a new line
					$order->addItem(array(
						'product_id'	=> $product->id,
						'description'	=> $product->description,
						'quantity'		=> 1,
						'unit_price'	=> $product->currentPrice()
					));
					if ($order->error()) $page->addError($order->error());
					else $page->appendSuccess("Added ".$product->code);
				}
			}
			// Unique Product - Add a new line
			else {
				$order->addItem(array(
					'product_id'	=> $product->id,
					'description'	=> $product->description,
					'quantity'		=> 1,
					'unit_price'	=> $product->currentPrice()
				));
				if ($order->error()) $page->addError($order->error());
				else $page->appendSuccess("Added ".$product->code);
			}
		}

		// remove item from order
		if (!empty($_REQUEST['remove_item'])) {
			$order->appendSuccess("Removing item ".$_REQUEST['remove_item']);
			$order->dropItem($_REQUEST['remove_item']);
		}
	}

	/********************************************/
	/* Update Order Status Per Footer Buttons	*/
	/********************************************/
	if (isset($_REQUEST['btn_submit'])) {
		if ($page->errorCount() > 0) {
			$page->addError("Not updating order status");
		}
		if (preg_match('/Save/',$_REQUEST['btn_submit'])) {
			header("Location: /_sales/orders");

		}
		elseif (preg_match('/Quote/',$_REQUEST['btn_submit'])) {
			if ($order->quote()) {
				header("Location: /_sales/orders");
				exit;
			}
			else {
				$page->addError($order->error());
			}
		}
		elseif (preg_match('/Approve/',$_REQUEST['btn_submit'])) {
			if ($order->approve()) {
				header("Location: /_sales/orders");
				exit;
			}
			else {
				$page->addError($order->error());
			}
		}
		elseif (preg_match('/Cancel/',$_REQUEST['btn_submit'])) {
			if ($order->cancel()) {
				header("Location: /_sales/orders");
				exit;
			}
			else {
				$page->addError($order->error());
			}
		}
	}

	/****************************************/
	/* Load Resources Needed by the Form	*/
	/****************************************/
	// Organization List
	$organizationlist = new \Register\OrganizationList();
	$organizations = $organizationlist->find();
	if ($organizationlist->error()) $page->addError($organizationlist->error());
	if (empty($organizations)) $page->addError("Error: no organizations to create a sales order");

	// Customes from Selected Assocation
	$customers = array();
	if ($organization->id > 0) {
		$customers = $organization->members('human',array('NEW','ACTIVE'));
		if (empty($customers)) $page->addError("Error: no NEW or ACTIVE customers in organization to create sales order");
		else {
			$locations = $organization->locations();
			if (empty($locations)) $page->addError("Error: no NEW or ACTIVE locations in organization to create sales order");
		}
	}

	// Get Shipping Vendors
	$shippingVendorList = new \Shipping\VendorList();
	$shippingVendors = $shippingVendorList->find();

	// Get Available Products
	$productList = new \Product\ItemList();
	$products = $productList->find(array('type' => array('unique','inventory','service')));

	// Get Existing Order Items
	$orderItems = array();
	if ($order->id) {
		$orderItems = $order->items(array('status' => 'OPEN'));
	}

	// Prefill Form Fields from Database Info
	if ($order->id > 0) {
		$form["organization_id"] = $organization->id;
		$form["customer_id"] = $customer->id;
		$form["shipping_location_id"] = $shipping_location->id;
		$form["billing_location_id"] = $billing_location->id;
		$form["shipping_vendor_id"] = $shipping_vendor->id;
	}

	// Populate Instructions
	if ($form["organization_id"] < 1) $page->instructions = "Select an Organization";
	elseif ($form["customer_id"] < 1) $page->instructions = "Select a Customer";
	elseif ($form["billing_location_id"] < 1) $page->instructions = "Select a Billing Location";
	elseif ($form["shipping_location_id"] < 1) $page->instructions = "Select a Shipping Location";
	elseif ($form["shipping_vendor_id"] < 1) $page->instructions = "Select a Shipping Vendor";
	elseif ($order->id > 0) $page->instructions = "Add Items to Order.<br>Click 'Save For Later' to keep the order in new status and return for completion.<br>Click 'Prepare Quote' to provide a Quote for a customer.<br>Click 'Approve Order' to initiate fullfilment.";

	// Breadcrumbs and Title
	$page->addBreadcrumb("Sales");
	$page->addBreadcrumb("Orders","/_sales/orders");
	if ($order->id) {
		$page->addBreadCrumb("Order ".$order->number(),"/_sales/cart?order_id=".$order->id);
		$page->title("Sales Order ".$order->number());
	}
	else {
		$page->title("Create Order");
	}
