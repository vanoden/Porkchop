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
			if (!empty($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			
			$vendors = $vendorList->find($parameters);
			if ($vendorList->error()) $this->error("Error finding vendors: ".$vendorList->error());

			$response = new \APIResponse();
			$response->AddElement('vendor',$vendors);
			$response->print();
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
			if ($vendor->error()) $this->app_error("Error adding vendor: ".$vendor->error());

			$response = new \APIResponse();
			$response->AddElement('vendor',$vendor);
			$response->print();
		}

		###################################################
		### Update a Vendor								###
		###################################################
		public function updateVendor() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$vendor = new \Shipping\Vendor();
			$vendor->get($_REQUEST['name']);
			if ($vendor->error()) $this->error("Error finding vendor: ".$vendor->error());
			if (! $vendor->id) $this->notFound("Vendor ".$_REQUEST['name']." not found");

			$parameters = array();
			$parameters['account_number'] = $_REQUEST['account_number'];
			$vendor->update($parameters);

			if ($vendor->error()) $this->app_error("Error updating vendor: ".$vendor->error());
			$response = new \APIResponse();
			$response->AddElement('vendor',$vendor);
			$response->print();
		}

		###################################################
		### Get a Specific Vendor						###
		###################################################
		public function getVendor() {
			$vendor = new \Shipping\Vendor();
			if ($vendor->get($_REQUEST['name'])) {
				$response = new \APIResponse();
				$response->AddElement('vendor',$vendor);
				$response->print();
			}
			elseif ($vendor->error()) {
				$this->error("Error finding vendor: ".$vendor->error(),'error');
			}
			else $this->notFound("Vendor ".$_REQUEST['name']." not found");
		}

		###################################################
		### Add a Shipment								###
		###################################################
		public function addShipment() {
			$parameters = array();
			$send_location = new \Register\Location($_REQUEST['send_location_id']);
			if ($send_location->id) $parameters['send_location_id'] = $send_location->id;
			else $this->invalidRequest("Sending location not found");

			$receive_location = new \Register\Location($_REQUEST['receive_location_id']);
			if ($receive_location->id) $parameters['receive_location_id'] = $receive_location->id;
			else $this->invalidRequest("Receiving location not found");

			$send_customer = new \Register\Customer($_REQUEST['send_customer_id']);
			if ($send_customer->id) $parameters['send_customer_id'] = $send_customer->id;
			else $this->invalidRequest("Sending Customer not found");

			$receive_customer = new \Register\Customer($_REQUEST['receive_customer_id']);
			if ($receive_customer->id) $parameters['receive_customer_id'] = $_REQUEST['receive_customer_id'];
			else $this->invalidRequest("Receiving Customer not found");

			$vendor = new \Shipping\Vendor($_REQUEST['vendor_id']);
			if ($vendor->id) $parameters['vendor_id'] = $_REQUEST['vendor_id'];

			if (isset($_REQUEST['document_number'])) $parameters['document_number'] = $_REQUEST['document_number'];
			else $this->incompleteRequest("Document number required");

			if (!empty($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];

			$shipment = new \Shipping\Shipment();
			$shipment->add($parameters);
			if ($shipment->error()) $this->error("Error adding shipment: ".$shipment->error());

			$response = new \APIResponse();
			$response->AddElement('shipment',$shipment);
			$response->print();
		}

		###################################################
		### Update a Shipment							###
		###################################################
		public function updateShipment() {
			$shipment = new \Shipping\Shipment();
			$shipment->get($_REQUEST['code']);
			if ($shipment->error()) $this->app_error("Error finding shipment: ".$shipment->error());
			if (! $shipment->id) $this->notFound("Request not found");

			$parameters = array();
			$shipment->update(
				$parameters
			);
			if ($shipment->error()) $this->app_error("Error updating shipment: ".$shipment->error(),'error',__FILE__,__LINE__);
			$response = new \APIResponse();
			$response->AddElement('shipment',$shipment);
			$response->print();
		}

		###################################################
		### Get Specified Shipment						###
		###################################################
		public function getShipment() {
			$shipment = new \Shipping\Shipment();
			$shipment->get($_REQUEST['code']);

			if ($shipment->error()) $this->error("Error getting shipment: ".$shipment->error());
			$response = new \APIResponse();
			$response->AddElement('shipment',$shipment);
			$response->print();
		}

		###################################################
		### Find matching Shipments						###
		###################################################
		public function findShipments() {
			$shipmentList = new \Shipping\ShipmentList();
			
			$parameters = array();
			if ($_REQUEST['status']) $parameters['status'] = $_REQUEST['status'];
			
			$shipments = $shipmentList->find($parameters);
			if ($shipmentList->error()) $this->app_error("Error finding shipments: ".$shipmentList->error());

			$response = new \APIResponse();
			$response->AddElement('shipment',$shipments);
			$response->print();
		}

		###################################################
		### Add a Package to a Shipment					###
		###################################################
		public function addPackage() {
			$parameters = array();
			$shipment = new \Shipping\Shipment($_REQUEST['shipment_id']);
			if ($shipment->id) $parameters['shipment_id'] = $shipment->id;
			else $this->error("Shipment not found");

			$package = $shipment->addPackage($parameters);

			$response = new \APIResponse();
			$response->AddElement('package',$package);
			$response->print();
		}

		###################################################
		### Update a Package							###
		###################################################
		public function updatePackage() {
			$shipment = new \Shipping\Shipment($_REQUEST['shipment_id']);
			$package = $shipment->package($_REQUEST['id']);
			if ($shipment->error()) $this->app_error("Error finding package: ".$shipment->error());
			if (! $shipment->id) $this->error("Request not found");

			$parameters = array();
			$shipment->update(
				$parameters
			);
			if ($shipment->error()) $this->app_error("Error updating shipment: ".$shipment->error(),'error',__FILE__,__LINE__);
			$response = new \APIResponse();
			$response->AddElement('shipment',$shipment);
			$response->print();
		}

		###################################################
		### Find matching Packages						###
		###################################################
		public function findPackages() {
			$packageList = new \Shipping\PackageList();
			
			$parameters = array();
			if ($_REQUEST['status']) $parameters['status'] = $_REQUEST['status'];
			
			$packages = $packageList->find($parameters);
			if ($packageList->error()) $this->app_error("Error finding shipments: ".$packageList->error());

			$response = new \APIResponse();
			$response->AddElement('package',$packages);
			$response->print();
		}

		public function _methods() {
			return array(
				'findVendors' => array(
					'description'	=> 'Find matching Vendors',
					'return_element'	=> 'vendor',
					'return_type'	=> 'array',
					'parameters'	=> array(
						'name' => array(
							'description' => 'Name of Vendor',
							'validation_method' => 'Shipping::Vendor::validName()',
							'allow_wildcards' => true,
						),
					),
				),
				'addVendor' => array(
					'description'	=> 'Add a Vendor',
					'token_required'	=> true,
					'privilege_required'	=> 'shipping manager',
					'return_element'	=> 'vendor',
					'return_type'	=> 'object',
					'parameters'	=> array(
						'name' => array(
							'description' => 'Name of Vendor',
							'validation_method' => 'Shipping::Vendor::validName()',
						),
						'account_number' => array(
							'description' => 'Account Number',
							'validation_method' => 'Shipping::Vendor::validAccountNumber()',
						),
					),
				),
				'updateVendor' => array(
					'description'	=> 'Update a Vendor',
					'token_required'	=> true,
					'privilege_required'	=> 'shipping manager',
					'return_element'	=> 'vendor',
					'return_type'	=> 'object',
					'parameters'	=> array(
						'name' => array(
							'description' => 'Name of Vendor',
							'validation_method' => 'Shipping::Vendor::validName()',
						),
						'account_number' => array(
							'description' => 'Account Number',
							'validation_method' => 'Shipping::Vendor::validAccountNumber()',
						),
					),
				),
				'getVendor' => array(
					'description'	=> 'Get details regarding specified Vendor',
					'return_element'	=> 'vendor',
					'return_type'	=> 'object',
					'parameters'	=> array(
						'name' => array(
							'description' => 'Name of Vendor',
							'validation_method' => 'Shipping::Vendor::validName()',
						),
					),
				),
				'addShipment' => array(
					'description'	=> 'Add a Shipment',
					'token_required'	=> true,
					'privilege_required'	=> 'shipping manager',
					'return_element'	=> 'shipment',
					'return_type'	=> 'object',
					'parameters'	=> array(
						'send_location_id' => array(
							'description' => 'Sending Location ID',
							'validation_method' => 'Register::Location::validID()',
						),
						'receive_location_id' => array(
							'description' => 'Receiving Location ID',
							'validation_method' => 'Register::Location::validID()',
						),
						'send_customer_id' => array(
							'description' => 'Sending Customer ID',
							'validation_method' => 'Register::Customer::validID()',
						),
						'receive_customer_id' => array(
							'description' => 'Receiving Customer ID',
							'validation_method' => 'Register::Customer::validID()',
						),
						'vendor_id' => array(
							'description' => 'Vendor ID',
							'validation_method' => 'Shipping::Vendor::validID()',
						),
						'document_number' => array(
							'description' => 'Document Number',
							'validation_method' => 'Shipping::Shipment::validDocumentNumber()',
						),
						'status' => array(
							'description' => 'Status',
							'validation_method' => 'Shipping::Shipment::validStatus()',
						),
					)
				),
				'updateShipment' => array(
					'description'	=> 'Update a Shipment',
					'token_required'	=> true,
					'privilege_required'	=> 'shipping manager',
					'return_element'	=> 'shipment',
					'return_type'	=> 'object',
					'parameters'	=> array(
						'code' => array(
							'description' => 'Shipment Code',
							'required' => true,
							'validation_method' => 'Shipping::Shipment::validCode()',
						),
						'status' => array(
							'description' => 'Status',
							'validation_method' => 'Shipping::Shipment::validStatus()',
						),
					),
				),
				'getShipment' => array(
					'description'	=> 'Get details regarding specified Shipment',
					'return_element'	=> 'shipment',
					'return_type'	=> 'object',
					'parameters'	=> array(
						'code' => array(
							'description' => 'Shipment Code',
							'required' => true,
							'validation_method' => 'Shipping::Shipment::validCode()',
						),
					),
				),
				'findShipments' => array(
					'description'	=> 'Search for shipments',
					'return_element'	=> 'shipment',
					'return_type'	=> 'array',
					'parameters'	=> array(
						'status' => array(
							'description' => 'Status',
							'validation_method' => 'Shipping::Shipment::validStatus()',
						),
					),
				),
				'addPackage' => array(
					'description'	=> 'Add a Package to a Shipment',
					'token_required'	=> true,
					'privilege_required'	=> '[CONDITIONAL]',
					'return_element'	=> 'package',
					'return_type'	=> 'object',
					'parameters'	=> array(
						'shipment_id' => array(
							'description' => 'Shipment ID',
							'required' => true,
							'content-type' => 'int',
						),
						'package_id'	=> array(
							'description' => 'Package ID',
							'required' => true,
							'content-type' => 'int',
						),
					),
				),
				'updatePackage' => array(
					'description'	=> 'Update a Package',
					'token_required'	=> true,
					'privilege_required'	=> '[CONDITIONAL]',
					'return_element'	=> 'package',
					'return_type'	=> 'object',
					'parameters'	=> array(
						'shipment_id' => array(
							'description' => 'Shipment ID',
							'required' => true,
							'content-type' => 'int',
						),
						'package_id'	=> array(
							'description' => 'Package ID',
							'required' => true,
							'content-type' => 'int',
						),
						'status'	=> array(
							'description' => 'Status',
							'validation_method' => 'Shipping::Package::validStatus()',
						)
					),
				),
				'findPackages' => array(
					'description'	=> 'Search for packages',
					'return_element'	=> 'package',
					'return_type'	=> 'array',
					'parameters'	=> array(
						'status' => array(
							'description' => 'Status',
							'validation_method' => 'Shipping::Package::validStatus()',
						),
					),
				),
			);
		}
	}