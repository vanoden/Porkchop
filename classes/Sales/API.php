<?php
	namespace Sales;

	class API Extends \API {
		public function __construct() {
			$this->_admin_role = 'sales manager';
			$this->_name = 'sales';
			$this->_version = '0.2.3';
			$this->_release = '2021-06-16';
			$this->_schema = new \Sales\Schema();
			parent::__construct();

			$response = new \HTTP\Response();
		}

		###################################################
		### Add a Sales Order							###
		###################################################
		public function addOrder() {
			$order = new \Sales\Order();
			$parameters = array();

			if ($GLOBALS['_SESSION_']->customer->has_role('salesperson')) {
				if (isset($_REQUEST['customer_id'])) $parameters['customer_id'] = $_REQUEST['customer_id'];
				elseif(isset($_REQUEST['customer_code'])) {
					$customer = new \Register\Customer();
					if ($customer->get($_REQUEST['customer_code'])) {
    					$parameters['customer_id'] = $customer->id;
                        $parameters['organization_id'] = $customer->organization->id;
                    }
                    else {
                        $this->error("Customer '".$_REQUEST['customer_code']."' not found");
                    }
                }
				else {
                    $parameters['customer_id'] = $GLOBALS['_SESSION_']->customer->id;
                    $parameters['organization_id'] = $GLOBALS['_SESSION_']->customer->organization->id;
                }

				if (!empty($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
				else $parameters['status'] = 'QUOTE';
				if (!empty($_REQUEST['salesperson_code'])) {
                    $salesperson = new \Register\Customer();
                    if (! $salesperson->get($_REQUEST['salesperson_code'])) $this->error("Salesperson '".$_REQUEST['salesperson_code']."' not found");
                }
				else $parameters['salesperson_id'] = $GLOBALS['_SESSION_']->customer->id;
				if (!empty($_REQUEST['customer_order_number'])) $parameters['customer_order_number'] = $_REQUEST['customer_order_number'];
			}
			elseif ($GLOBALS['_SESSION_']->authenticated()) {
				$parameters['customer_id'] = $GLOBALS['_SESSION_']->customer->id;
				$parameters['status'] = 'NEW';
			}
			else {
                $this->error("Not authenticated");
				$parameters['status'] = 'NEW';
			}
            if (isset($_REQUEST['customer_order_number'])) $parameters['customer_order_number'] = $_REQUEST['customer_order_number'];
			if (! $order->add($parameters)) $this->error("Error adding order: ".$order->error());
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

			if ($GLOBALS['_SESSION_']->customer->can('edit sales order')) {
				# OK To Update
                if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
                if (!empty($_REQUEST['salesperson_code'])) {
                    $salesperson = new \Register\Customer();
                    if (!$salesperson->get($_REQUEST['salesperson_code'])) $this->error("Salesperson not found");
                    else $parameters['salesperson_id'] = $salesperson->id;
                }
			}
			else {
				$this->app_error("Permission Denied");
			}

			$parameters = array();
			if (! $order->update($parameters)) {
				$this->error("Error updating order: ".$order->error());
			}

			$response = new \HTTP\Response();
			$response->success = 1;
			$response->order = $order;

			print $this->formatOutput($response);
		}

		###################################################
		### Update a Sales Order						###
		###################################################
		public function approveOrder() {
			$order = new \Sales\Order();
			if (! $order->get($_REQUEST['code'])) {
				$this->error("Error finding order: ".$order->error());
			}

			if ($GLOBALS['_SESSION_']->customer->can('approve sales order')) {
				# OK To Update
                $order = new \Sales\Order();
                if (! $order->get($_REQUEST['code'])) $this->error("Order not found");
                if (in_array($order->status,array('APPROVED','CANCELLED','COMPLETE'))) $this->error("Order not ready for approval");
                if (! $order->approve()) $this->error($order->error());
			}
            else $this->error("Permission denied");

			$parameters = array();
			if (! $order->update($parameters)) {
				$this->error("Error updating order: ".$order->error());
			}

			$response = new \HTTP\Response();
			$response->success = 1;
			$response->order = $order;

			print $this->formatOutput($response);
		}

		###################################################
		### Update a Sales Order						###
		###################################################
		public function cancelOrder() {
			$order = new \Sales\Order();
			if ($GLOBALS['_SESSION_']->customer->can('edit sales order') || $GLOBALS['_SESSION_']->customer->id == $order->customer_id) {
				# OK To Update
                $order = new \Sales\Order();
                if (! $order->get($_REQUEST['code'])) $this->error("Order not found");
                if (in_array($order->status,array('CANCELLED','COMPLETE'))) $this->error($order->status." order not ready for cancellation");
                if (! $order->cancel()) $this->error($order->error());
			}
            else $this->error("Permission denied");

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

			if (! $order->get($_REQUEST['code'])) $this->error("Error getting order: ".$order->error());
			if (! $GLOBALS['_SESSION_']->customer->can('browse sales orders') && $GLOBALS['_SESSION_']->customer->id != $order->customer_id) $this->error("Permission denied");

			$response = new \HTTP\Response();
			$response->success = 1;
			$response->order = $order;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Find matching Sales Orders					###
		###################################################
		public function findOrders() {
			$orderList = new \Sales\OrderList();

			$parameters = array();
			if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
            if (! $GLOBALS['_SESSION_']->customer->id) $this->error("Permission Denied");
			elseif (! $GLOBALS['_SESSION_']->customer->can('browse sales orders')) {
                $parameters['customer_id'] = $GLOBALS['_SESSION_']->customer->id;
                $parameters['organization_id'] = $GLOBALS['_SESSION_']->customer->organization->id;
            }
            elseif (isset($_REQUEST['customer_code'])) {
                $customer = new \Register\Customer();
                if (! $customer->get($parameters['customer_code'])) $this->error("Customer not found");
                $parameters['customer_id'] = $customer->id;
            }

			if (!empty($_REQUEST['salesperson_code'])) {
                $salesperson = new \Register\Customer();
                if (! $salesperson->get($parameters['salesperson_code'])) $this->error("Salesperson '".$_REQUEST['salesperson_code']."' not found");
                $parameters['salesperson_id'] = $salesperson->id;
            }

			if (! $GLOBALS['_SESSION_']->customer->can('browse sales orders') && !empty($_REQUEST['organization_code'])) {
                $organization = new \Register\Organization();
                if (! $organization->get($_REQUEST['organization_code'])) $this->error("Organization not found");
                $parameters['organization_id'] = $organization->id;
            }

			$orders = $orderList->find($parameters);
			if ($orderList->error) $this->app_error("Error finding orders: ".$orderList->error());

			$response = new \HTTP\Response();
			$response->success = 1;
			$response->order = $orders;

			print $this->formatOutput($response);
		}

		###################################################
		### Add an Item to an Order						###
		###################################################
		public function addOrderItem() {
			$order = new \Sales\Order();
			if (isset($_REQUEST['order_code'])) {
				if (! $order->get($_REQUEST['order_code'])) {
					$this->error("Order not found: ".$order->error());
				}
			}
			else {
				$this->error("Order Code required");
			}

			if (isset($_REQUEST['product_code'])) {
				$product = new \Product\Item();
				if (! $product->get($_REQUEST['product_code'])) {
					$this->error("Cannot find product: ".$product->error());
				}
			}
			else {
				$this->error("Product Code required");
			}
			if (! $product->id) $this->error("Product not found");

			$parameters = array(
				'order_id'		=> $order->id,
				'product_id'	=> $product->id,
				'description'	=> $_REQUEST['description'],
				'price'			=> $_REQUEST['price'],
				'quantity'		=> $_REQUEST['quantity']
			);
	
			if (! $order->addItem($parameters)) $this->error("Error adding order item: ".$order->error());
			$item = new \Sales\Order\Item($order->lastItemID());
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->item = $item;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Update an Order Item						###
		###################################################
		public function updateOrderItem() {
			$order = new \Sales\Order();
			if ($order->get($_REQUEST['order_code'])) {
				error("Error finding order: ".$order->error());
			}
	
			$item = $order->getItem($_REQUEST['line_number']);
			if (! $item) $this->error("Item not found: ".$order->error());
	
			$parameters = array();
			if ($_REQUEST['price']) $parameters['price'] = $_REQUEST['price'];
			if ($_REQUEST['quantity']) $parameters['quantity'] = $_REQUEST['quantity'];
			if ($_REQUEST['description']) $parameters['description'] = $_REQUEST['description'];
	
			$item->update($parameters);
			if ($item->error) $this->app_error("Error updating item: ".$item->error(),'error');
	
			$response = new \HTTP\Response();
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
		### Find order items							###
		###################################################
		public function findOrderItems() {
			$itemList = new \Sales\Order\ItemList();

			$parameters = array();
			if ($_REQUEST['order_code']) $parameters['order_code'] = $_REQUEST['order_code'];
			if ($_REQUEST['product_code']) $parameters['product_code'] = $_REQUEST['product_code'];
			if ($_REQUEST['status']) $parameters['status'] = $_REQUEST['status'];

			$items = $itemList->find($parameters);
			if ($itemList->error()) error("Error finding items: ".$itemList->error());

			$response = new \HTTP\Response();
			$response->success = 1;
			$response->item = $items;

			print $this->formatOutput($response);
		}

		###################################################
		### Add A Currency								###
		###################################################
		public function addCurrency() {
			if (! $GLOBALS['_SESSION_']->customer->can('edit currencies')) $this->error("Permission Denied");

			if (empty($_REQUEST['name'])) $this->error("Currency name required");
			$parameters = array('name' => $_REQUEST['name']);
			if (!empty($_REQUEST['symbol'])) $parameters['symbol'] = $_REQUEST['symbol'];

			$currency = new \Sales\Currency();
			if ($currency->get($_REQUEST['name'])) $this->error("Currency already exists");
	
			if (! $currency->add($parameters)) $this->error("Error adding currency: ".$currency->error());

			$response = new \HTTP\Response();
			$response->success = 1;
			$response->currency = $currency;

			print $this->formatOutput($response);
		}

		###################################################
		### Update A Currency							###
		###################################################
		public function updateCurrency() {
			if (! $GLOBALS['_SESSION_']->customer->can('edit currencies')) $this->error("Permission Denied");

			if (empty($_REQUEST['name'])) $this->error("Currency name required");
			$parameters = array('name' => $_REQUEST['name']);
			if (!empty($_REQUEST['symbol'])) $parameters['symbol'] = $_REQUEST['symbol'];

			$currency = new \Sales\Currency();
			if (! $currency->get($_REQUEST['name'])) $this->error("Currency not found");
	
			if (! $currency->update($parameters)) $this->error("Error updating currency: ".$currency->error());

			$response = new \HTTP\Response();
			$response->success = 1;
			$response->currency = $currency;

			print $this->formatOutput($response);
		}

		###################################################
		### Get A Currency								###
		###################################################
		public function getCurrency() {
			if (empty($_REQUEST['name'])) $this->error("Currency name required");

			$currency = new \Sales\Currency();
			if (! $currency->get($_REQUEST['name'])) $this->error("Currency not found");

			$response = new \HTTP\Response();
			$response->success = 1;
			$response->currency = $currency;

			print $this->formatOutput($response);
		}

		###################################################
		### Find matching Currencies					###
		###################################################
		public function findCurrencies() {
			$currencyList = new \Sales\CurrencyList();

			$parameters = array();

			$currencies = $currencyList->find($parameters);
			if ($currencyList->error()) $this->error("Error finding currencies: ".$currencyList->error());

			$response = new \HTTP\Response();
			$response->success = 1;
			$response->currency = $currencies;

			print $this->formatOutput($response);
		}

		###################################################
		### Find matching Order Events					###
		###################################################
		public function findOrderEvents() {
			if (! $GLOBALS['_SESSION_']->authenticated()) $this->error("Authentication required");

			$eventList = new \Sales\Order\EventList();

			$parameters = array();
			if (empty($_REQUEST['order_code'])) $this->error("Order code required");
			$order = new \Sales\Order();
			if (! $order->get($_REQUEST['order_code'])) $this->error("Order not found");
			$parameters['order_id'] = $order->id;
			
			if (! $GLOBALS['_SESSION_']->customer->can("browse sales orders") && $order->customer_id != $GLOBALS['_SESSION_']->customer->id) $this->error("Permission denied");

			$events = $eventList->find($parameters);
			if ($eventList->error()) $this->error("Error finding events: ".$eventList->error());

			$response = new \HTTP\Response();
			$response->success = 1;
			$response->event = $events;

			print $this->formatOutput($response);
		}

        ###################################################
        ### API Form                                    ###
        ###################################################
		public function _methods() {
			return array(
				'ping'			=> array(),
				'addOrder'		=> array(
					'code'			=> array(),
					'customer_code'	=> array('required' => true),
					'status'		=> array(),
					'salesperson_code'	=> array(),
                    'customer_order_number' => array(),
					'date_quote'	=> array(),
					'date_order'	=> array(),
				),
                `updateOrder`   => array(
					'code'			=> array('required' => true),
					'status'		=> array(),
					'salesperson_code'	=> array(),
                    'customer_order_number' => array(),
					'date_quote'	=> array(),
					'date_order'	=> array(),
                ),
				'getOrder'		=> array(
					'code'			=> array('required' => true),
				),
				'approveOrder'	=> array(
					'code'	=> array('required' => true),
				),
				'cancelOrder'	=> array(
					'code'	=> array('required' => true),
                    'reason'    => array('required' => true)
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
					'description'	=> array(),
					'price'			=> array('required' => true),
				),
				'updateOrderItem'	=> array(
					'order_code'	=> array('required' => true),
					'line_number'	=> array('required' => true),
					'product_code'	=> array(),
					'quantity'		=> array(),
					'price'			=> array(),
					'deleted'		=> array(),
				),
				'dropOrderItem'	=> array(
					'order_code'	=> array('required'	=> true),
					'line_number'	=> array('required'	=> true),
				),
				'findOrderItems'	=> array(
					'order_code'	=> array(),
					'product_code'	=> array(),
					'status'		=> array()
				),
				'addCurrency'		=> array(
					'name'			=> array('required' => true),
					'symbol'		=> array()
				),
				'updateCurrency'	=> array(
					'name'			=> array('required' => true),
					'symbol'		=> array()
				),
				'getCurrency'		=> array(
					'name'			=> array('required' => true)
				),
				'findCurrencies'	=> array(),
				'findOrderEvents'	=> array(
					'order_code'	=> array()
				)
			);
		}
	}
