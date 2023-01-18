<?php
	$page = new \Site\Page();
	$page->fromRequest();
	$page->requirePrivilege('see sales quotes');
	
	// clean user input
	$organization_id = 0;
	if (isset($_REQUEST['organization_id'])) $organization_id = intval($_REQUEST['organization_id']);
	
	$member_id = 0;
	if (isset($_REQUEST['member_id'])) $member_id = intval($_REQUEST['member_id']);
	
	$billing_location = 0;
	if (isset($_REQUEST['billing_location'])) $billing_location = intval($_REQUEST['billing_location']);
	
	$shipping_location = 0;
	if (isset($_REQUEST['shipping_location'])) $shipping_location = intval($_REQUEST['shipping_location']);
	
	// get shipping vendor
	$shipping_vendor = "DHL";
	
    // shipping vendors available
    $shippingVendorList = new \Shipping\VendorList();
    $shippingVendors = $shippingVendorList->findUnique();
    if (isset($shipping_vendor) && in_array($shipping_vendor, $shippingVendors)) $shipping_vendor = $_REQUEST['shipping_vendor'];

	// Security - Only Register Module Operators or Managers can see other customers
	$organizationlist = new \Register\OrganizationList();
	
	// Initialize Parameter Array
	$find_parameters = array();
	$find_parameters['status'] = array('NEW','ACTIVE');

	// Get Count before Pagination
	$organizations = $organizationlist->find($find_parameters,true);
	if ($organizationlist->error()) $page->addError($organizationlist->error());

    // get members for organization
    $members = array();
    if (isset($organization_id) && intval($organization_id)) {
        $organization = new \Register\Organization($organization_id);
        if ($organization->error()) $page->addError($organization->error());
        $members = $organization->members('human',array('NEW','ACTIVE'));
    }

    // get contact info for selected member
    $locations = array();
    if (isset($_REQUEST['member_id']) && intval($_REQUEST['member_id'])) {
        $registerPerson = new \Register\Person($_REQUEST['member_id']);
        $contacts = $registerPerson->contacts();
        $contactMethods = array('phone' => array(), 'email' => array(), 'sms' => array(), 'facebook' => array(), 'insite' => array());
        foreach ($contacts as $contact) $contactMethods[$contact->type][] = $contact->value;

        // get default locations here
        $displayedOrganizations = array();
        $locations = $organization->locations();
    }

    $itemsInOrder = array();
	if (isset($_REQUEST['items_in_order'])) $itemsInOrder = explode(",", trim($_REQUEST['items_in_order'],','));

    // remove item from order
    if ($_REQUEST['btn_remove'] && !empty($_REQUEST['btn_remove'])) {
        if (($key = array_search($_REQUEST['btn_remove'], $itemsInOrder)) !== false) unset($itemsInOrder[$key]);  
    }

    // add item to order
    if ($_REQUEST['btn_add'] && !empty($_REQUEST['add_items_select']) && $_REQUEST['add_items_select'] != 0) $itemsInOrder[] = $_REQUEST['add_items_select'];

    // get existing ACTIVE items, but only the ones not added to the cart yet
    $itemsForOrder = array();
    $itemList = new \Product\ItemList();
    $itemsForSale = $itemList->find(array('type' => array('unique', 'inventory')));
    foreach ($itemsForSale as $itemForSale) if (!in_array($itemForSale->code, $itemsInOrder)) $itemsForOrder[] = $itemForSale; 
    
    $isReadyToQuote = false;
    if (!empty($organization_id) && !empty($member_id) && !empty($billing_location) && !empty($shipping_location) && count($itemsInOrder) > 1) $isReadyToQuote = true;
    
    
    print_r($isReadyToQuote);


    
