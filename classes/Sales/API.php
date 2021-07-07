<?php
	namespace Sales;

	class API Extends API {

		public function __construct() {
			$this->_admin_role = 'sales manager';
			$this->_name = 'sales';
			$this->_version = '0.2.3';
			$this->_release = '2021-06-16';
			$this->_schema = new \Sales\Schema();
			parent::__construct();
		}

		###################################################
		### Add a Sales Order							###
		###################################################
		public function addOrder() {
			$order = new \Sales\Order();

			$parameters = array();
			if ($GLOBALS['_SESSION_']->customer->has_role('salesperson')) {
				if (isset($_REQUEST['customer_id'])) $parameters['customer_id'] = $_REQUEST['customer_id'];
				else $parameters['customer_id'] = $GLOBALS['_SESSION_']->customer->id;
				if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
				else $parameters['status'] = 'QUOTE';
				if (isset($_REQUEST['salesperson_code'])) $parameters['salesperson_code'] = $_REQUEST['salesperson_code'];
				else $parameters['salesperson_id'] = $GLOBALS['_SESSION_']->customer->id;
			}
			else if ($GLOBALS['_SESSION_']->authenticated()) {
				$parameters['salesperson_id'] = $GLOBALS['_SESSION_']->customer->id;
				$parameters['status'] = 'REQUEST';
			}
			else {
				$parameters['status'] = 'REQUEST';
			}
			if (! $order->add($parameters)) app_error("Error adding order: ".$order->error());

			$response = new \HTTP\Response();
			$response->success = 1;
			$response->order = $order;

			print $this->formatOutput($response);
		}
	
		###################################################
		### Update a Sales Order						###
		###################################################
		public function updateOrder() {
			$order = new \Sales\Order();
			if (! $order->get($_REQUEST['code'])) {
				error("Error finding order: ".$order->error());
			}

			$parameters = array();
			if (! $order->update($parameters)) {
				error("Error updating order: ".$order->error());
			}

			$response = new \HTTP\Response();
			$response->success = 1;
			$response->order = $order;

			print $this->formatOutput($response);
		}
	
		###################################################
		### Get Specified Sales Order					###
		###################################################
		public function getOrder() {
			$order = new \Sales\Order();
			if (! $order->get($_REQUEST['code'])) {
				error("Error getting order: ".$order->error());
			}

			$response = new \HTTP\Response();
			$response->success = 1;
			$response->order = $order;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Find matching Sales Orders					###
		###################################################
		function findOrders() {
			$orderList = new \Sales\OrderList();

			$parameters = array();
			if ($_REQUEST['status']) $parameters['status'] = $_REQUEST['status'];
			if ($_REQUEST['customer_code']) $parameters['customer_code'] = $_REQUEST['customer_code'];
			if ($_REQUEST['organization_code']) $parameters['organization_code'] = $_REQUEST['organization_code'];

			$orders = $orderList->find($parameters);
			if ($orderList->error) app_error("Error finding orders: ".$orderList->error());

			$response = new \HTTP\Response();
			$response->success = 1;
			$response->order = $orders;

			print $this->formatOutput($response);
		}

		###################################################
		### Add an Item to an Order						###
		###################################################
		function addOrderItem() {
			$order = new \Sales\Order();
			if (isset($_REQUEST['order_code'])) {
				if (! $order->get($_REQUEST['order_code'])) {
					error("Order not found: ".$order->error());
				}
			}
			else {
				error("Order Code required");
			}

			if (isset($_REQUEST['product_code'])) {
				$product = new \Product\Item();
				if (! $product->get($_REQUEST['product_code'])) {
					error("Cannot find product: ".$product->error());
				}
			}
			else {
				error("Product Code required");
			}
			if (! $product->id) $this->error("Product not found");

			$parameters = array(
				'order_id'		=> $order->id,
				'product_id'	=> $product->id,
				'description'	=> $_REQUEST['description'],
				'price'			=> $_REQUEST['price'],
				'quantity'		=> $_REQUEST['quantity']
			);
	
			if (! $order->addItem($parameters)) $this->app_error("Error adding order item: ".$order->error());
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->item = $item;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Update an Order Item						###
		###################################################
		function updateOrderItem() {
			$order = new \Sales\Order();
			if ($order->get($_REQUEST['order_code'])) {
				error("Error finding order: ".$order->error());
			}
	
			$item = $order->getItem($_REQUEST['line']);
			if (! $item) $this->error("Item not found: ".$order->error());
	
			$parameters = array();
			if ($_REQUEST['price']) $parameters['price'] = $_REQUEST['price'];
			if ($_REQUEST['quantity']) $parameters['quantity'] = $_REQUEST['quantity'];
			if ($_REQUEST['description']) $parameters['description'] = $_REQUEST['description'];
	
			$item->update($parameters);
			if ($item->error) $this->app_error("Error updating item: ".$item->error(),'error');
			$response->success = 1;
			$response->item = $item;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Drop an Item								###
		###################################################
		public function dropOrderItem() {
			$order = new \Sales\Order();
			if (! $order->get($_REQUEST['order_code'])) {
				error("Error finding order: ".$order->error());
			}
			if (! $order->dropItem($_REQUEST['line'])) {
				if ($order->error) $this->error("Item not found");
			}

			$response = new \HTTP\Response();
			$response->success = 1;

			print $this->formatOutput($response);
		}

		###################################################
		### Find matching Provinces						###
		###################################################
		public function findOrderItems() {
			$itemList = new \Sale\Order\ItemList();

			$parameters = array();
			if ($_REQUEST['order_code']) $parameters['order_code'] = $_REQUEST['order_code'];
			if ($_REQUEST['product_code']) $parameters['product_code'] = $_REQUEST['product_code'];

			$items = $itemList->find($parameters);
			if ($itemList->error()) error("Error finding items: ".$itemList->error());

			$response = new \HTTP\Response();
			$response->success = 1;
			$response->item = $items;

			print $this->formatOutput($response);
		}

		public function _methods() {
			return array(
				'ping'			=> array(),
				'addOrder'		=> array(
					'code'			=> array('required' => true),
					'customer_code'	=> array('required' => true),
					'status'		=> array(),
					'salesperson_code'	=> array(),
					'date_quote'	=> array(),
					'date_order'	=> array(),
				),
				'getOrder'		=> array(
					'code'			=> array('required' => true),
				),
				'approveOrder'	=> array(
					'order_code'	=> array('required' => true),
				},
				'cancelOrder'	=> array(
					'order_code'	=> array('required' => true),
				),
				'findOrders' => array(
					'organization_code' => array(),
					'code'			=> array(),
					'date_quote'	=> array(),
					'date_order'	=> array(),
					'salesperson_code'	=> array(),
				),
				'addOrderItem'	=> array(
					'order_code'	=> array('required'	=> true),
					'product_code'	=> array('required'	=> true),
					'quantity'		=> array('required' => true, 'default' => '1'),
					'price'			=> array('required' => true),
				),
				'updateOrderItem'	=> array(
					'order_code'	=> array('required' => true),
					'line'			=> array('required' => true),
					'product_code'	=> array(),
					'quantity'		=> array(),
					'price'			=> array(),
					'deleted'		=> array(),
				),
			);
		}
	}
