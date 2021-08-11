<?php
	namespace Support;

	/* Base Class for APIs */
	class API extends \API {

		public function __construct() {
			$this->_name = 'support';
			$this->_version = '0.2.0';
			$this->_release = '2021-08-11';
			$this->_schema = new \Support\Schema();
			$this->_admin_role = 'support manager';
			parent::__construct();
		}

		###################################################
		### Add an Item									###
		###################################################
		public function addItem() {
			$product = new \Product\Item();
	
			$product->add(
				array(
					'code'			=> $_REQUEST['code'],
					'name'			=> $_REQUEST['name'],
					'description'	=> $_REQUEST['description'],
					'status'		=> $_REQUEST['status'],
					'type'			=> $_REQUEST['type']
				)
			);
			if ($product->error) $this->error("Error adding product: ".$product->error);
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->item = $product;
	
			print $this->formatOutput($response);
		}

		/**
		 * Add a Request
		 */
		public function addRequest() {
			$request = new \Support\Request ();
			$parameters = array ();
			if ($GLOBALS['_SESSION_']->customer->has_role( 'support manager' )) {
				if ($_REQUEST ['customer']) {
					$customer = new \Register\Customer ();
					$customer->get ( $_REQUEST ['customer'] );
					if ($customer->error) $this->app_error ( "Error getting customer: " . $customer->error, 'error', __FILE__, __LINE__ );
					if (! $customer->id) $this->error ( "Customer not found" );
					$parameters ['customer_id'] = $customer->id;
				}
				if ($_REQUEST ['tech']) {
					$admin = new RegisterAdmin ();
					$admin->get ( $_REQUEST ['admin'] );
					if ($admin->error) $this->app_error ( "Error getting admin: " . $admin->error, 'error', __FILE__, __LINE__ );
					if (! $admin->id) $this->error ( "Tech not found" );
					$parameters ['tech_id'] = $admin->id;
				}
				if ($_REQUEST ['status']) {
					$parameters ['status'] = $_REQUEST ['status'];
				}
			}

			$request->add ( $parameters );
			if ($request->error) $this->app_error ( "Error adding request: " . $request->error );

			$this->response->success = 1;
			$this->response->request = $request;

			print $this->formatOutput ( $this->response );
		}

		/**
		 * Update a Request
		 */
		public function updateRequest() {
			$request = new \Support\Request ();
			$request->get ( $_REQUEST ['code'] );
			if ($request->error) $this->app_error ( "Error finding request: " . $request->error, 'error', __FILE__, __LINE__ );
			if (! $request->id) $this->error ( "Request not found" );

			$request->update ( $request->id, array ('name' => $_REQUEST ['name'],'type' => $_REQUEST ['type'],'status' => $_REQUEST ['status'],'description' => $_REQUEST ['description'] ) );
			if ($request->error) $this->app_error ( "Error adding product: " . $request->error, 'error', __FILE__, __LINE__ );

			$this->response->success = 1;
			$this->response->request = $request;

			print $this->formatOutput ( $this->response );
		}

		/**
		 * Get Specified Request
		 */
		public function getRequest() {
			$request = new \Support\Request ();
			$request->get ( $_REQUEST ['code'] );

			if ($request->error) $this->error ( "Error getting request: " . $request->error );

			$this->response->success = 1;
			$this->response->request = $request;

			print $this->formatOutput ( $this->response );
		}

		/**
		 * Find matching Requests
		 */
		public function findRequests() {
			$requestlist = new \Support\RequestList ();

			$parameters = array ();
			if ($_REQUEST ['status']) $parameters ['status'] = $_REQUEST ['status'];

			$requests = $requestlist->find ( $parameters );
			if ($requestlist->error) $this->app_error ( "Error finding requests: " . $requestlist->error );

			$this->response->success = 1;
			$this->response->request = $requests;

			print $this->formatOutput ( $this->response );
		}

		public function _methods() {
			return array(
				'ping'			=> array(),
				'findItems'	=> array(
					'code'		=> array(),
					'name'		=> array(),
					'status'	=> array(),
					'type'		=> array(),
				),
				'getItem'	=> array(
					'code'	=> array(),
				),
				'addItem'	=> array(
					'code'		=> array('required' => true),
					'name'		=> array('required' => true),
					'status'	=> array('default' => 'ACTIVE'),
					'type'		=> array('required' => true),
				),
				'updateItem'	=> array(
					'code'		=> array('required' => true),
					'name'		=> array(),
					'status'	=> array(),
					'type'		=> array(),
				),
				'findRelationships'	=> array(
					'parent_code'	=> array('required' => true),
					'child_code'	=> array('required' => true),
				),
				'addRelationship'	=> array(
					'parent_code'	=> array('required' => true),
					'child_code'	=> array('required' => true),
				),
				'getRelationship'	=> array(
					'parent_code'	=> array('required' => true),
					'child_code'	=> array('required' => true),
				),
				'findGroupItems'	=> array(
					'code'			=> array('required' => true)
				)
			);
		}
	}
