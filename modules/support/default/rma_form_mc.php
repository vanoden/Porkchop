<?php
$page = new \Site\Page ();
$page->requireRole ( "support user" );

// get cooresponding RMA from possible input values
$rma = new \Support\Request\Item\RMA ();
$rmaId = (isset ( $_REQUEST ['id'] )) ? $_REQUEST ['id'] : 0;
$rmaCode = (isset ( $_REQUEST ['code'] )) ? $_REQUEST ['code'] : 0;

if (isset ( $GLOBALS ['_REQUEST_']->query_vars_array [0] )) $rmaCode = $GLOBALS ['_REQUEST_']->query_vars_array [0];
if ($rmaId) {
	$rma = new \Support\Request\Item\RMA ( $_REQUEST ['id'] );
} elseif ($rmaCode) {
	$rma->get ( $rmaCode );
}

// add events to page if they exist
if ($rma->exists ()) {
	$events = $rma->events ();
} else {
	$events = array ();
}

// get any values for UI, check if they exist
$rmaNumber = $rma->number () ? $rma->number () : "";
$rmaItemId = $rma->item () ? $rma->item ()->id : "";
$rmaTicketNumber = $rma->item ()->ticketNumber () ? $rma->item ()->ticketNumber () : "";
$rmaCustomerFullName = $rma->item ()->request->customer ? $rma->item ()->request->customer->full_name () : "";
$rmaCustomerOrganizationName = $rma->item ()->request->customer->organization->name ? $rma->item ()->request->customer->organization->name : "";
$rmaApprovedByName = $rma->approvedBy ? $rma->approvedBy->full_name () : "";
$rmaDateApproved = date ( "m/d/Y", strtotime ( $rma->date_approved ) );
$rmaStatus = $rma->status;
$rmaProductCode = $rma->item ()->product ? $rma->item ()->product->code : "";
$rmaSerialNumber = $rma->item () ? $rma->item ()->serial_number : "";

//////////////////////////////////////////////
/////// TESTING //////////////////////////////
//////////////////////////////////////////////
//////////////////////////////////////////////

//$_REQUEST['shipping_firstname'] = 'shipping_firstname';
//$_REQUEST['shipping_address'] = 'shipping_address';
//$_REQUEST['shipping_address2'] = 'shipping_address2';
//$_REQUEST['shipping_city'] = 'shipping_city';
//$_REQUEST['shipping_state'] = 'shipping_state';
//$_REQUEST['shipping_zip'] = '123456';
//$_REQUEST['billing_same_as_shipping'] = 'billing_same_as_shipping';

//$_REQUEST['billing_firstname'] = 'billing_firstname';
//$_REQUEST['billing_address'] = 'billing_address';
//$_REQUEST['billing_address2'] = 'billing_address2';
//$_REQUEST['billing_city'] = 'billing_address2';
//$_REQUEST['billing_state'] = 'billing_address2';
//$_REQUEST['billing_zip'] = '123456';

//$_REQUEST['power_cord'] = 'power_cord';
//$_REQUEST['power_cord'] = 'power_cord';
//$_REQUEST['power_cord'] = 'power_cord';
//$_REQUEST['carry_bag'] = 'carry_bag';
//$_REQUEST['usb_comm_cable'] = 'usb_comm_cable';
//$_REQUEST['usb_comm_cable'] = 'cellular_access_point';
//$_REQUEST['cellular_access_point'] = 'cellular_access_point';
//$_REQUEST['tracking_numbers'] = 'tracking_numbers';
//$_REQUEST['agree_package_properly'] = 'agree_package_properly';
//$_REQUEST['agree_payment_received'] = 'agree_payment_received';
//$_REQUEST['form_submitted'] = 'submit';

//////////////////////////////////////////////////
/////// END TESTING //////////////////////////////
//////////////////////////////////////////////////
//////////////////////////////////////////////////


// process the form submission for the return request
if ($_REQUEST ['form_submitted'] == 'submit') {

	// A shipping record is created status NEW.
	// Each item from the form including accessories is added to the shipment as a shipping_item record
	$shippingShipment = new \Shipping\Shipment ();
	$registerLocationShipping = new \Register\Location ();
	$registerLocationBilling = new \Register\Location ();
	$shippingShipment->get ( $rmaCode );
	$parameters = array ();
	$parameters ['code'] = $rmaCode;

	$billingAddressParams = array ();
	$billingAddressParams ['name'] = $_REQUEST ['billing_address'];
	$billingAddressParams ['address_1'] = $_REQUEST ['billing_address'];
	$billingAddressParams ['address_2'] = $_REQUEST ['billing_address2'];
	$billingAddressParams ['city'] = $_REQUEST ['billing_city'];
	$billingAddressParams ['zip_code'] = $_REQUEST ['billing_zip'];
	$billingAddressParams ['notes'] = "";	

    // @TODO defaulting to America/MA for now	
	$billingAddressParams ['region_id'] = 4075;
	$billingAddressParams ['country_id'] = 217;

	$shippingAddressParams = array ();
	$shippingAddressParams ['name'] = $_REQUEST ['shipping_address'];
	$shippingAddressParams ['address_1'] = $_REQUEST ['shipping_address'];
	$shippingAddressParams ['address_2'] = $_REQUEST ['shipping_address2'];
	$shippingAddressParams ['city'] = $_REQUEST ['shipping_city'];
	$shippingAddressParams ['zip_code'] = $_REQUEST ['shipping_zip'];
	$shippingAddressParams ['notes'] = "";	
	
    // @TODO defaulting to America/NY for now	
	$shippingAddressParams ['region_id'] = 4075;
	$shippingAddressParams ['country_id'] = 217;

    // add user address(es) if they don't exist yet
	if (! $registerLocationShipping->findExistingByAddress ( $shippingAddressParams )) $registerLocationShipping->add ( $shippingAddressParams );
    if (!empty($_REQUEST['billing_same_as_shipping'])) {
        if (! $registerLocationBilling->findExistingByAddress ( $billingAddressParams )) $registerLocationBilling->add ( $billingAddressParams );
    }

	// upsert shipment info, use the location recently provided
	if (! $shippingShipment->id) {
	
    	$parameters ['code'] = uniqid ();
		$parameters ['document_number'] = uniqid ();
		$parameters ['date_entered'] = date ( "Y-m-d H:i:s" );
		$parameters ['status'] = 'NEW';
		$parameters ['send_contact_id'] = $rma->item ()->request->customer->id;
		$parameters ['rec_contact_id'] = $rma->approvedBy ()->id;
        $parameters ['send_location_id'] = $registerLocationShipping->id;

        // @TODO, this is just spectros default location?
		$parameters ['rec_location_id'] = 1;

        // @TODO, this is an organization_id??
		$parameters ['vendor_id'] = 0; 	

        // add shipment with package and items entries				
		$shippingShipment->add ( $parameters );
		
		// each item from the form including accessories is added to the shipment as a shipping_item record
		    // INSERT shipping_packages (on shipment_id = shipping_shipments->id)
		    // INSERT shipping_items (on package_id = shipping_packages->id)
	}
}
