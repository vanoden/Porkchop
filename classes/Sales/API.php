<?php
	namespace Sales;

	class API Extends API {

		public function __construct() {
			$this->_admin_role = 'sales manager';
			$this->_name = 'sales';
			$this->_version = '0.1.2';
			$this->_release = '2019-12-23';
			$this->_schema = new \Sales\Schema();
			parent::__construct();
		}

		###################################################
		### Add a Sales Order							###
		###################################################
		public function addOrder() {
			$order = new \Sales\Order();
	
			$parameters = array();
			if (isset($_REQUEST['customer_id'])) $parameters['customer_id'] = $_REQUEST['customer_id'];
			if (isset($_REQUEST['salesperson_id'])) $parameters['salesperson_id'] = $_REQUEST['salesperson_id'];
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
			$order->get($_REQUEST['code']);
			if ($country->error) app_error("Error finding country: ".$country->error(),'error',__FILE__,__LINE__);
			if (! $country->id) error("Request not found");
	
			$parameters = array();
			$country->update(
				$parameters
			);
			if ($country->error) app_error("Error updating country: ".$country->error(),'error',__FILE__,__LINE__);
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->country = $country;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Get Specified Sales Order					###
		###################################################
		public function getOrder() {
			$order = new \Sales\Order();
			$order->get($_REQUEST['code']);
			if ($order->error()) app_error($order->error());
	
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
		function addItem() {
			if (isset($_REQUEST['order_id'])) $order = new \Sales\Order($_REQUEST['order_id']);
			elseif (isset($_REQUEST['order_code'])) {
				$order = new \Sales\Order();
				$order->get($_REQUEST['order_code']);
			}
			if (! $order->id) $this->error("Sales Order not found");
	
			if (isset($_REQUEST['product_id'])) $product = new\Product\Item($_REQUEST['product_id']);
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
		function updateItem() {
			$order = new \Sales\Order();
			$order->get($_REQUEST['order_code']);
			if ($order->error) $this->app_error("Error finding order: ".$order->error(),'error',__FILE__,__LINE__);
			if (! $order->id) $this->error("Order not found");
	
			$item = $order->getItem($_REQUEST['line']);
			if (! $item) $this->error("Item not found");
	
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
		public function dropItem() {
			$order = new \Sales\Order();
			$order->get($_REQUEST['order_code']);
			if ($order->error) $this->app_error("Error finding order: ".$order->error(),'error');
			if (! $order->id) $this->error("Order not found");
	
			$order->dropItem($_REQUEST['line']);
			if ($order->error) $this->error("Item not found");
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->item = $item;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Find matching Provinces						###
		###################################################
		public function findItems() {
			$itemList = new \Sale\Order\ItemList();
	
			$parameters = array();
			if ($_REQUEST['order_code']) $parameters['order_code'] = $_REQUEST['order_code'];
			if ($_REQUEST['product_code']) $parameters['product_code'] = $_REQUEST['product_code'];
	
			$items = $itemList->find($parameters);
			if ($itemList->error) $this->app_error("Error finding items: ".$itemList->error());
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->item = $items;
	
			print $this->formatOutput($response);
		}
	}
?>