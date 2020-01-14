<?php
$page = new \Site\Page ();
$page->requireRole ( "support user" );

$optional_contents = array(
	'power_cord'	=> 'Power Cord',
	'filters'		=> 'Filters',
	'battery'		=> 'Battery',
	'carry_bag'		=> 'Carrying case',
	'usb_comm_cable'	=> 'USB Cable',
	'cellular_access_point'	=> 'Cellular Access Point'
);

# Get Warehouse Address
$configuration = new \Site\Configuration("module/support/rma_location_id");
if ($configuration->value()) $receive_location_id = $configuration->value();
else $page->addError("Default RMA Address Not Configured");

# Get Misc Inventory Product
$misc_inventory_code = 'misc';
$misc_inventory_item = new \Product\Item();
if (! $misc_inventory_item->get($misc_inventory_code)) {
	$page->addError("No product found for '$misc_inventory_code'");
}
app_log("Misc Item ID: ".$misc_inventory_item->id,'notice');

/**
 * for country name sorting
 *
 * @param string $a
 * @param string $b
 */
function sortNames($a, $b) {
	return strcmp($a->name, $b->name);
}

/**
 * for any ORM objects log possible errors to the page and error_log
 *
 * @param obj $DataObject
 */
function logErrors($dataObject, $page) {
    try {
        if (method_exists ( $dataObject ,'error' )) {
            if (!empty($dataObject->error())) {
                if (!stristr($dataObject->error(), "no records found")) {
                    $page->addError(get_class($dataObject) . ': ' . $dataObject->error());
                    app_log(get_class($dataObject) . ': ' . $dataObject->error(),'error');
                }
            }
        }
    } catch (Exception $e) {}
}

// get the addresses known for given customer and customer organization
$customerId = $GLOBALS['_SESSION_']->customer->id;
$organization = $GLOBALS['_SESSION_']->customer->organization;

// get cooresponding RMA from possible input values
$rma = new \Support\Request\Item\RMA ();
$rmaId = (isset ( $_REQUEST ['id'] )) ? $_REQUEST ['id'] : 0;
$rmaCode = (isset ( $_REQUEST ['code'] )) ? $_REQUEST ['code'] : 0;
if (isset ( $GLOBALS ['_REQUEST_']->query_vars_array [0] )) $rmaCode = $GLOBALS ['_REQUEST_']->query_vars_array [0];
if ($rmaId) {
	$rma = new \Support\Request\Item\RMA ( $_REQUEST ['id'] );
} elseif ($rmaCode) {
	if (!$rma->get ( $rmaCode )) $page->addError("Error getting RMA: ".$rma->error());
}
else {
	$page->addError("RMA Code or ID required");
}

// add events to page if they exist
if ($rma->exists ()) {
	$events = $rma->events ();
} else {
	$page->addError("RMA $rmaCode Not Found");
}

// get any values for UI, check if they exist
if ($page->errorCount() < 1) {
	$rmaNumber = $rma->number () ? $rma->number () : "";
	$rmaItem = $rma->item();
	$rmaItemId = $rmaItem ? $rmaItem->id : "";
	$rmaTicketNumber = $rmaItem->ticketNumber () ? $rmaItem->ticketNumber () : "";
	$rmaCustomerFullName = $rmaItem->request->customer ? $rmaItem->request->customer->full_name () : "";
	$rmaCustomerOrganizationName = $rmaItem->request->customer->organization->name ? $rmaItem->request->customer->organization->name : "";
	$rmaApprovedByName = $rma->approvedBy () ? $rma->approvedBy ()->full_name () : "";
	$rmaDateApproved = date ( "m/d/Y", strtotime ( $rma->date_approved ) );
	$rmaStatus = $rma->status;
	$rmaProduct = $rmaItem->product;
	app_log("RMA Product: ".$rmaItem->product->id,'notice');

	$rmaSerialNumber = $rmaItem ? $rmaItem->serial_number : "";
	$organization = $rmaItem->request->customer->organization;

	// make sure customer belongs to the RMA, or we're an admin user wishing to view it
	$authorized = true;
	if ( $rmaItem->request->customer->id != $GLOBALS['_SESSION_']->customer->id ) $authorized = false;
	if ( $GLOBALS['_SESSION_']->customer->has_role('support user') ) $authorized = true;

	// get the shipment in question if it exists
	$shippingShipment = new \Shipping\Shipment ();
	$shippingDocument = sprintf("RMA%06d",$rma->number());
	while ($shippingShipment->get ( $shippingDocument )) {
		if (preg_match('/^RMA(\d{6})\-(\d+)$/',$shippingDocument,$matches)) {
			$shippingDocument = "RMA".$matches[1]."-".$matches[2] + 1;
		}
		else {
			$shippingDocument .= "-1";
		}
	}
	
	// get existing geography for form fields
	$countryList = new \Geography\CountryList ();
	$allCountriesList = $countryList->find();
	usort($allCountriesList, "sortNames");

	// process the form submission for the return request
	if (isset($_REQUEST['form_submitted']) && $_REQUEST ['form_submitted'] == 'submit') {
	
		// A shipping record is created status NEW.
		// Each item from the form including accessories is added to the shipment as a shipping_item record
		$parameters = array ();
		//$parameters ['code'] = $rmaCode;
	
		// upsert shipment info, use the location recently provided
		if (! $shippingShipment->id) {
	
			//$parameters ['code'] = $rmaCode;
			$parameters ['document_number'] = $shippingDocument;
			$parameters ['date_entered'] = date ( "Y-m-d H:i:s" );
			$parameters ['status'] = 'NEW';
			$parameters ['send_customer_id'] = $rmaItem->request->customer->id;
			$parameters ['receive_customer_id'] = $rma->approvedBy ()->id;
			$parameters ['receive_location_id'] = $receive_location_id;
	
			if (!empty($_REQUEST ['shipping_address_picker'])) {
				$registerLocationShipping = new \Register\Location($_REQUEST['shipping_address_picker']);
				if (! $registerLocationShipping->id) {
					$page->addError("Error finding location: ".$registerLocationShipping->error());
				}
			} else {
				$registerLocationShipping = new \Register\Location ();
				$registerLocationShipping->add(array(
					'name'			=> $_REQUEST ['shipping_location_name'],
					'address_1'		=> $_REQUEST ['shipping_address'],
					'address_2'		=> $_REQUEST ['shipping_address2'],
					'city'			=> $_REQUEST ['shipping_city'],
					'zip_code'		=> $_REQUEST ['shipping_zip'],
					'province_id'	=> $_REQUEST ['shipping_province'],
					'notes'			=> 'address added during RMA process'
				));
				if ($registerLocationShipping->error()) {
					$page->addError("Failed to add location: ".$registerLocationShipping->error());
				}
				else {
					// add user address(es) if they don't exist yet with the register location mapping relationships included
					if ($_REQUEST ['shipping_address_type'] == 'business') {
						$registerLocationShipping->associateOrganization($organization->id, $_REQUEST['shipping_location_name']);
					} else {
						$registerLocationShipping->associateUser($customerId);
					}
				}
			}
			if (! $registerLocationShipping->id) {
				$page->addError("No location identified for return shipping");
			}
			else {
				$parameters['send_location_id'] = $registerLocationShipping->id;
	
				// RMA request has a new billing contact to be added
				if (empty($_REQUEST['billing_contact_picker'])) {
					$newUser = new Register\Person();
					$newUser->add(array(
						'login' => preg_replace('/\s+/', '', strtolower($_REQUEST['billing_firstname']))  . '-' . preg_replace('/\s+/', '', strtolower($_REQUEST['billing_lastname'])) . rand (1, 10000),
						'password' => uniqid(),
						'first_name' => $_REQUEST['billing_firstname'],
						'last_name' => $_REQUEST['billing_lastname'],
						'timezone' => 'America/New_York',        
						'organization_id' => $organization->id
					));
					$registerContact = new Register\Contact();
					$registerContact->add(array(
						'person_id' => $newUser->id,
						'description' =>  'Billing Email',
						'type' => 'email',
						'value' => $_REQUEST['billing_email'],
						'notes' => 'email added during RMA return request'
						)
					);
					$registerContact->add(array(
						'person_id' => $newUser->id,
						'description' =>  'Billing Phone',
						'type' => 'phone',
						'value' => $_REQUEST['billing_phone'],
						'notes' => 'phone number added during RMA return request'
						)
					);
				}
			
				$parameters ['instructions'] = (isset ( $_REQUEST ['delivery_instructions'] )) ? $_REQUEST ['delivery_instructions'] : '';
	
				// add shipment with package and items entries
				if (! $shippingShipment->add ( $parameters )) {
					$page->addError("Error creating shipment: ".$shippingShipment->error());
				}
				else {
					// add a default "1st" package to the shipment, there should be at least that
					$packageDetails = array ();
					$packageDetails ['shipment_id'] = $shippingShipment->id;
					$packageDetails ['number'] = 1;
					$packageDetails ['tracking_code'] = (isset ( $_REQUEST ['tracking_number'] )) ? $_REQUEST ['tracking_number'] : '';
					$packageDetails ['status'] = 'READY';
					$packageDetails ['condition'] = 'OK';
					$shippingPackage = $shippingShipment->add_package($packageDetails);
					if ($shippingShipment->error()) {
						$page->addError("Error adding package to shipment: ".$shippingShipment->error());
					}
					else {
						// each item from the form including accessories is added to the shipment as a shipping_item record
						$shippingPackage->add_item(array(
							'product_id'	=> $rmaItem->product->id,
							'serial_number'	=> $rmaSerialNumber,
							'condition'		=> 'OK',
							'quantity'		=> 1,
							'description'	=> $rmaProduct->description
						));
						if ($shippingPackage->error()) {
							$page->addError("Error adding item to shipment: ".$shippingPackage->error());
							$shippingShipment->delete();
							$shippingShipment = null;
						}
						else {
							foreach ($optional_contents as $code => $name) {
								if (! empty ( $_REQUEST[$code] )) {
									$shippingPackage->add_item(array(
										'product_id'	=> $misc_inventory_item->id,
										'serial_number'	=> '',
										'condition'		=> 'OK',
										'quantity'		=> 1,
										'description'	=> $name
									));
									if ($shippingPackage->error()) $page->addError("Error adding item to shipment: ".$shippingPackage->error());
								}
							}
						}
					}

					// RMA status is changed to CUSTOMER_SHIP @TODO, why didn't the table have that in the ENUM values?
					$rma->update(array('status'=>'PRINTED'));
				}
			}
		}
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
	if (!empty($shippingShipment->id)) {
		$rmaSubmitted = true;
		$sentFromLocation = $shippingShipment->send_location ();
		$sentToLocation = $shippingShipment->rec_location ();
		$shippingPackage = new \Shipping\Package ();
		$shippingPackage->getByShippingID($shippingShipment->id);
	}
}

// process any errors if occurred
if (isset($rma)) logErrors($rma, $page);
if (isset($countryList)) logErrors($countryList, $page);
if (isset($shippingShipment)) logErrors($shippingShipment, $page);
if (isset($registerLocationShipping)) logErrors($registerLocationShipping, $page);
if (isset($newUser)) logErrors($newUser, $page);
if (isset($registerContact)) logErrors($registerContact, $page);
if (isset($shippingPackage)) logErrors($shippingPackage, $page);

$organizationUsers = $organization->members('human');
$customerLocations = $GLOBALS['_SESSION_']->customer->locations();
