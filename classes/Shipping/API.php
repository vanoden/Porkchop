<?php
	namespace Shipping;

	/* Base Class for APIs */
	class API extends \API {

		public function __construct() {
			$this->_admin_role = 'shipping manager';
			$this->_name = 'shipping';
			$this->_version = '0.1.2';
			$this->_release = '2022-03-10';
			$this->_schema = new \Shipping\Schema();
			parent::__construct();
		}

		###################################################
		### Find matching Vendors						###
		###################################################
		public function findVendors() {
			$vendorList = new \Shipping\VendorList();
			
			$parameters = array();
			
			$vendors = $vendorList->find($parameters);
			if ($vendorList->error) $this->app_error("Error finding vendors: ".$vendorList->error);

			$response = new \HTTP\Response();
			$response->success = 1;
			$response->vendors = $vendors;

			print $this->formatOutput($response);
		}

		###################################################
		### Add a Vendor								###
		###################################################
		public function addVendor() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$vendor = new \Shipping\Vendor();

			$parameters = array();
			$parameters['name'] = $_REQUEST['name'];
			$parameters['account_number'] = $_REQUEST['account_number'];

			$vendor->add($parameters);
			if ($vendor->error) $this->app_error("Error adding vendor: ".$vendor->error);

			$response = new \HTTP\Response();
			$response->success = 1;
			$response->vendor = $vendor;

			print $this->formatOutput($response);
		}

		###################################################
		### Update a Vendor								###
		###################################################
		public function updateVendor() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$vendor = new \Shipping\Vendor();
			$vendor->get($_REQUEST['name']);
			if ($vendor->error) $this->app_error("Error finding vendor: ".$vendor->error,'error',__FILE__,__LINE__);
			if (! $vendor->id) $this->error("Vendor ".$_REQUEST['name']." not found");

			$parameters = array();
			$parameters['account_number'] = $_REQUEST['account_number'];
			$vendor->update($parameters);

			if ($vendor->error) $this->app_error("Error updating vendor: ".$vendor->error,'error',__FILE__,__LINE__);
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->vendor = $vendor;

			print $this->formatOutput($response);
		}

		###################################################
		### Get a Specific Vendor						###
		###################################################
		public function getVendor() {
			$response = new \HTTP\Response();
			$vendor = new \Shipping\Vendor();
			if ($vendor->get($_REQUEST['name'])) {
				$response->success = 1;
				$response->vendor = $vendor;
			}
			else {
				$this->error("Error finding vendor: ".$vendor->error(),'error');
			}

			print $this->formatOutput($response);
		}

		###################################################
		### Add a Shipment								###
		###################################################
		public function addShipment() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$parameters = array();
			$send_location = new \Register\Location($_REQUEST['send_location_id']);
			if ($send_location->id) $parameters['send_location_id'] = $send_location->id;
			else $this->error("Sending location not found");

			$receive_location = new \Register\Location($_REQUEST['receive_location_id']);
			if ($receive_location->id) $parameters['receive_location_id'] = $receive_location->id;
			else $this->error("Receiving location not found");

			$send_customer = new \Register\Customer($_REQUEST['send_customer_id']);
			if ($send_customer->id) $parameters['send_customer_id'] = $send_customer->id;
			else $this->error("Sending Customer not found");

			$receive_customer = new \Register\Customer($_REQUEST['receive_customer_id']);
			if ($receive_customer->id) $parameters['receive_customer_id'] = $_REQUEST['receive_customer_id'];
			else $this->error("Receiving Customer not found");

			$vendor = new \Shipping\Vendor($_REQUEST['vendor_id']);
			if ($vendor->id) $parameters['vendor_id'] = $_REQUEST['vendor_id'];

			if (isset($_REQUEST['document_number'])) $parameters['document_number'] = $_REQUEST['document_number'];
			else $this->error("Document number required");

			if (!empty($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];

			$shipment = new \Shipping\Shipment();
			$shipment->add($parameters);
			if ($shipment->error()) error("Error adding shipment: ".$shipment->error());

			$response = new \HTTP\Response();
			$response->success = 1;
			$response->shipment = $shipment;

			print $this->formatOutput($response);
		}

		###################################################
		### Update a Shipment							###
		###################################################
		public function updateShipment() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$shipment = new \Shipping\Shipment();
			$shipment->get($_REQUEST['code']);
			if ($shipment->error) $this->app_error("Error finding shipment: ".$shipment->error,'error',__FILE__,__LINE__);
			if (! $shipment->id) $this->error("Request not found");

			$parameters = array();
			$shipment->update(
				$parameters
			);
			if ($shipment->error) $this->app_error("Error updating shipment: ".$shipment->error,'error',__FILE__,__LINE__);
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->shipment = $shipment;

			print $this->formatOutput($response);
		}

		###################################################
		### Get Specified Shipment						###
		###################################################
		public function getShipment() {
			$shipment = new \Shipping\Shipment();
			$shipment->get($_REQUEST['code']);

			if ($shipment->error) $this->error("Error getting shipment: ".$shipment->error);
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->shipment = $shipment;

			print $this->formatOutput($response);
		}

		###################################################
		### Find matching Shipments						###
		###################################################
		public function findShipments() {
			$shipmentList = new \Shipping\ShipmentList();
			
			$parameters = array();
			if ($_REQUEST['status']) $parameters['status'] = $_REQUEST['status'];
			
			$shipments = $shipmentList->find($parameters);
			if ($shipmentList->error) $this->app_error("Error finding shipments: ".$shipmentList->error);

			$response = new \HTTP\Response();
			$response->success = 1;
			$response->shipment = $shipments;

			print $this->formatOutput($response);
		}

		###################################################
		### Add a Package to a Shipment					###
		###################################################
		public function addPackage() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$parameters = array();
			$shipment = new \Shipping\Shipment($_REQUEST['shipment_id']);
			if ($shipment->id) $parameters['shipment_id'] = $shipment->id;
			else $this->error("Shipment not found");

			$package = $shipment->addPackage();

			$response = new \HTTP\Response();
			$response->success = 1;
			$response->package = $package;

			print $this->formatOutput($response);
		}

		###################################################
		### Update a Package							###
		###################################################
		public function updatePackage() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$shipment = new \Shipping\Shipment($_REQUEST['shipment_id']);
			$package = $shipment->package($_REQUEST['id']);
			if ($shipment->error) $this->app_error("Error finding package: ".$shipment->error,'error',__FILE__,__LINE__);
			if (! $shipment->id) $this->error("Request not found");

			$parameters = array();
			$shipment->update(
				$parameters
			);
			if ($shipment->error) $this->app_error("Error updating shipment: ".$shipment->error,'error',__FILE__,__LINE__);
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->shipment = $shipment;

			print $this->formatOutput($response);
		}

		###################################################
		### Find matching Packages						###
		###################################################
		public function findPackages() {
			$packageList = new \Shipping\PackageList();
			
			$parameters = array();
			if ($_REQUEST['status']) $parameters['status'] = $_REQUEST['status'];
			
			$packages = $packageList->find($parameters);
			if ($packageList->error) $this->app_error("Error finding shipments: ".$packageList->error);

			$response = new \HTTP\Response();
			$response->success = 1;
			$response->package = $packages;

			print $this->formatOutput($response);
		}
	}