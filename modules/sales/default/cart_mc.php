<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('see sales quotes');

	// CSRF Protection
	if($_SERVER['REQUEST_METHOD'] == 'POST' && ! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])){
		echo "Invalid Request";
		die();
	}

	/****************************************/
	/* Validate Form Data					*/
	/****************************************/
	// Get the order from Post or Get Vars
	$can_proceed = true;
	
	$request = new \HTTP\Request();
	$order_id = $_REQUEST['order_id'] ?? null;
	if ($request->validInteger($order_id)) {
		$order = new \Sales\Order($order_id);
	}
	elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$order = new \Sales\Order();
		$order->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
	}

	// Initialize Parameter Array
	$parameters = array();

	// Load Each of the necessary objects
	$form = array(
		'organization_id' => 0,
		'customer_id' => 0,
		'shipping_location_id' => 0,
		'billing_location_id' => 0,
		'shipping_vendor_id' => 0
	);
	$organization_id = $_REQUEST['organization_id'] ?? null;
	if ($request->validInteger($organization_id)) {
		$organization = new \Register\Organization($organization_id);
		$parameters['organization_id'] = $organization->id;
		$form['organization_id'] = $organization->id;
	}
	else $organization = $order->organization();

	$customer_id = $_REQUEST['customer_id'] ?? null;
	if ($request->validInteger($customer_id)) {
		$customer = new \Register\Customer($customer_id);
		$parameters['customer_id'] = $customer->id;
		$form['customer_id'] = $customer->id;
	}
	else $customer = $order->customer();

	$shipping_location = $_REQUEST['shipping_location'] ?? null;
	if ($request->validInteger($shipping_location)) {
		$shipping_location = new \Register\Location($shipping_location);
		$parameters['shipping_location_id'] = $shipping_location->id;
		$form['shipping_location_id'] = $shipping_location->id;
	}
	else $shipping_location = $order->shipping_location();

	$billing_location = $_REQUEST['billing_location'] ?? null;
	if ($request->validInteger($billing_location)) {
		$billing_location = new \Register\Location($billing_location);
		$parameters['billing_location_id'] = $billing_location->id;
		$form['billing_location_id'] = $billing_location->id;
	}
	else $billing_location = $order->billing_location();

	$shipping_vendor_id = $_REQUEST['shipping_vendor_id'] ?? null;
	if ($request->validInteger($shipping_vendor_id)) {
		$shipping_vendor = new \Shipping\Vendor($shipping_vendor_id);
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
				
				$description = $_REQUEST['description'][$item_id] ?? null;
				if ($request->validText($description) && $description != $item->description) 
					$item_params['description'] = $description;
				
				$price = $_REQUEST['price'][$item_id] ?? null;
				if ($request->validDecimal($price) && $price != $item->unit_price) 
					$item_params['unit_price'] = $price;
				
				if ($item->product()->type != 'unique') {
					$quantity = $_REQUEST['quantity'][$item_id] ?? null;
					if ($request->validInteger($quantity) && $quantity != $item->quantity) {
						if ($quantity <= 0) {
							$order->dropItem($item_id);
							$page->appendSuccess("Dropped item ".$item_id);
							continue;
						}
						else $item_params['quantity'] = $quantity;
					}
				}
				else {
					$serial_number = $_REQUEST['serial_number'][$item_id] ?? null;
					if ($request->validText($serial_number) && $serial_number != $item->serial_number) 
						$item_params['serial_number'] = $serial_number;
				}
				if (count($item_params)) {
					if ($item->update($item_params)) {
						$page->appendSuccess("Updated line ".$item_id);
					}
					else {
						$page->addError("Cannot update order item: ".$item->error());
						$can_proceed = false;
					}
				}
			}
		}

		// Add a new Item
		$new_item = $_REQUEST['new_item'] ?? null;
		if ($request->validInteger($new_item)) {
			$product = new \Product\Item($new_item);
			if (!$product->exists()) {
				$page->addError("Product not found");
				$can_proceed = false;
			}
			elseif ($product->type != 'unique') {
				// Don't want numerous lines of same product
				// unless it's a Unique product.
				// See if Order has An Item with that Product
				$line = $order->productLine($product->id);
				if (!empty($line)) {
					// Update the existing line
					$product_id_quantity = $_REQUEST['product_id'][$item_id] ?? 0;
					if (!$request->validInteger($product_id_quantity)) $product_id_quantity = 0;
					
					$line->update(array(
						'quantity'	=> $line->quantity + $product_id_quantity
					));
					if ($line->error()) {
						$page->addError($line->error());
						$can_proceed = false;
					}
					else $page->appendSuccess("Incremented quantity of ".$product->code);
				}
				else {
					// Add as a new line
					$order->addItem(array(
						'product_id'	=> $product->id,
						'description'	=> $product->description,
						'quantity'		=> 1,
						'unit_price'	=> $product->currentPrice()->amount
					));
					if ($order->error()) {
						$page->addError($order->error());
						$can_proceed = false;
					}
					else $page->appendSuccess("Added ".$product->code);
				}
			}
			// Unique Product - Add a new line
			else {
				$order->addItem(array(
					'product_id'	=> $product->id,
					'description'	=> $product->description,
					'quantity'		=> 1,
					'unit_price'	=> $product->currentPrice()->amount
				));
				if ($order->error()) {
					$page->addError($order->error());
					$can_proceed = false;
				}
				else $page->appendSuccess("Added ".$product->code);
			}
		}

		// remove item from order
		$remove_item = $_REQUEST['remove_item'] ?? null;
		if ($request->validInteger($remove_item)) {
			$order->appendSuccess("Removing item ".$remove_item);
			$order->dropItem($remove_item);
		}
	}

	/********************************************/
	/* Update Order Status Per Footer Buttons	*/
	/********************************************/
	$btn_submit = $_REQUEST['btn_submit'] ?? null;
	if ($request->validText($btn_submit)) {
		if ($page->errorCount() > 0) {
			$page->addError("Not updating order status");
			$can_proceed = false;
		}
		
		if ($can_proceed) {
			if (preg_match('/Save/',$btn_submit)) {
				header("Location: /_sales/orders");
			}
			elseif (preg_match('/Quote/',$btn_submit)) {
				if ($order->quote()) {
					header("Location: /_sales/orders");
					exit;
				}
				else {
					$page->addError($order->error());
				}
			}
			elseif (preg_match('/Approve/',$btn_submit)) {
				if ($order->approve()) {
					header("Location: /_sales/orders");
					exit;
				}
				else {
					$page->addError($order->error());
				}
			}
			elseif (preg_match('/Cancel/',$btn_submit)) {
				if ($order->cancel()) {
					header("Location: /_sales/orders");
					exit;
				}
				else {
					$page->addError($order->error());
				}
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
	$page->setAdminMenuSection("Sales");  // Keep Sales section open
