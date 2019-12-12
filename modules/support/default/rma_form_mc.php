<?php
$page = new \Site\Page ();
$page->requireRole ( "support user" );

/**
 * add a new item included in the shipment
 *
 * @param int $package_id
 * @param
 *        	int$product_id
 * @param string $serial_number
 * @param Enum $condition
 * @param int $quantity
 * @param string $description
 */
function addShippedItem($package_id, $product_id, $serial_number, $condition, $quantity, $description) {
	$shippedItemDetails = array ();
	$shippedItemDetails ['package_id'] = $package_id;
	$shippedItemDetails ['product_id'] = $product_id;
	$shippedItemDetails ['serial_number'] = $serial_number;
	$shippedItemDetails ['condition'] = $condition;
	$shippedItemDetails ['quantity'] = $quantity;
	$shippedItemDetails ['description'] = $description;
	$shippingItem = new Shipping\Item ();
	$shippingItem->add ( $shippedItemDetails );
}

/**
 * for country name sorting
 *
 * @param string $a
 * @param string $b
 */
function sortNames($a, $b) {
	return strcmp($a->name, $b->name);
}

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
$rmaApprovedByName = $rma->approvedBy () ? $rma->approvedBy ()->full_name () : "";
$rmaDateApproved = date ( "m/d/Y", strtotime ( $rma->date_approved ) );
$rmaStatus = $rma->status;
$rmaProductCode = $rma->item ()->product ? $rma->item ()->product->code : "";
$rmaProductId = $rma->item ()->product->id ? $rma->item ()->product->id : "";
$rmaSerialNumber = $rma->item () ? $rma->item ()->serial_number : "";

// get the shipment in question if it exists
$shippingShipment = new \Shipping\Shipment ();
$shippingShipment->get ( $rmaCode );

// get existing geography for form fields
$countryList = new \Geography\CountryList ();
$allCountriesList = $countryList->find();
usort($allCountriesList, "sortNames");

// process the form submission for the return request
if ($_REQUEST ['form_submitted'] == 'submit') {

	// A shipping record is created status NEW.
	// Each item from the form including accessories is added to the shipment as a shipping_item record
	$registerLocationShipping = new \Register\Location ();
	$registerLocationBilling = new \Register\Location ();
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
	if (empty ( $_REQUEST ['billing_same_as_shipping'] )) {
		if (! $registerLocationBilling->findExistingByAddress ( $billingAddressParams )) $registerLocationBilling->add ( $billingAddressParams );
	}

	// upsert shipment info, use the location recently provided
	if (! $shippingShipment->id) {

		$parameters ['code'] = $rmaCode;
		$parameters ['document_number'] = uniqid ();
		$parameters ['date_entered'] = date ( "Y-m-d H:i:s" );
		$parameters ['status'] = 'NEW';
		$parameters ['send_contact_id'] = $rma->item ()->request->customer->id;
		$parameters ['rec_contact_id'] = $rma->approvedBy ()->id;
		$parameters ['send_location_id'] = $registerLocationShipping->id;
		$parameters ['instructions'] = (isset ( $_REQUEST ['delivery_instructions'] )) ? $_REQUEST ['delivery_instructions'] : '';

		// @TODO, this is just spectros default location?
		$parameters ['rec_location_id'] = 1;

		// @TODO, this is an organization_id??
		$parameters ['vendor_id'] = 0;

		// add shipment with package and items entries
		$shippingShipment->add ( $parameters );

		// add a default "1st" package to the shipment, there should be at least that
		$packageDetails = array ();
		$packageDetails ['shipment_id'] = $shippingShipment->id;
		$packageDetails ['number'] = 1;
		$packageDetails ['tracking_code'] = (isset ( $_REQUEST ['tracking_number'] )) ? $_REQUEST ['tracking_number'] : '';
		$packageDetails ['status'] = 'READY';
		$packageDetails ['condition'] = 'OK';
		$shippingPackage = new \Shipping\Package ();
		$shippingPackage->add ( $packageDetails );

		// each item from the form including accessories is added to the shipment as a shipping_item record
		addShippedItem ( $shippingPackage->id, $rmaProductId, $rmaSerialNumber, 'OK', 1, 'Main Monitor Unit' );
		if (! empty ( $_REQUEST ['power_cord'] )) addShippedItem ( $shippingPackage->id, $rmaProductId, $rmaSerialNumber, 'OK', 1, 'Power Cord' );
		if (! empty ( $_REQUEST ['filters'] )) addShippedItem ( $shippingPackage->id, $rmaProductId, $rmaSerialNumber, 'OK', 1, 'Filter' );
		if (! empty ( $_REQUEST ['battery'] )) addShippedItem ( $shippingPackage->id, $rmaProductId, $rmaSerialNumber, 'OK', 1, 'Battery' );
		if (! empty ( $_REQUEST ['carry_bag'] )) addShippedItem ( $shippingPackage->id, $rmaProductId, $rmaSerialNumber, 'OK', 1, 'Carrying Case' );
		if (! empty ( $_REQUEST ['usb_comm_cable'] )) addShippedItem ( $shippingPackage->id, $rmaProductId, $rmaSerialNumber, 'OK', 1, 'USB Cable' );
		if (! empty ( $_REQUEST ['cellular_access_point'] )) addShippedItem ( $shippingPackage->id, $rmaProductId, $rmaSerialNumber, 'OK', 1, 'Cellular Access Point' );
	}
	
	// RMA status is changed to CUSTOMER_HIP @TODO, why didn't the table have that in the ENUM values?
	$rma->update(array('status'=>'PRINTED'));
}

// process the form submission for the adding package and tracking details
if ($_REQUEST ['form_submitted'] == 'package_details_submitted') {

    $shippingPackage = new \Shipping\Package ();
    $shippingPackage->getByShippingID($shippingShipment->id);
	$packageDetails = array ();
	$packageDetails ['shipment_id'] = $shippingShipment->id;
	$packageDetails ['tracking_code'] = (isset ( $_REQUEST ['tracking_code'] )) ? $_REQUEST ['tracking_code'] : '';
	$packageDetails ['height'] = (isset ( $_REQUEST ['height'] )) ? floatval( $_REQUEST ['height'] ) : 0;
	$packageDetails ['width'] = (isset ( $_REQUEST ['width'] )) ? floatval( $_REQUEST ['width'] ) : 0;
	$packageDetails ['depth'] = (isset ( $_REQUEST ['depth'] )) ?  floatval( $_REQUEST ['depth'] ) : 0;
	$packageDetails ['weight'] = (isset ( $_REQUEST ['weight'] )) ? floatval( $_REQUEST ['weight'] ) : 0;		
	if (!empty($shippingPackage->id)) {
		$shippingPackage->update ( $packageDetails );
	} else {
		$shippingPackage->add ( $packageDetails );
	}
}

// set UI to submitted or not
$rmaSubmitted = false;
if ($shippingShipment->id) {
	$rmaSubmitted = true;
	$sentFromLocation = $shippingShipment->send_location ();
	$sentToLocation = $shippingShipment->rec_location ();
    $shippingPackage = new \Shipping\Package ();
    $shippingPackage->getByShippingID($shippingShipment->id);
}
