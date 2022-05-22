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
					$admin = new \Register\Admin ();
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

			$request->update ( array ('name' => $_REQUEST ['name'],'type' => $_REQUEST ['type'],'status' => $_REQUEST ['status'],'description' => $_REQUEST ['description'] ) );
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
				'getRequest'	=> array(
					'code'		=> array()
				),
				'addRequest'	=> array(
					'customer'	=> array('required' => true),
					'tech'		=> array(),
					'status'	=> array()
				),
				'updateRequest'	=> array(
					'code'		=> array('required' => true),
					'customer'	=> array(),
					'tech'		=> array(),
					'status'	=> array()
				),
				'findRequests'	=> array(
					'status'	=> array('required' => true)
				)
			);
		}
	}
