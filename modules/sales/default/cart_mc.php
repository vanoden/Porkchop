<?php
	$page = new \Site\Page();
	$page->fromRequest();
	$page->requirePrivilege('see sales quotes');
	
	print "<!--";
	print_r($_REQUEST);
    print "-->";
    
	// get sales order if existing from URL
	$salesOrder = new \Sales\Order();
	if (isset($GLOBALS['_REQUEST_']->query_vars_array[0])) {
    	$salesOrder->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
    	if (!empty($salesOrder->id)) {
        	$_REQUEST['organization_id'] = $salesOrder->organization_id;
        	$_REQUEST['member_id'] = $salesOrder->customer_id;
        	$_REQUEST['order_code'] = $salesOrder->code;
    	}
	}

	// clean user input
	$organization_id = 0;
	if (isset($_REQUEST['organization_id'])) $organization_id = intval($_REQUEST['organization_id']);
	
	$member_id = 0;
	if (isset($_REQUEST['member_id'])) $member_id = intval($_REQUEST['member_id']);
	
    $order_code = '';
	if (isset($_REQUEST['order_code'])) $order_code = $_REQUEST['order_code'];
	
    $order_id = 0;
	if (isset($_REQUEST['order_id'])) $order_id = intval($_REQUEST['order_id']);
	
	// add new or find existing sales order
    if (empty($order_code) && !empty($member_id) && !empty($organization_id)) {
    
        $salesOrderAdded = $salesOrder->add(
            array(
                'customer_id' => $member_id, 
                'salesperson_id' => $GLOBALS ['_SESSION_']->customer->id, 
                'status' => 'NEW',
                'customer_order_number' => rand(10000, 100000)
            )
        );
        if (!$salesOrderAdded) {
            $page->addError("Error: sales order could not be created");
        } else {
            $page->appendSuccess("Sales Order Created");
        }
        
        // apply organization
        $salesOrderUpdated = $salesOrder->update(array('status' => 'NEW', 'organization_id' => $organization_id));
        $order_code = $salesOrder->code;
        $order_id = $salesOrder->id;
        if (!$salesOrderUpdated) {
            $page->addError("Error: could not apply organization to Sales Order");
        } else {
            $page->appendSuccess("Sales Order applied to organization");
        }
        
    } else {
        $salesOrder->get($order_code);
        $order_id = $salesOrder->id;
    }
    
	$billing_location = 0;
	if (isset($_REQUEST['billing_location'])) {
    	$billing_location = intval($_REQUEST['billing_location']);
	} else {
    	$billing_location = $salesOrder->billing_location_id;
	}
	
	if (!empty($billing_location)) {
    	$salesOrderUpdated = $salesOrder->update(array('status' => $salesOrder->status, 'billing_location_id' => $billing_location));
        if (!$salesOrderUpdated) {
            $page->addError("Error: could not apply a billing location to Sales Order");
        } else {
            $page->appendSuccess("Sales Order applied a billing location");
        }
	}
	
	$shipping_location = 0;
	if (isset($_REQUEST['shipping_location'])) {
    	$shipping_location = intval($_REQUEST['shipping_location']);
	} else {
    	$shipping_location = $salesOrder->shipping_location_id;
	}
	
	if (!empty($shipping_location)) {
    	$salesOrderUpdated = $salesOrder->update(array('status' => $salesOrder->status, 'shipping_location_id' => $shipping_location));
        if (!$salesOrderUpdated) {
            $page->addError("Error: could not apply shipping location to Sales Order");
        } else {
            $page->appendSuccess("Sales Order applied a shipping location");
        }    	
	} 
    
	// get shipping vendor
	$shipping_vendor = "DHL";
	
    // shipping vendors available
    $shippingVendorList = new \Shipping\VendorList();
    $shippingVendors = $shippingVendorList->findUnique();
    if (isset($_REQUEST['shipping_vendor']) && in_array($_REQUEST['shipping_vendor'], $shippingVendors)) $shipping_vendor = $_REQUEST['shipping_vendor'];

	// Security - Only Register Module Operators or Managers can see other customers
	$organizationlist = new \Register\OrganizationList();
	
	// Initialize Parameter Array
	$find_parameters = array();
	$find_parameters['status'] = array('NEW','ACTIVE');

	// Get Count before Pagination
	$organizations = $organizationlist->find($find_parameters,true);
	if ($organizationlist->error()) $page->addError($organizationlist->error());
    if (empty($organizations)) $page->addError("Error: no organizations to create a sales order");

    // get members for organization
    $members = array();
    if (isset($organization_id) && intval($organization_id)) {
        $organization = new \Register\Organization($organization_id);
        $members = $organization->members('human',array('NEW','ACTIVE'));
        if (empty($members)) $page->addError("Error: no NEW or ACTIVE members in organization to create sales order");
    }
    
    // get contact info for selected member
    $locations = array();
    if (!empty($member_id)) {
        $registerPerson = new \Register\Person($member_id);
        $contacts = $registerPerson->contacts();
        $contactMethods = array('phone' => array(), 'email' => array(), 'sms' => array(), 'facebook' => array(), 'insite' => array());
        foreach ($contacts as $contact) $contactMethods[$contact->type][] = $contact->value;

        // get default locations here
        $displayedOrganizations = array();
        if (!empty($organization)) $locations = $organization->locations();
    }

    // add item to order and sync the sales order items
    $itemsInOrder = array();
    if (isset($_REQUEST['items_in_order'])) $itemsInOrder = explode(",", trim($_REQUEST['items_in_order'],','));
    if (isset($_REQUEST['btn_add']) && !empty($_REQUEST['add_items_select'])) $itemsInOrder[] = $_REQUEST['add_items_select'];
    
    $salesOrderItems = $salesOrder->items();
    
	print "<!-- salesOrderItems DEBUG: ";
	print_r($itemsInOrder);
	print "-->";
    
    foreach ($itemsInOrder as $itemCode) {
    
	    print "<!-- foreach ($itemsInOrder as $itemCode) DEBUG: ";
	    print_r($itemCode);
	    print "-->";
    
        if (empty($itemCode)) continue;

        // add item if not in order
        $itemInSalesOrder = false;
        $itemInCart = new \Product\Item();
        $itemInCart->get($itemCode);
        foreach ($salesOrderItems as $salesOrderItem) if ($salesOrderItem->product_id == $itemInCart->id) $itemInSalesOrder = true;
        if (!$itemInSalesOrder) {
        
            // get current set price for product, else default to 0
            $price = 0;
            $currentPrice = $itemInCart->currentPrice();
            if (!empty($currentPrice)) {
                $price = $currentPrice->amount;
                $page->appendSuccess("ACTIVE price located for Product " . $itemCode);
            } else {
                $page->addError("Product " . $itemCode . " doesn't have an ACTIVE price set. [<a href='/_product/report'>Find Product</a>]");
            }
            $itemAdded = $salesOrder->addItem (
                array (
                    "order_id" => $order_id,
                    "product_id" => $itemInCart->id,
                    "description" => $itemInCart->description,
                    "quantity" => 1,
                    "unit_price" => $price,
                    "status" => "OPEN"
                )
            );
            
            if (!$itemAdded) {
                $page->addError("Error Adding Item to Order");
            } else {
                $page->appendSuccess("Sales Order item has been added");
            }  
        }
    }
    
    // update the order with custom prices or descriptions and build the list to show in the UI
    $itemsInOrder = array();
    if (!empty($salesOrder->id)) {
        $salesOrderItems = $salesOrder->items();
        foreach ($salesOrderItems as $salesOrderItem) {
            $itemInCart = new \Product\Item($salesOrderItem->product_id, true);
            $salesOrderItemUpdated = $salesOrderItem->update(
                array(
                    'quantity' => $_REQUEST["qty-".$itemInCart->code],
                    'description' => $_REQUEST["description-".$itemInCart->code],
                    'unit_price' => $_REQUEST["price-".$itemInCart->code]
                )        
            );
            $itemsInOrder[] = $itemInCart->code;        
            if (!$salesOrderItemUpdated) {
                $page->addError("Error Updating Sales Item");
            } else {
                $page->appendSuccess("Sales Order item has been updated");
            }
        }    
    }
    
    // remove item from order
    if (isset($_REQUEST['btn_remove']) && !empty($_REQUEST['btn_remove'])) {
    
        $itemToRemove = new \Product\Item();
        $itemToRemove->get($_REQUEST['btn_remove']);
        
        $saleOrderItem = new \Sales\Order\Item();
        $saleOrderItem->getByProductIdOrderId($itemToRemove->id, $order_id);
        $saleOrderItem->delete();

        if (($key = array_search($_REQUEST['btn_remove'], $itemsInOrder)) !== false) unset($itemsInOrder[$key]);
    }

    // get existing ACTIVE items for the add products dropdown, but ONLY the ones not added to the cart yet
    $itemsForOrder = array();
    $itemList = new \Product\ItemList();
    $itemsForSale = $itemList->find(array('type' => array('unique', 'inventory')));
    foreach ($itemsForSale as $itemForSale) if (!in_array($itemForSale->code, $itemsInOrder)) $itemsForOrder[] = $itemForSale; 
    
    $isReadyToQuote = false;
    if (!empty($organization_id) && !empty($member_id) && !empty($billing_location) && !empty($shipping_location) && count($itemsInOrder) > 0) $isReadyToQuote = true;
    
    // if we're quoting or approving the order update as such
    if (isset($_REQUEST['btn_quote'])) $salesOrder->update(array('status' => 'QUOTE')); 
    if (isset($_REQUEST['btn_create'])) $salesOrder->approve();
    
	print "<!--";
	print_r($page);
	print "-->";
	
	print "<!-- DB ERRORS: ";	
	print_r($GLOBALS['_database']->ErrorMsg());
	print "-->";
		
	print "<!-- REQUEST DEBUG: ";
	print_r($_REQUEST);
	print "-->";    
