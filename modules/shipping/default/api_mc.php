<?php
    ###############################################
    ### Handle API Request for Shipment			###
    ### tracking.								###
    ### A. Caravello 10/30/2019               	###
    ###############################################

	###############################################
	### Load API Objects						###
    ###############################################
	$_package = array(
		"name"		=> "shipping",
		"version"	=> "0.1.1",
		"release"	=> "2019-10-30",
	);

	app_log("Request: ".print_r($_REQUEST,true),'debug',__FILE__,__LINE__);

	# Call Requested Event
	if ($_REQUEST["method"]) {
		# Call the Specified Method
		$function_name = $_REQUEST["method"];
		$function_name();
		exit;
	}
	# Only Developers Can See The API
	elseif (! $GLOBALS['_SESSION_']->customer->has_role('shipping manager')) {
		header("location: /_shipping/home");
		exit;
	}

	###################################################
	### Just See if Server Is Communicating			###
	###################################################
	function ping() {
		$response = new \HTTP\Response();
		$response->header->session = $GLOBALS['_SESSION_']->code;
		$response->header->method = $_REQUEST["method"];
		$response->header->date = system_time();
		$response->message = "PING RESPONSE";
		$response->success = 1;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Find matching Vendors						###
	###################################################
	function findVendors() {
		$vendorList = new \Shipping\VendorList();
		
		$parameters = array();
		
		$vendors = $vendorList->find($parameters);
		if ($vendorList->error) app_error("Error finding vendors: ".$vendorList->error);

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->vendors = $vendors;

		print formatOutput($response);
	}

	###################################################
	### Add a Vendor								###
	###################################################
	function addVendor() {
		$vendor = new \Shipping\Vendor();

		$parameters = array();
		$parameters['name'] = $_REQUEST['name'];
		$parameters['account_number'] = $_REQUEST['account_number'];

		$vendor->add($parameters);
		if ($_request->error) app_error("Error adding vendor: ".$_request->error);

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->vendor = $vendor;

		print formatOutput($response);
	}

	###################################################
	### Update a Vendor								###
	###################################################
	function updateVendor() {
		$vendor = new \Shipping\Vendor();
		$vendor->get($_REQUEST['name']);
		if ($vendor->error) app_error("Error finding vendor: ".$vendor->error,'error',__FILE__,__LINE__);
		if (! $vendor->id) error("Vendor ".$_REQUEST['name']." not found");

		$parameters = array();
		$parameters['account_number'] = $_REQUEST['account_number'];
		$vendor->update($parameters);

		if ($vendor->error) app_error("Error updating vendor: ".$vendor->error,'error',__FILE__,__LINE__);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->vendor = $vendor;

		print formatOutput($response);
	}

	###################################################
	### Get a Specific Vendor						###
	###################################################
	function getVendor() {
		$response = new \HTTP\Response();
		$vendor = new \Shipping\Vendor();
		if ($vendor->get($_REQUEST['name'])) {
			$response->success = 1;
			$response->vendor = $vendor;
		}
		else {
			error("Error finding vendor: ".$vendor->error(),'error');
		}

		print formatOutput($response);
	}

	###################################################
	### Add a Shipment								###
	###################################################
	function addShipment() {
		$parameters = array();
		$send_location = new \Register\Location($_REQUEST['send_location_id']);
		if ($send_location->id) $parameters['send_location_id'] = $send_location->id;
		else error("Sending location not found");

		$receive_location = new \Register\Location($_REQUEST['receive_location_id']);
		if ($receive_location->id) $parameters['receive_location_id'] = $receive_location->id;
		else error("Receiving location not found");

		$send_customer = new \Register\Customer($_REQUEST['send_customer_id']);
		if ($send_customer->id) $parameters['send_customer_id'] = $send_customer->id;
		else error("Sending Customer not found");

		$receive_customer = new \Register\Customer($_REQUEST['receive_customer_id']);
		if ($receive_customer->id) $parameters['receive_customer_id'] = $_REQUEST['receive_customer_id'];
		else error("Receiving Customer not found");

		$vendor = new \Shipping\Vendor($_REQUEST['vendor_id']);
		if ($vendor->id) $parameters['vendor_id'] = $_REQUEST['vendor_id'];

		if (isset($_REQUEST['document_number'])) $parameters['document_number'] = $_REQUEST['document_number'];
		else error("Document number required");

		if (!empty($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];

		$shipment = new \Shipping\Shipment();
		$shipment->add($parameters);
		if ($shipment->error()) error("Error adding shipment: ".$shipment->error());

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->shipment = $shipment;

		print formatOutput($response);
	}

	###################################################
	### Update a Shipment							###
	###################################################
	function updateShipment() {
		$shipment = new \Shipping\Shipment();
		$shipment->get($_REQUEST['code']);
		if ($shipment->error) app_error("Error finding shipment: ".$shipment->error,'error',__FILE__,__LINE__);
		if (! $shipment->id) error("Request not found");

		$parameters = array();
		$shipment->update(
			$parameters
		);
		if ($shipment->error) app_error("Error updating shipment: ".$shipment->error,'error',__FILE__,__LINE__);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->shipment = $shipment;

		print formatOutput($response);
	}

	###################################################
	### Get Specified Shipment						###
	###################################################
	function getShipment() {
		$shipment = new \Shipping\Shipment();
		$shipment->get($_REQUEST['code']);

		if ($shipment->error) error("Error getting shipment: ".$shipment->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->shipment = $shipment;

		print formatOutput($response);
	}

	###################################################
	### Find matching Shipments						###
	###################################################
	function findShipments() {
		$shipmentList = new \Shipping\ShipmentList();
		
		$parameters = array();
		if ($_REQUEST['status']) $parameters['status'] = $_REQUEST['status'];
		
		$shipments = $shipmentList->find($parameters);
		if ($shipmentList->error) app_error("Error finding shipments: ".$shipmentList->error);

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->shipment = $shipments;

		print formatOutput($response);
	}

	###################################################
	### Add a Package to a Shipment					###
	###################################################
	function addPackage() {
		$parameters = array();
		$shipment = new \Shipping\Shipment($_REQUEST['shipment_id']);
		if ($shipment->id) $parameters['shipment_id'] = $shipment->id;
		else error("Shipment not found");

		$package = $shipment->addPackage();

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->package = $package;

		print formatOutput($response);
	}

	###################################################
	### Update a Package							###
	###################################################
	function updatePackage() {
		$shipment = new \Shipping\Shipment($_REQUEST['shipment_id']);
		$package = $shipment->package($_REQUEST['id']);
		if ($shipment->error) app_error("Error finding package: ".$shipment->error,'error',__FILE__,__LINE__);
		if (! $shipment->id) error("Request not found");

		$parameters = array();
		$shipment->update(
			$parameters
		);
		if ($shipment->error) app_error("Error updating shipment: ".$shipment->error,'error',__FILE__,__LINE__);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->shipment = $shipment;

		print formatOutput($response);
	}

	###################################################
	### Find matching Packages						###
	###################################################
	function findPackages() {
		$packageList = new \Shipping\PackageList();
		
		$parameters = array();
		if ($_REQUEST['status']) $parameters['status'] = $_REQUEST['status'];
		
		$packages = $packageList->find($parameters);
		if ($packageList->error) app_error("Error finding shipments: ".$packageList->error);

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->package = $packages;

		print formatOutput($response);
	}

	###################################################
	### Manage Support Schema						###
	###################################################
	function schemaVersion() {
		$schema = new \Shipping\Schema();
		if ($schema->error) {
			app_error("Error getting version: ".$schema->error,__FILE__,__LINE__);
		}
		$version = $schema->version();
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->version = $version;
		print formatOutput($response);
	}
	function schemaUpgrade() {
		$schema = new \Shipping\Schema();
		if ($schema->error) {
			app_error("Error getting version: ".$schema->error,__FILE__,__LINE__);
		}
		$version = $schema->upgrade();
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->version = $version;
		print formatOutput($response);
	}

	###################################################
	### System Time									###
	###################################################
	function system_time() {
		return date("Y-m-d H:i:s");
	}
	###################################################
	### Return Properly Formatted Error Message		###
	###################################################
	function error($message) {
		$_REQUEST["stylesheet"] = '';
		error_log($message);
		$response = new \HTTP\Response();
		$response->message = $message;
		$response->success = 0;
		print formatOutput($response);
		exit;
	}
	###################################################
	### Application Error							###
	###################################################
	function app_error($message,$file = __FILE__,$line = __LINE__) {
		app_log($message,'error',$file,$line);
		error('Application Error');
	}
	###################################################
	### Convert Object to XML						###
	###################################################
	function formatOutput($object) {
		if (isset($_REQUEST['_format']) && $_REQUEST['_format'] == 'json') {
			$format = 'json';
			header('Content-Type: application/json');
		}
		else {
			$format = 'xml';
			header('Content-Type: application/xml');
		}
		$document = new \Document($format);
		$document->prepare($object);
		return $document->content();
	}
?>
