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
	### Add a Shipment								###
	###################################################
	function addShipment() {
		$shipment = new \Shipping\Shipment();

		$parameters = array();
		$shipment->add($parameters);
		if ($_request->error) app_error("Error adding shipment: ".$_request->error);

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
