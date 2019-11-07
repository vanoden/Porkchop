<?php
	$page = new \Site\Page();
	$page->requireRole("support user");
	
    // get cooresponding RMA from possible input values
    $rma = new \Support\Request\Item\RMA();
    $rmaId = (isset($_REQUEST['id'])) ? $_REQUEST['id'] : 0;
    $rmaCode = (isset($_REQUEST['code'])) ? $_REQUEST['code'] : 0;
    if (isset($GLOBALS['_REQUEST_']->query_vars_array[0])) $rmaCode = $GLOBALS['_REQUEST_']->query_vars_array[0];
	if ($rmaId) {
		$rma = new \Support\Request\Item\RMA($_REQUEST['id']);
	} elseif ($rmaCode) {
		$rma->get($_REQUEST['code']);
	}
	
    // add events to page if they exist
	if ($rma->exists()) {
		$events = $rma->events();
	} else {
		$events = array();
	}
	
	// get any values for UI, check if they exist
	$rmaNumber = $rma->number() ? $rma->number() : "";
	$rmaItemId = $rma->item() ? $rma->item()->id : "";
	$rmaTicketNumber = $rma->item()->ticketNumber() ? $rma->item()->ticketNumber() : "";
	$rmaCustomerFullName = $rma->item()->request->customer ? $rma->item()->request->customer->full_name() : "";
	$rmaCustomerOrganizationName = $rma->item()->request->customer->organization->name ? $rma->item()->request->customer->organization->name : "";
	$rmaApprovedByName = $rma->approvedBy ? $rma->approvedBy->full_name() : "";
	$rmaDateApproved = date("m/d/Y", strtotime($rma->date_approved));
	$rmaStatus = $rma->status;
	$rmaProductCode = $rma->item()->product ? $rma->item()->product->code : "";
	$rmaSerialNumber = $rma->item() ? $rma->item()->serial_number : "";
    
    // process the form submission for the return request
    if ($_REQUEST['form_submitted'] == 'submit') {
    
        // A shipping record is created status NEW.
        //  Each item from the form including accessories is added to the shipment as a shipping_item record
        $shippingShipment = new \Shipping\Shipment();
        $registerLocation = new \Register\Location();
        $shippingShipment->get($rmaCode);
        $parameters = array();
        $parameters['code'] = $rmaCode;
        
        $billingAddressParams = array();
        $billingAddressParams['address_1'] = $_REQUEST['billing_address'];
        $billingAddressParams['address_2'] = $_REQUEST['billing_address2'];
        $billingAddressParams['city'] = $_REQUEST['billing_city'];
        $billingAddressParams['zip_code'] = $_REQUEST['billing_zip'];
        
        $shippingAddressParams = array();
        $shippingAddressParams['address_1'] = $_REQUEST['shipping_address'];
        $shippingAddressParams['address_2'] = $_REQUEST['shipping_address2'];
        $shippingAddressParams['city'] = $_REQUEST['shipping_city'];
        $shippingAddressParams['zip_code'] = $_REQUEST['shipping_zip'];

        if (!$registerLocation->findExistingByAddress($billingAddressParams)) $registerLocation->add($billingAddressParams);
        if (!$registerLocation->findExistingByAddress($shippingAddressParams)) $registerLocation->add($shippingAddressParams);
        
        // upsert shipment info, use the location recently provided
        if (!$shippingShipment->id) {
            $parameters['document_number'] = uniqid();
            $parameters['date_entered'] = date("Y-m-d H:i:s");
            $parameters['status'] = 'NEW';
            $parameters['send_contact_id'] = $rma->item()->request->customer->id;
            $parameters['rec_contact_id'] = $rma->approvedBy()->id;
            $parameters['rec_location_id'] = 0; // @TODO, this defaults to a Spectros Inc. default location??
            $parameters['vendor_id'] = 0; // @TODO, this is an organization_id??
            $parameters['send_location_id'] = $registerLocation->id;
            $shippingShipment->add($parameters);
        } else {
            $parameters['send_location_id'] = $registerLocation->id;
            $shippingShipment->update($parameters);            
        }
    }
